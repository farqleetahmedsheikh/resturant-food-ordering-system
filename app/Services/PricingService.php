<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\MenuItem;
use App\Models\MenuItemAddon;
use App\Models\MenuItemSize;
use Illuminate\Support\Collection;

class PricingService
{
    public const MAX_QUANTITY = 99;

    /**
     * @param  array<int, int|string>  $addonIds
     * @return array{
     *     menu_item: MenuItem,
     *     size: MenuItemSize|null,
     *     addons: Collection<int, MenuItemAddon>,
     *     quantity: int,
     *     base_price: float,
     *     addons_total: float,
     *     unit_price: float,
     *     line_total: float
     * }
     */
    public function priceMenuSelection(MenuItem $menuItem, ?int $sizeId, array $addonIds, int $quantity): array
    {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw new BusinessRuleException('Quantity must be between 1 and '.self::MAX_QUANTITY.'.');
        }

        $menuItem->loadMissing(['category', 'activeSizes', 'activeAddons']);

        if (! $menuItem->is_available || ($menuItem->category && ! $menuItem->category->is_active)) {
            throw new BusinessRuleException('This menu item is currently unavailable.');
        }

        $size = null;

        if ($menuItem->activeSizes->isNotEmpty()) {
            if (! $sizeId) {
                throw new BusinessRuleException('Please choose a size for this menu item.');
            }

            $size = $menuItem->activeSizes->firstWhere('id', $sizeId);

            if (! $size) {
                throw new BusinessRuleException('The selected size is unavailable for this menu item.');
            }
        } elseif ($sizeId) {
            throw new BusinessRuleException('This menu item does not support the selected size.');
        }

        $normalizedAddonIds = collect($addonIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $addons = $menuItem->activeAddons->whereIn('id', $normalizedAddonIds)->values();

        if ($normalizedAddonIds->count() !== $addons->count()) {
            throw new BusinessRuleException('One or more add-ons are unavailable for this menu item.');
        }

        $basePrice = round((float) ($size?->price ?? $menuItem->price), 2);
        $addonsTotal = round((float) $addons->sum(fn (MenuItemAddon $addon): float => (float) $addon->price), 2);
        $unitPrice = round($basePrice + $addonsTotal, 2);

        return [
            'menu_item' => $menuItem,
            'size' => $size,
            'addons' => $addons,
            'quantity' => $quantity,
            'base_price' => $basePrice,
            'addons_total' => $addonsTotal,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
        ];
    }
}
