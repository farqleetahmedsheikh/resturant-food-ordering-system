<?php

namespace App\Services\Orders;

use App\Models\Order;

readonly class CheckoutResult
{
    public function __construct(
        public Order $order,
        public string $checkoutUrl,
        public string $stripeCheckoutSessionId,
    ) {}
}
