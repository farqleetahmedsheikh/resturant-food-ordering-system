<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\PendingStripeCheckout;

readonly class CheckoutResult
{
    public function __construct(
        public ?Order $order,
        public string $checkoutUrl,
        public string $stripeCheckoutSessionId,
        public ?PendingStripeCheckout $pendingCheckout = null,
    ) {}
}
