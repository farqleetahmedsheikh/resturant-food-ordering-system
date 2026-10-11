<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\AddonGroup;
use App\Models\MenuItem;
use App\Models\MenuItemAddon;
use App\Models\MenuItemSize;
use Illuminate\Support\Collection;

class PricingService
{
    public const MAX_QUANTITY = 99;

    /**
     * @param  array<int|string, int|string>  $addonIds
     * @return array{
     *     menu_item: MenuItem,
     *     size: MenuItemSize|null,
     *     addons: Collection<int, MenuItemAddon>,
     *     addon_quantities: array<int, int>,
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

        $menuItem->loadMissing([
            'category.activeAddonGroups.activeAddons',
            'restaurant.globalAddonGroups.activeAddons',
            'activeAddonGroups.activeAddons',
            'activeSizes',
            'activeAddons',
        ]);

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

        $addonQuantities = $this->normalizeAddonQuantities($addonIds);
        $normalizedAddonIds = collect(array_keys($addonQuantities))->values();

        $addons = $menuItem->activeAddons->whereIn('id', $normalizedAddonIds)->values();

        if ($normalizedAddonIds->count() !== $addons->count()) {
            throw new BusinessRuleException('One or more add-ons are unavailable for this menu item.');
        }

        $this->validateGroupedAddonRules($menuItem, $addons, $addonQuantities);

        $basePrice = round((float) ($size?->price ?? $menuItem->price), 2);
        $addonsTotal = round((float) $addons->sum(
            fn (MenuItemAddon $addon): float => (float) $addon->price * ($addonQuantities[$addon->id] ?? 1),
        ), 2);
        $unitPrice = round($basePrice + $addonsTotal, 2);

        return [
            'menu_item' => $menuItem,
            'size' => $size,
            'addons' => $addons,
            'addon_quantities' => $addonQuantities,
            'quantity' => $quantity,
            'base_price' => $basePrice,
            'addons_total' => $addonsTotal,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
        ];
    }

    /**
     * @param  Collection<int, MenuItemAddon>  $addons
     */
    private function validateGroupedAddonRules(MenuItem $menuItem, Collection $addons, array $addonQuantities): void
    {
        $groups = collect()
            ->concat($menuItem->activeAddonGroups)
            ->concat($menuItem->category?->activeAddonGroups ?? [])
            ->concat($menuItem->restaurant?->globalAddonGroups->where('pivot.is_active', true) ?? [])
            ->filter(fn (AddonGroup $group): bool => $group->activeAddons->isNotEmpty())
            ->unique('id');

        if ($groups->isEmpty()) {
            return;
        }

        $selectedKeys = $addons->flatMap(function (MenuItemAddon $addon) use ($addonQuantities): array {
            return array_fill(0, $addonQuantities[$addon->id] ?? 1, $this->addonKey($addon->name, (float) $addon->price));
        })->values();

        foreach ($groups as $group) {
            $groupKeys = $group->activeAddons
                ->map(fn ($addon): string => $this->addonKey($addon->name, (float) $addon->price));

            $count = $selectedKeys->intersect($groupKeys)->count();
            $minimum = $group->is_required ? max(1, (int) $group->min_selections) : (int) $group->min_selections;
            $maximum = $group->selection_type === 'single'
                ? 1
                : ($group->max_selections !== null ? (int) $group->max_selections : null);

            if ($count < $minimum) {
                throw new BusinessRuleException("Please select at least {$minimum} option(s) from {$group->name}.");
            }

            if ($maximum !== null && $count > $maximum) {
                throw new BusinessRuleException("Please select no more than {$maximum} option(s) from {$group->name}.");
            }

            $quantityLimit = max(1, (int) ($group->quantity_limit ?? 1));
            foreach ($addons as $addon) {
                $key = $this->addonKey($addon->name, (float) $addon->price);

                if ($groupKeys->contains($key) && ($addonQuantities[$addon->id] ?? 1) > $quantityLimit) {
                    throw new BusinessRuleException("You can select {$addon->name} up to {$quantityLimit} time(s).");
                }
            }
        }
    }

    /** @param array<int|string, int|string> $addonIds */
    private function normalizeAddonQuantities(array $addonIds): array
    {
        if (array_is_list($addonIds)) {
            return collect($addonIds)
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->countBy()
                ->map(fn ($quantity): int => (int) $quantity)
                ->all();
        }

        return collect($addonIds)
            ->mapWithKeys(fn ($quantity, $id): array => [(int) $id => max(0, (int) $quantity)])
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->all();
    }

    private function addonKey(string $name, float $price): string
    {
        // Group membership is identified by the current add-on name. The legacy
        // bridge uses a zero price for included default ingredients.
        return mb_strtolower(trim($name));
    }
}
