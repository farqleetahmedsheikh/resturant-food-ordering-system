<?php

namespace App\Services\Payments;

readonly class StripeCheckoutSessionData
{
    public function __construct(
        public string $id,
        public string $url,
    ) {}
}
