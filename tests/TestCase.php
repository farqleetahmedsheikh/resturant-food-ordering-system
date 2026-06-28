<?php

namespace Tests;

use App\Contracts\Payments\StripeCheckoutGateway;
use App\Services\Payments\StripeCheckoutSessionData;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function fakeStripeCheckout(
        string $sessionId = 'cs_test_checkout_session',
        string $checkoutUrl = 'https://checkout.stripe.test/session',
    ): object {
        config([
            'services.stripe.mode' => 'sandbox',
            'services.stripe.currency' => 'AUD',
            'services.stripe.publishable_key' => 'pk_test_unit',
            'services.stripe.secret_key' => 'sk_test_unit',
            'services.stripe.webhook_secret' => 'whsec_unit',
            'services.stripe.success_url' => 'http://localhost/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'services.stripe.cancel_url' => 'http://localhost/checkout/cancel',
        ]);

        $gateway = new class($sessionId, $checkoutUrl) implements StripeCheckoutGateway
        {
            /**
             * @var array<int, array<string, mixed>>
             */
            public array $payloads = [];

            public function __construct(
                private string $sessionId,
                private string $checkoutUrl,
            ) {}

            public function createCheckoutSession(array $payload): StripeCheckoutSessionData
            {
                $this->payloads[] = $payload;

                return new StripeCheckoutSessionData($this->sessionId, $this->checkoutUrl);
            }
        };

        $this->app->instance(StripeCheckoutGateway::class, $gateway);

        return $gateway;
    }
}
