<?php

namespace App\Services\Payments;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PendingStripeCheckout;
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
        $pendingCheckout = $this->findPendingCheckoutFromCheckoutSession($session);

        if ($pendingCheckout) {
            return $this->createOrderFromPaidPendingCheckout($pendingCheckout, $session);
        }

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
        $pendingCheckout = $this->findPendingCheckoutFromCheckoutSession($session);

        if ($pendingCheckout) {
            $pendingCheckout = PendingStripeCheckout::query()->lockForUpdate()->findOrFail($pendingCheckout->id);

            if (! $pendingCheckout->order_id) {
                $pendingCheckout->update([
                    'status' => PendingStripeCheckout::STATUS_EXPIRED,
                    'stripe_payment_status' => 'expired',
                    'expired_at' => $pendingCheckout->expired_at ?? now(),
                ]);
            }

            return null;
        }

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
        $pendingCheckout = $this->findPendingCheckoutFromPaymentIntent($paymentIntent);

        if ($pendingCheckout) {
            $pendingCheckout = PendingStripeCheckout::query()->lockForUpdate()->findOrFail($pendingCheckout->id);
            $failure = $this->value($paymentIntent, 'last_payment_error');

            if (! $pendingCheckout->order_id) {
                $pendingCheckout->update([
                    'status' => PendingStripeCheckout::STATUS_FAILED,
                    'stripe_payment_intent_id' => (string) ($this->value($paymentIntent, 'id') ?? $pendingCheckout->stripe_payment_intent_id),
                    'stripe_payment_status' => (string) ($this->value($paymentIntent, 'status') ?? 'failed'),
                    'failed_at' => $pendingCheckout->failed_at ?? now(),
                    'failure_reason' => Str::limit((string) ($this->value($failure, 'message') ?? 'Payment failed.'), 1000, ''),
                ]);
            }

            return null;
        }

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

    private function createOrderFromPaidPendingCheckout(PendingStripeCheckout $pendingCheckout, StripeObject $session): ?Order
    {
        $paymentStatus = (string) ($this->value($session, 'payment_status') ?? '');
        $paymentIntentId = $this->stripeId($this->value($session, 'payment_intent'));

        $pendingCheckout = PendingStripeCheckout::query()->lockForUpdate()->findOrFail($pendingCheckout->id);

        $pendingCheckout->update([
            'stripe_payment_status' => $paymentStatus ?: $pendingCheckout->stripe_payment_status,
            'stripe_payment_intent_id' => $paymentIntentId ?: $pendingCheckout->stripe_payment_intent_id,
        ]);

        if (! in_array($paymentStatus, ['paid', 'no_payment_required'], true)) {
            return null;
        }

        if ($pendingCheckout->order_id) {
            return null;
        }

        $summary = $pendingCheckout->summary;
        $customer = $pendingCheckout->customer_payload;

        $order = Order::create([
            'user_id' => $pendingCheckout->user_id,
            'restaurant_id' => $pendingCheckout->restaurant_id,
            'order_number' => $this->generateOrderNumber(),
            'customer_name' => $customer['customer_name'],
            'customer_phone' => $customer['customer_phone'],
            'customer_email' => $customer['customer_email'] ?? null,
            'delivery_address' => $customer['delivery_address'],
            'delivery_latitude' => $customer['delivery_latitude'] ?? null,
            'delivery_longitude' => $customer['delivery_longitude'] ?? null,
            'order_notes' => $customer['order_notes'] ?? null,
            'subtotal' => $summary['subtotal'],
            'delivery_fee' => $summary['delivery_fee'],
            'total' => $summary['total'],
            'currency' => $pendingCheckout->currency ?: ($summary['currency'] ?? 'AUD'),
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'stripe_checkout_session_id' => $pendingCheckout->stripe_checkout_session_id,
            'stripe_payment_intent_id' => $pendingCheckout->stripe_payment_intent_id,
            'stripe_payment_status' => $paymentStatus ?: 'paid',
            'paid_at' => $pendingCheckout->paid_at ?? now(),
            'order_status' => 'pending',
        ]);

        foreach ($summary['items'] as $item) {
            $order->items()->create([
                'menu_item_id' => $item['menu_item_id'],
                'item_name' => $item['name'],
                'item_notes' => $item['item_notes'] ?? null,
                'size_name' => $item['size']['name'] ?? null,
                'size_price' => $item['size']['price'] ?? null,
                'addons_snapshot' => $item['addons'],
                'customizations_snapshot' => $item['customizations'] ?? [],
                'addons_total' => $item['addons_total'],
                'quantity' => $item['quantity'],
                'price' => $item['unit_price'],
                'total' => $item['line_total'],
            ]);
        }

        $order->statusHistories()->create([
            'previous_status' => null,
            'new_status' => 'pending',
            'changed_by_user_id' => null,
            'changed_by_role' => 'stripe',
            'reason' => 'Stripe payment confirmed',
            'metadata' => [
                'pending_checkout_id' => $pendingCheckout->id,
                'checkout_session_id' => $pendingCheckout->stripe_checkout_session_id,
                'payment_intent_id' => $pendingCheckout->stripe_payment_intent_id,
                'source' => $pendingCheckout->source,
            ],
        ]);

        $pendingCheckout->update([
            'status' => PendingStripeCheckout::STATUS_ORDER_CREATED,
            'order_id' => $order->id,
            'paid_at' => $pendingCheckout->paid_at ?? now(),
        ]);

        if ($pendingCheckout->cart_id) {
            Cart::query()
                ->whereKey($pendingCheckout->cart_id)
                ->where('status', Cart::STATUS_ACTIVE)
                ->update([
                    'status' => Cart::STATUS_CONVERTED,
                    'active_cart_key' => null,
                    'updated_at' => now(),
                ]);
        }

        return $order;
    }

    private function findPendingCheckoutFromCheckoutSession(StripeObject $session): ?PendingStripeCheckout
    {
        $sessionId = (string) ($this->value($session, 'id') ?? '');

        $pendingCheckout = PendingStripeCheckout::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if ($pendingCheckout) {
            return $pendingCheckout;
        }

        $pendingCheckoutId = $this->metadataValue($session, 'pending_checkout_id');

        if ($pendingCheckoutId === '') {
            $clientReference = (string) ($this->value($session, 'client_reference_id') ?? '');

            if (str_starts_with($clientReference, 'pending_checkout:')) {
                $pendingCheckoutId = substr($clientReference, strlen('pending_checkout:'));
            }
        }

        return $pendingCheckoutId !== '' ? PendingStripeCheckout::query()->find($pendingCheckoutId) : null;
    }

    private function findPendingCheckoutFromPaymentIntent(StripeObject $paymentIntent): ?PendingStripeCheckout
    {
        $pendingCheckoutId = $this->metadataValue($paymentIntent, 'pending_checkout_id');

        if ($pendingCheckoutId !== '') {
            return PendingStripeCheckout::query()->find($pendingCheckoutId);
        }

        $paymentIntentId = (string) ($this->value($paymentIntent, 'id') ?? '');

        return $paymentIntentId !== ''
            ? PendingStripeCheckout::query()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : null;
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
        $pendingCheckoutId = $this->metadataValue($object, 'pending_checkout_id');

        if ($pendingCheckoutId !== '') {
            return PendingStripeCheckout::query()->find($pendingCheckoutId)?->order_id;
        }

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

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
