<?php

namespace App\Contracts\Payments;

use App\Services\Payments\StripeCheckoutSessionData;

interface StripeCheckoutGateway
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function createCheckoutSession(array $payload): StripeCheckoutSessionData;
}
