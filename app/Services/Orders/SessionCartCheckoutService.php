<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\MenuItem;
use App\Models\PendingStripeCheckout;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Payments\StripeCheckoutService;
use App\Services\PricingService;
use App\Services\RestaurantAvailabilityService;
use App\Support\Cart as SessionCart;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class SessionCartCheckoutService
{
    public function __construct(
        private PricingService $pricingService,
        private RestaurantAvailabilityService $availability,
        private StripeCheckoutService $stripeCheckoutService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function checkout(User $user, array $payload): CheckoutResult
    {
        $this->stripeCheckoutService->assertReadyForCheckout();

        $restaurant = Restaurant::current();

        if (! $restaurant) {
            throw new BusinessRuleException('Restaurant is not available right now.');
        }

        $availability = $this->availability->status($restaurant);

        if (! $availability['is_open']) {
            throw new BusinessRuleException('Restaurant is closed now. Your items are in cart and you can checkout later when restaurant opens.');
        }

        $summary = $this->validatedSummary($restaurant);
        $sessionSummary = SessionCart::summary($restaurant);

        if ($summary['count'] < 1) {
            throw new BusinessRuleException('Your cart is empty. Add a meal before checkout.');
        }

        if ($restaurant->minimum_order_amount > 0 && $summary['subtotal'] < (float) $restaurant->minimum_order_amount) {
            throw new BusinessRuleException('Minimum order amount is '.Money::format($restaurant->minimum_order_amount).'.');
        }

        if (abs($summary['total'] - (float) $sessionSummary['total']) > 0.001) {
            throw new BusinessRuleException('Your cart total changed. Please review your cart before payment.');
        }

        $pendingCheckout = DB::transaction(function () use ($user, $payload, $restaurant, $summary): PendingStripeCheckout {
            return PendingStripeCheckout::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'source' => 'website',
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
                'currency' => $summary['currency'],
                'total' => $summary['total'],
            ]);
        });

        $session = $this->stripeCheckoutService->createForPendingCheckout($pendingCheckout);

        return new CheckoutResult(
            null,
            $session->url,
            $session->id,
            $pendingCheckout->refresh(),
        );
    }

    /**
     * @return array{
     *     count: int,
     *     subtotal: float,
     *     delivery_fee: float,
     *     minimum_order_amount: float,
     *     total: float,
     *     currency: string,
     *     items: array<int, array<string, mixed>>,
     *     restaurant: Restaurant
     * }
     */
    private function validatedSummary(Restaurant $restaurant): array
    {
        $cartItems = SessionCart::items();

        if ($cartItems === []) {
            return [
                'count' => 0,
                'subtotal' => 0.0,
                'delivery_fee' => 0.0,
                'minimum_order_amount' => (float) $restaurant->minimum_order_amount,
                'total' => 0.0,
                'currency' => 'AUD',
                'items' => [],
                'restaurant' => $restaurant,
            ];
        }

        $menuItems = MenuItem::query()
            ->with(['category', 'activeSizes', 'activeAddons'])
            ->whereIn('id', collect($cartItems)->pluck('id')->map(fn ($id): int => (int) $id)->unique())
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0.0;
        $count = 0;

        foreach ($cartItems as $cartItem) {
            $menuItem = $menuItems->get((int) ($cartItem['id'] ?? 0));

            if (! $menuItem) {
                throw new BusinessRuleException('One or more cart items are unavailable. Please update your cart.');
            }

            $pricing = $this->pricingService->priceMenuSelection(
                $menuItem,
                isset($cartItem['size_id']) ? (int) $cartItem['size_id'] : null,
                collect($cartItem['addons'] ?? [])->pluck('id')->filter()->map(fn ($id): int => (int) $id)->all(),
                (int) ($cartItem['quantity'] ?? 0),
            );

            $items[] = [
                'menu_item_id' => $pricing['menu_item']->id,
                'name' => $pricing['menu_item']->name,
                'size' => $pricing['size'] ? [
                    'id' => $pricing['size']->id,
                    'name' => $pricing['size']->name,
                    'price' => $pricing['base_price'],
                ] : null,
                'addons' => $pricing['addons']->map(fn ($addon): array => [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'type' => $addon->type,
                    'price' => (float) $addon->price,
                ])->values()->all(),
                'quantity' => $pricing['quantity'],
                'unit_price' => $pricing['unit_price'],
                'base_price' => $pricing['base_price'],
                'addons_total' => $pricing['addons_total'],
                'line_total' => $pricing['line_total'],
            ];

            $subtotal += $pricing['line_total'];
            $count += $pricing['quantity'];
        }

        $subtotal = round($subtotal, 2);
        $deliveryFee = $subtotal > 0 ? round((float) $restaurant->delivery_fee, 2) : 0.0;

        return [
            'count' => $count,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'minimum_order_amount' => round((float) $restaurant->minimum_order_amount, 2),
            'total' => round($subtotal + $deliveryFee, 2),
            'currency' => 'AUD',
            'items' => $items,
            'restaurant' => $restaurant,
        ];
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
