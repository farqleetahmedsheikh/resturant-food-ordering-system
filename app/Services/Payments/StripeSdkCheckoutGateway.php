<?php

namespace App\Services\Payments;

use App\Contracts\Payments\StripeCheckoutGateway;
use Stripe\StripeClient;

class StripeSdkCheckoutGateway implements StripeCheckoutGateway
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function createCheckoutSession(array $payload): StripeCheckoutSessionData
    {
        $client = new StripeClient((string) config('services.stripe.secret_key'));
        $session = $client->checkout->sessions->create($payload);

        return new StripeCheckoutSessionData(
            (string) $session->id,
            (string) $session->url,
        );
    }
}
