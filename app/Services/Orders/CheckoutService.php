<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\IdempotencyKey;
use App\Models\PendingStripeCheckout;
use App\Models\User;
use App\Services\Cart\DatabaseCartService;
use App\Services\Payments\StripeCheckoutService;
use App\Services\RestaurantAvailabilityService;
use App\Services\Security\AuditLogger;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private DatabaseCartService $cartService,
        private AuditLogger $auditLogger,
        private RestaurantAvailabilityService $availability,
        private StripeCheckoutService $stripeCheckoutService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function checkout(User $user, array $payload, ?string $idempotencyKey = null, ?Request $request = null): CheckoutResult
    {
        $this->stripeCheckoutService->assertReadyForCheckout();

        $requestHash = hash('sha256', json_encode($payload));

        $isNewCheckout = false;
        $claimedIdempotency = null;

        $pendingCheckout = DB::transaction(function () use ($user, $payload, $idempotencyKey, $requestHash, $request, &$isNewCheckout, &$claimedIdempotency): PendingStripeCheckout {
            $idempotency = $this->claimIdempotencyKey($user, $idempotencyKey, $requestHash, $request);
            $claimedIdempotency = $idempotency;

            if ($idempotency?->response_body) {
                $pendingCheckoutId = (int) ($idempotency->response_body['pending_checkout_id'] ?? 0);

                if ($pendingCheckoutId > 0) {
                    return PendingStripeCheckout::query()->findOrFail($pendingCheckoutId);
                }
            }

            $cart = $this->cartService->get($user);
            $summary = $this->cartService->summary($cart);
            $restaurant = $summary['restaurant'];

            if ($summary['count'] < 1) {
                throw new BusinessRuleException('Your cart is empty. Add a meal before checkout.');
            }

            $availability = $this->availability->status($restaurant);

            if (! $availability['is_open']) {
                throw new BusinessRuleException('Restaurant is closed now. Your items are in cart and you can checkout later when restaurant opens.');
            }

            if ($summary['minimum_order_amount'] > 0 && $summary['subtotal'] < $summary['minimum_order_amount']) {
                throw new BusinessRuleException('Minimum order amount is '.Money::format($summary['minimum_order_amount']).'.');
            }

            $pendingCheckout = PendingStripeCheckout::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant?->id,
                'cart_id' => $cart->id,
                'source' => 'mobile_api',
                'status' => PendingStripeCheckout::STATUS_CREATED,
                'customer_payload' => [
                    'customer_name' => $payload['customer_name'],
                    'customer_phone' => $payload['customer_phone'],
                    'customer_email' => $payload['customer_email'] ?? null,
                    'delivery_address' => $payload['delivery_address'],
                    'delivery_latitude' => $payload['delivery_latitude'] ?? null,
                    'delivery_longitude' => $payload['delivery_longitude'] ?? null,
                    'order_notes' => $payload['order_notes'] ?? null,
                ],
                'summary' => $this->checkoutSummarySnapshot($summary),
                'currency' => 'AUD',
                'total' => $summary['total'],
            ]);

            $idempotency?->update([
                'response_code' => 201,
                'response_body' => ['pending_checkout_id' => $pendingCheckout->id],
            ]);

            $this->auditLogger->record('checkout.session_requested', $user, $pendingCheckout, [], ['total' => $pendingCheckout->total], $request);

            $isNewCheckout = true;

            return $pendingCheckout;
        });

        if (! $isNewCheckout) {
            $responseBody = $claimedIdempotency?->response_body ?? [];
            $checkoutUrl = (string) ($responseBody['checkout_url'] ?? '');

            if ($checkoutUrl === '') {
                throw new BusinessRuleException('Checkout session is still being prepared. Please retry in a moment.', 409);
            }

            return new CheckoutResult(
                null,
                $checkoutUrl,
                (string) ($responseBody['stripe_checkout_session_id'] ?? $pendingCheckout->stripe_checkout_session_id),
                $pendingCheckout,
            );
        }

        $session = $this->stripeCheckoutService->createForPendingCheckout($pendingCheckout);

        $claimedIdempotency?->update([
            'response_body' => [
                'pending_checkout_id' => $pendingCheckout->id,
                'checkout_url' => $session->url,
                'stripe_checkout_session_id' => $session->id,
            ],
        ]);

        return new CheckoutResult(
            null,
            $session->url,
            $session->id,
            $pendingCheckout->refresh(),
        );
    }

    private function claimIdempotencyKey(User $user, ?string $key, string $requestHash, ?Request $request): ?IdempotencyKey
    {
        if (! $key) {
            return null;
        }

        $idempotency = IdempotencyKey::query()
            ->where('user_id', $user->id)
            ->where('key', $key)
            ->lockForUpdate()
            ->first();

        if ($idempotency) {
            if ($idempotency->request_hash !== $requestHash) {
                throw new BusinessRuleException('Idempotency-Key was already used for a different checkout request.', 409);
            }

            return $idempotency;
        }

        return IdempotencyKey::create([
            'user_id' => $user->id,
            'key' => $key,
            'method' => $request?->method() ?? 'POST',
            'path' => $request?->path() ?? 'api/v1/customer/checkout',
            'request_hash' => $requestHash,
            'expires_at' => now()->addDay(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private function checkoutSummarySnapshot(array $summary): array
    {
        unset($summary['restaurant']);

        return $summary;
    }

}
