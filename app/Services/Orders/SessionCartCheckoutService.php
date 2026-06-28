<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\MenuItem;
use App\Models\Order;
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

        $order = DB::transaction(function () use ($user, $payload, $restaurant, $summary): Order {
            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $payload['customer_name'],
                'customer_phone' => $payload['customer_phone'],
                'customer_email' => $payload['customer_email'] ?? null,
                'delivery_address' => $payload['delivery_address'],
                'delivery_latitude' => $payload['delivery_latitude'] ?? null,
                'delivery_longitude' => $payload['delivery_longitude'] ?? null,
                'order_notes' => $payload['order_notes'] ?? null,
                'subtotal' => $summary['subtotal'],
                'delivery_fee' => $summary['delivery_fee'],
                'total' => $summary['total'],
                'currency' => $summary['currency'],
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'order_status' => 'pending_payment',
            ]);

            foreach ($summary['items'] as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'item_name' => $item['name'],
                    'size_name' => $item['size']['name'] ?? null,
                    'size_price' => $item['size']['price'] ?? null,
                    'addons_snapshot' => $item['addons'],
                    'addons_total' => $item['addons_total'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'total' => $item['line_total'],
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => null,
                'new_status' => 'pending_payment',
                'changed_by_user_id' => $user->id,
                'changed_by_role' => $user->role,
                'reason' => 'Stripe Checkout session requested',
                'metadata' => ['source' => 'website'],
            ]);

            return $order->load(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });

        $session = $this->stripeCheckoutService->createForOrder($order);

        return new CheckoutResult(
            $order->refresh()->load(['items', 'user', 'rider', 'delivery', 'statusHistories']),
            $session->url,
            $session->id,
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

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
