<?php

namespace App\Services\Cart;

use App\Exceptions\BusinessRuleException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

class DatabaseCartService
{
    public function __construct(private PricingService $pricingService) {}

    public function cartFor(User $user): Cart
    {
        $restaurant = Restaurant::current();

        if (! $restaurant) {
            throw new BusinessRuleException('Restaurant is not available right now.');
        }

        return Cart::query()->firstOrCreate(
            ['active_cart_key' => $this->cartKey($user, $restaurant)],
            [
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'status' => Cart::STATUS_ACTIVE,
            ],
        );
    }

    public function get(User $user): Cart
    {
        return $this->cartFor($user)->load($this->relationships());
    }

    /**
     * @param  array<int, int|string>  $addonIds
     */
    public function add(User $user, MenuItem $menuItem, ?int $sizeId, array $addonIds, int $quantity): Cart
    {
        return DB::transaction(function () use ($user, $menuItem, $sizeId, $addonIds, $quantity): Cart {
            $cart = $this->lockedCart($user);
            $pricing = $this->pricingService->priceMenuSelection($menuItem, $sizeId, $addonIds, $quantity);
            $lineHash = $this->lineHash($menuItem->id, $pricing['size']?->id, $pricing['addons']->pluck('id')->all());

            $cartItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('line_hash', $lineHash)
                ->first();

            if ($cartItem) {
                $newQuantity = min(PricingService::MAX_QUANTITY, $cartItem->quantity + $quantity);
                $this->pricingService->priceMenuSelection($menuItem, $sizeId, $addonIds, $newQuantity);
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $cartItem = $cart->items()->create([
                    'menu_item_id' => $menuItem->id,
                    'menu_item_size_id' => $pricing['size']?->id,
                    'line_hash' => $lineHash,
                    'quantity' => $quantity,
                ]);
            }

            $cartItem->addons()->sync($pricing['addons']->pluck('id')->all());

            return $this->freshCart($cart);
        });
    }

    public function update(User $user, CartItem $cartItem, int $quantity): Cart
    {
        return DB::transaction(function () use ($user, $cartItem, $quantity): Cart {
            $cart = $this->lockedCart($user);
            $cartItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->with(['menuItem', 'addons'])
                ->findOrFail($cartItem->id);

            if ($quantity < 1) {
                $cartItem->delete();

                return $this->freshCart($cart);
            }

            $this->pricingService->priceMenuSelection(
                $cartItem->menuItem,
                $cartItem->menu_item_size_id,
                $cartItem->addons->pluck('id')->all(),
                $quantity,
            );

            $cartItem->update(['quantity' => $quantity]);

            return $this->freshCart($cart);
        });
    }

    public function remove(User $user, CartItem $cartItem): Cart
    {
        return DB::transaction(function () use ($user, $cartItem): Cart {
            $cart = $this->lockedCart($user);

            CartItem::query()
                ->where('cart_id', $cart->id)
                ->whereKey($cartItem->id)
                ->delete();

            return $this->freshCart($cart);
        });
    }

    public function clear(User $user): Cart
    {
        return DB::transaction(function () use ($user): Cart {
            $cart = $this->lockedCart($user);
            $cart->items()->delete();

            return $this->freshCart($cart);
        });
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
     *     restaurant: Restaurant|null
     * }
     */
    public function summary(Cart $cart): array
    {
        $cart->loadMissing($this->relationships());

        $items = [];
        $subtotal = 0.0;
        $count = 0;

        foreach ($cart->items as $cartItem) {
            $pricing = $this->pricingService->priceMenuSelection(
                $cartItem->menuItem,
                $cartItem->menu_item_size_id,
                $cartItem->addons->pluck('id')->all(),
                $cartItem->quantity,
            );

            $items[] = [
                'id' => $cartItem->id,
                'menu_item_id' => $pricing['menu_item']->id,
                'name' => $pricing['menu_item']->name,
                'image_url' => $pricing['menu_item']->image_url,
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

        $restaurant = $cart->restaurant;
        $deliveryFee = round((float) ($restaurant?->delivery_fee ?? 0), 2);
        $subtotal = round($subtotal, 2);

        return [
            'count' => $count,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'minimum_order_amount' => round((float) ($restaurant?->minimum_order_amount ?? 0), 2),
            'total' => round($subtotal + $deliveryFee, 2),
            'currency' => \App\Support\Money::code(),
            'items' => $items,
            'restaurant' => $restaurant,
        ];
    }

    public function markConverted(Cart $cart): void
    {
        $cart->update([
            'status' => Cart::STATUS_CONVERTED,
            'active_cart_key' => null,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function relationships(): array
    {
        return [
            'restaurant',
            'items.menuItem.category',
            'items.menuItem.activeSizes',
            'items.menuItem.activeAddons',
            'items.size',
            'items.addons',
        ];
    }

    private function lockedCart(User $user): Cart
    {
        $cart = $this->cartFor($user);

        return Cart::query()
            ->whereKey($cart->id)
            ->where('user_id', $user->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function freshCart(Cart $cart): Cart
    {
        return $cart->fresh($this->relationships());
    }

    private function cartKey(User $user, Restaurant $restaurant): string
    {
        return $user->id.':'.$restaurant->id;
    }

    /**
     * @param  array<int, int|string>  $addonIds
     */
    private function lineHash(int $menuItemId, ?int $sizeId, array $addonIds): string
    {
        $normalizedAddonIds = collect($addonIds)->map(fn ($id): int => (int) $id)->sort()->values()->all();

        return hash('sha256', json_encode([
            'menu_item_id' => $menuItemId,
            'size_id' => $sizeId,
            'addon_ids' => $normalizedAddonIds,
        ]));
    }
}
