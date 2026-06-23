<?php

namespace App\Support;

use App\Models\MenuItemAddon;
use App\Models\MenuItem;
use App\Models\MenuItemSize;
use App\Models\Restaurant;
use Illuminate\Support\Collection;

class Cart
{
    private const SESSION_KEY = 'cart.items';

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public static function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @param  iterable<int, MenuItemAddon>  $addons
     */
    public static function add(MenuItem $menuItem, int $quantity = 1, ?MenuItemSize $size = null, iterable $addons = []): void
    {
        $items = self::items();
        $selectedAddons = collect($addons)->sortBy('id')->values();
        $addonIds = $selectedAddons->pluck('id')->map(fn ($id) => (int) $id)->all();
        $key = self::lineKey($menuItem->id, $size?->id, $addonIds);
        $basePrice = round((float) ($size?->price ?? $menuItem->price), 2);
        $addonsTotal = round($selectedAddons->sum(fn (MenuItemAddon $addon): float => (float) $addon->price), 2);
        $unitPrice = round($basePrice + $addonsTotal, 2);

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'cart_key' => $key,
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'description' => $menuItem->description,
                'base_price' => $basePrice,
                'price' => $unitPrice,
                'quantity' => $quantity,
                'image' => $menuItem->image,
                'size_id' => $size?->id,
                'size_name' => $size?->name,
                'size_price' => $size?->price ? (float) $size->price : null,
                'addons' => $selectedAddons->map(fn (MenuItemAddon $addon): array => [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'type' => $addon->type,
                    'price' => (float) $addon->price,
                ])->all(),
                'addons_total' => $addonsTotal,
            ];
        }

        self::store($items);
    }

    public static function update(int|string $item, int $quantity): void
    {
        $items = self::items();
        $id = (string) $item;

        if (! isset($items[$id])) {
            return;
        }

        $items[$id]['quantity'] = $quantity;

        self::store($items);
    }

    public static function remove(int|string $item): void
    {
        $items = self::items();
        unset($items[(string) $item]);

        self::store($items);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function count(): int
    {
        return array_sum(array_map(
            fn (array $item): int => (int) $item['quantity'],
            self::items(),
        ));
    }

    public static function subtotal(): float
    {
        return round(array_sum(array_map(
            fn (array $item): float => (float) ($item['total'] ?? ((float) $item['price'] * (int) $item['quantity'])),
            self::items(),
        )), 2);
    }

    public static function deliveryFee(?Restaurant $restaurant = null): float
    {
        $restaurant ??= self::restaurant();

        return self::subtotal() > 0 ? round((float) ($restaurant?->delivery_fee ?? 0), 2) : 0.00;
    }

    public static function total(?Restaurant $restaurant = null): float
    {
        return round(self::subtotal() + self::deliveryFee($restaurant), 2);
    }

    /**
     * @return array{items: array<int|string, array<string, mixed>>, count: int, subtotal: float, delivery_fee: float, total: float}
     */
    public static function summary(?Restaurant $restaurant = null): array
    {
        $restaurant ??= self::restaurant();

        return [
            'items' => self::items(),
            'count' => self::count(),
            'subtotal' => self::subtotal(),
            'delivery_fee' => self::deliveryFee($restaurant),
            'total' => self::total($restaurant),
        ];
    }

    public static function restaurant(): ?Restaurant
    {
        return Restaurant::where('is_active', true)->first();
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $items
     */
    private static function store(array $items): void
    {
        $normalized = [];

        foreach ($items as $id => $item) {
            $quantity = max(0, (int) ($item['quantity'] ?? 0));

            if ($quantity < 1) {
                continue;
            }

            $basePrice = round((float) ($item['base_price'] ?? $item['price'] ?? 0), 2);
            $addons = self::normalizeAddons($item['addons'] ?? []);
            $addonsTotal = round((float) ($item['addons_total'] ?? $addons->sum(fn (array $addon): float => (float) $addon['price'])), 2);
            $price = round((float) ($item['price'] ?? $basePrice + $addonsTotal), 2);
            $cartKey = (string) ($item['cart_key'] ?? $id);

            $normalized[$cartKey] = [
                'cart_key' => $cartKey,
                'id' => (int) ($item['id'] ?? $id),
                'name' => (string) ($item['name'] ?? 'Menu item'),
                'description' => $item['description'] ?? null,
                'base_price' => $basePrice,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $item['image'] ?? null,
                'size_id' => isset($item['size_id']) ? (int) $item['size_id'] : null,
                'size_name' => $item['size_name'] ?? null,
                'size_price' => isset($item['size_price']) ? (float) $item['size_price'] : null,
                'addons' => $addons->all(),
                'addons_total' => $addonsTotal,
                'total' => round($price * $quantity, 2),
            ];
        }

        if ($normalized === []) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        session()->put(self::SESSION_KEY, $normalized);
    }

    /**
     * @param  array<int, int>  $addonIds
     */
    private static function lineKey(int $menuItemId, ?int $sizeId = null, array $addonIds = []): string
    {
        sort($addonIds);

        return md5(json_encode([
            'menu_item_id' => $menuItemId,
            'size_id' => $sizeId,
            'addon_ids' => $addonIds,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param  mixed  $addons
     * @return Collection<int, array{id: int|null, name: string, type: string|null, price: float}>
     */
    private static function normalizeAddons(mixed $addons): Collection
    {
        return collect(is_array($addons) ? $addons : [])
            ->map(fn (array $addon): array => [
                'id' => isset($addon['id']) ? (int) $addon['id'] : null,
                'name' => (string) ($addon['name'] ?? 'Add-on'),
                'type' => $addon['type'] ?? null,
                'price' => round((float) ($addon['price'] ?? 0), 2),
            ])
            ->values();
    }
}
