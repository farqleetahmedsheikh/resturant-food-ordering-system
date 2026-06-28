<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\StripeEvent;
use App\Services\Email\OrderEmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeObject;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookService
{
    public function __construct(private OrderEmailService $orderEmailService) {}

    /**
     * @throws SignatureVerificationException
     * @throws UnexpectedValueException
     */
    public function handle(string $payload, string $signature): void
    {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '') {
            throw new UnexpectedValueException('Stripe webhook secret is not configured.');
        }

        $event = Webhook::constructEvent($payload, $signature, $secret);
        $orderToEmail = null;

        DB::transaction(function () use ($event, &$orderToEmail): void {
            $eventRecord = StripeEvent::query()
                ->where('stripe_event_id', $event->id)
                ->lockForUpdate()
                ->first();

            if ($eventRecord?->processed_at) {
                Log::info('Stripe webhook duplicate ignored.', [
                    'stripe_event_id' => $event->id,
                    'type' => $event->type,
                ]);

                return;
            }

            if (! $eventRecord) {
                $eventRecord = StripeEvent::create([
                    'stripe_event_id' => $event->id,
                    'type' => $event->type,
                    'object_id' => (string) ($this->value($event->data->object, 'id') ?? ''),
                    'payload' => [
                        'stripe_event_id' => $event->id,
                        'type' => $event->type,
                    ],
                ]);
            }

            $orderToEmail = $this->processEvent($event->type, $event->data->object);

            $eventRecord->update([
                'order_id' => $orderToEmail?->id ?? $this->orderIdForEventObject($event->data->object),
                'processed_at' => now(),
            ]);

            Log::info('Stripe webhook processed.', [
                'stripe_event_id' => $event->id,
                'type' => $event->type,
                'order_id' => $eventRecord->order_id,
            ]);
        });

        if ($orderToEmail) {
            $this->orderEmailService->sendOrderPlaced(
                $orderToEmail->fresh(['items', 'restaurant', 'user', 'rider', 'delivery']),
            );
        }
    }

    private function processEvent(string $type, StripeObject $object): ?Order
    {
        return match ($type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($object),
            'checkout.session.expired' => $this->handleCheckoutSessionExpired($object),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($object),
            default => null,
        };
    }

    private function handleCheckoutSessionCompleted(StripeObject $session): ?Order
    {
        $order = $this->findOrderFromCheckoutSession($session);

        if (! $order) {
            Log::warning('Stripe checkout.session.completed could not be matched to an order.', [
                'checkout_session_id' => $this->value($session, 'id'),
            ]);

            return null;
        }

        $paymentStatus = (string) ($this->value($session, 'payment_status') ?? '');
        $paymentIntentId = $this->stripeId($this->value($session, 'payment_intent'));

        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        $wasPaid = $order->payment_status === 'paid';

        $payload = [
            'stripe_payment_status' => $paymentStatus ?: 'paid',
            'stripe_payment_intent_id' => $paymentIntentId ?: $order->stripe_payment_intent_id,
        ];

        if (in_array($paymentStatus, ['paid', 'no_payment_required'], true)) {
            $previousStatus = $order->order_status;

            $payload = array_merge($payload, [
                'payment_status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
                'payment_failed_at' => null,
                'payment_cancelled_at' => null,
                'payment_failure_reason' => null,
            ]);

            if ($order->order_status === 'pending_payment') {
                $payload['order_status'] = 'pending';
            }

            $order->update($payload);

            if ($previousStatus !== $order->order_status) {
                $order->statusHistories()->create([
                    'previous_status' => $previousStatus,
                    'new_status' => $order->order_status,
                    'changed_by_user_id' => null,
                    'changed_by_role' => 'stripe',
                    'reason' => 'Stripe payment confirmed',
                    'metadata' => [
                        'checkout_session_id' => $order->stripe_checkout_session_id,
                        'payment_intent_id' => $order->stripe_payment_intent_id,
                    ],
                ]);
            }

            return $wasPaid ? null : $order;
        }

        $order->update($payload);

        return null;
    }

    private function handleCheckoutSessionExpired(StripeObject $session): ?Order
    {
        $order = $this->findOrderFromCheckoutSession($session);

        if (! $order) {
            return null;
        }

        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        $previousStatus = $order->order_status;

        $order->update([
            'payment_status' => $order->payment_status === 'paid' ? 'paid' : 'cancelled',
            'stripe_payment_status' => 'expired',
            'payment_cancelled_at' => $order->payment_cancelled_at ?? now(),
            'order_status' => $order->payment_status === 'paid'
                ? $order->order_status
                : ($order->order_status === 'pending_payment' ? 'cancelled' : $order->order_status),
        ]);

        if ($previousStatus !== $order->order_status) {
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'new_status' => $order->order_status,
                'changed_by_user_id' => null,
                'changed_by_role' => 'stripe',
                'reason' => 'Stripe Checkout session expired',
                'metadata' => [
                    'checkout_session_id' => $order->stripe_checkout_session_id,
                ],
            ]);
        }

        return null;
    }

    private function handlePaymentIntentFailed(StripeObject $paymentIntent): ?Order
    {
        $order = $this->findOrderFromPaymentIntent($paymentIntent);

        if (! $order) {
            return null;
        }

        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        $previousStatus = $order->order_status;
        $failure = $this->value($paymentIntent, 'last_payment_error');

        $order->update([
            'payment_status' => $order->payment_status === 'paid' ? 'paid' : 'failed',
            'stripe_payment_intent_id' => (string) ($this->value($paymentIntent, 'id') ?? $order->stripe_payment_intent_id),
            'stripe_payment_status' => (string) ($this->value($paymentIntent, 'status') ?? 'failed'),
            'payment_failed_at' => $order->payment_failed_at ?? now(),
            'payment_failure_reason' => Str::limit((string) ($this->value($failure, 'message') ?? 'Payment failed.'), 1000, ''),
            'order_status' => $order->payment_status === 'paid'
                ? $order->order_status
                : ($order->order_status === 'pending_payment' ? 'cancelled' : $order->order_status),
        ]);

        if ($previousStatus !== $order->order_status) {
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'new_status' => $order->order_status,
                'changed_by_user_id' => null,
                'changed_by_role' => 'stripe',
                'reason' => 'Stripe payment failed',
                'metadata' => [
                    'payment_intent_id' => $order->stripe_payment_intent_id,
                ],
            ]);
        }

        return null;
    }

    private function findOrderFromCheckoutSession(StripeObject $session): ?Order
    {
        $sessionId = (string) ($this->value($session, 'id') ?? '');

        $order = Order::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if ($order) {
            return $order;
        }

        $orderId = $this->metadataValue($session, 'order_id')
            ?: (string) ($this->value($session, 'client_reference_id') ?? '');

        return $orderId !== '' ? Order::query()->find($orderId) : null;
    }

    private function findOrderFromPaymentIntent(StripeObject $paymentIntent): ?Order
    {
        $paymentIntentId = (string) ($this->value($paymentIntent, 'id') ?? '');

        $order = Order::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();

        if ($order) {
            return $order;
        }

        $orderId = $this->metadataValue($paymentIntent, 'order_id');

        return $orderId !== '' ? Order::query()->find($orderId) : null;
    }

    private function orderIdForEventObject(StripeObject $object): ?int
    {
        $orderId = $this->metadataValue($object, 'order_id');

        if ($orderId === '') {
            return null;
        }

        return (int) $orderId;
    }

    private function metadataValue(StripeObject $object, string $key): string
    {
        $metadata = $this->value($object, 'metadata');

        return (string) ($this->value($metadata, $key) ?? '');
    }

    private function stripeId(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof StripeObject) {
            return (string) ($this->value($value, 'id') ?? '');
        }

        return null;
    }

    private function value(mixed $object, string $key): mixed
    {
        if (is_array($object)) {
            return $object[$key] ?? null;
        }

        if ($object instanceof StripeObject) {
            return $object->{$key} ?? null;
        }

        if (is_object($object)) {
            return $object->{$key} ?? null;
        }

        return null;
    }
}
