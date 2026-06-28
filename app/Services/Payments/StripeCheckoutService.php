<?php

namespace App\Services\Payments;

use App\Contracts\Payments\StripeCheckoutGateway;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Illuminate\Support\Str;

class StripeCheckoutService
{
    public function __construct(private StripeCheckoutGateway $gateway) {}

    public function assertReadyForCheckout(): void
    {
        $mode = strtolower((string) config('services.stripe.mode', 'sandbox'));
        $currency = strtoupper((string) config('services.stripe.currency', 'AUD'));
        $secretKey = (string) config('services.stripe.secret_key');
        $publishableKey = (string) config('services.stripe.publishable_key');
        $webhookSecret = (string) config('services.stripe.webhook_secret');
        $successUrl = (string) config('services.stripe.success_url');
        $cancelUrl = (string) config('services.stripe.cancel_url');

        if ($currency !== 'AUD') {
            throw new StripeConfigurationException('Stripe Checkout is configured for an unsupported currency. Arcade Kebab House accepts AUD only.');
        }

        if ($secretKey === '' || $webhookSecret === '' || $successUrl === '' || $cancelUrl === '') {
            throw new StripeConfigurationException('Stripe Checkout is not fully configured. Add the Stripe secret key, webhook secret, success URL, and cancel URL to the environment.');
        }

        if ($mode === 'sandbox') {
            if (! Str::startsWith($secretKey, 'sk_test_')) {
                throw new StripeConfigurationException('Stripe sandbox mode requires a test secret key that starts with sk_test_.');
            }

            if ($publishableKey !== '' && ! Str::startsWith($publishableKey, 'pk_test_')) {
                throw new StripeConfigurationException('Stripe sandbox mode requires a test publishable key that starts with pk_test_.');
            }

            if (! Str::startsWith($webhookSecret, 'whsec_')) {
                throw new StripeConfigurationException('Stripe webhook secret must start with whsec_.');
            }
        }
    }

    public function createForOrder(Order $order): StripeCheckoutSessionData
    {
        $this->assertReadyForCheckout();

        $order->loadMissing(['items', 'user']);

        $session = $this->gateway->createCheckoutSession([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'client_reference_id' => (string) $order->id,
            'customer_email' => $order->customer_email ?: $order->user?->email,
            'success_url' => (string) config('services.stripe.success_url'),
            'cancel_url' => (string) config('services.stripe.cancel_url'),
            'line_items' => $this->lineItems($order),
            'metadata' => $this->metadata($order),
            'payment_intent_data' => [
                'metadata' => $this->metadata($order),
            ],
        ]);

        $order->forceFill([
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_status' => 'unpaid',
        ])->save();

        return $session;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function lineItems(Order $order): array
    {
        $items = $order->items
            ->map(fn (OrderItem $item): array => [
                'price_data' => [
                    'currency' => strtolower((string) $order->currency ?: 'aud'),
                    'unit_amount' => Money::toCents($item->price),
                    'product_data' => [
                        'name' => $this->lineItemName($item),
                    ],
                ],
                'quantity' => $item->quantity,
            ])
            ->values()
            ->all();

        if (Money::toCents($order->delivery_fee) > 0) {
            $items[] = [
                'price_data' => [
                    'currency' => strtolower((string) $order->currency ?: 'aud'),
                    'unit_amount' => Money::toCents($order->delivery_fee),
                    'product_data' => [
                        'name' => 'Delivery fee',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        return $items;
    }

    private function lineItemName(OrderItem $item): string
    {
        $name = $item->item_name;

        if ($item->size_name) {
            $name .= ' - '.$item->size_name;
        }

        return Str::limit($name, 120, '');
    }

    /**
     * @return array<string, string>
     */
    private function metadata(Order $order): array
    {
        return [
            'order_id' => (string) $order->id,
            'order_number' => (string) $order->order_number,
            'environment' => (string) config('services.stripe.mode', 'sandbox'),
        ];
    }
}
