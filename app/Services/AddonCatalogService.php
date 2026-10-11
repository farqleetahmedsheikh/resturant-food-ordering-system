<?php

namespace App\Services;

use App\Models\AddonGroup;
use App\Models\MenuItem;
use App\Models\MenuItemAddon;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AddonCatalogService
{
    /**
     * @return Collection<int, AddonGroup>
     */
    public function groupsForMenuItem(MenuItem $menuItem): Collection
    {
        $menuItem->loadMissing([
            'category.activeAddonGroups.activeAddons',
            'activeAddonGroups.activeAddons',
            'restaurant.globalAddonGroups.activeAddons',
        ]);

        return collect()
            ->concat($menuItem->activeAddonGroups)
            ->concat($menuItem->category?->activeAddonGroups ?? [])
            ->concat($menuItem->restaurant?->globalAddonGroups->where('pivot.is_active', true) ?? [])
            ->filter(fn (AddonGroup $group): bool => $group->activeAddons->isNotEmpty())
            ->unique('id')
            ->sortBy([
                fn (AddonGroup $group): int => (int) ($group->pivot?->sort_order ?? $group->sort_order),
                fn (AddonGroup $group): int => (int) $group->sort_order,
                fn (AddonGroup $group): string => $group->name,
            ])
            ->values();
    }

    /**
     * @param  array<int|string, mixed>  $selections
     * @return array{
     *     addon_ids: array<int, int>,
     *     addon_quantities: array<int, int>,
     *     addon_total: float,
     *     selected_addon_quantities_by_group: array<int, array<int, int>>,
     *     customization_snapshot: array<int, array<string, mixed>>
     * }
     */
    public function validateGroupedSelections(MenuItem $menuItem, array $selections): array
    {
        $groups = $this->groupsForMenuItem($menuItem);
        $selectionMap = $this->normalizeSelections($selections);
        $selectedAddonIds = [];
        $addonQuantities = [];
        $selectedAddonQuantitiesByGroup = [];
        $customizationSnapshot = [];
        $total = 0.0;

        $knownGroupIds = $groups->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $unknownGroups = collect(array_keys($selectionMap))
            ->map(fn ($id): int => (int) $id)
            ->diff($knownGroupIds);

        if ($unknownGroups->isNotEmpty()) {
            throw ValidationException::withMessages([
                'addon_selections' => 'One or more customization groups are not available for this item.',
            ]);
        }

        foreach ($groups as $group) {
            $availableAddons = $group->activeAddons->keyBy('id');
            $defaultQuantities = $availableAddons
                ->filter(fn ($addon): bool => (bool) ($addon->pivot?->is_default ?? false))
                ->mapWithKeys(fn ($addon): array => [(int) $addon->id => 1])
                ->all();
            $selectedForGroup = collect(array_key_exists($group->id, $selectionMap)
                ? $selectionMap[$group->id]
                : $defaultQuantities)
                ->mapWithKeys(fn ($quantity, $id): array => [(int) $id => max(0, (int) $quantity)])
                ->filter(fn (int $quantity): bool => $quantity > 0);

            $count = (int) $selectedForGroup->sum();
            $minimum = $group->is_required ? max(1, (int) $group->min_selections) : (int) $group->min_selections;
            $maximum = $group->selection_type === 'single'
                ? 1
                : ($group->max_selections !== null ? (int) $group->max_selections : null);

            if ($count < $minimum) {
                throw ValidationException::withMessages([
                    'addon_selections' => "Please select at least {$minimum} option(s) from {$group->name}.",
                ]);
            }

            if ($maximum !== null && $count > $maximum) {
                throw ValidationException::withMessages([
                    'addon_selections' => "Please select no more than {$maximum} option(s) from {$group->name}.",
                ]);
            }

            $missing = $selectedForGroup->keys()->reject(fn (int $id): bool => $availableAddons->has($id));

            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'addon_selections' => "One or more selected options for {$group->name} are unavailable.",
                ]);
            }

            $selectedAddonQuantitiesByGroup[$group->id] = $selectedForGroup->all();
            $selectedIds = $selectedForGroup->keys()->map(fn ($id): int => (int) $id);
            $selectedOptions = $selectedForGroup->map(function (int $quantity, int $addonId) use ($availableAddons): array {
                $addon = $availableAddons->get($addonId);
                $isDefault = (bool) ($addon->pivot?->is_default ?? false);

                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'type' => $addon->type,
                    'price' => $isDefault ? 0.0 : (float) $addon->price,
                    'quantity' => $quantity,
                    'is_default' => $isDefault,
                ];
            })->values()->all();
            $removedDefaults = $availableAddons
                ->filter(fn ($addon): bool => (bool) ($addon->pivot?->is_default ?? false) && ! $selectedIds->contains($addon->id))
                ->map(fn ($addon): array => [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'type' => $addon->type,
                    'price' => 0.0,
                    'quantity' => 0,
                    'is_default' => true,
                ])->values()->all();

            $customizationSnapshot[] = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'selected' => $selectedOptions,
                'removed' => $removedDefaults,
            ];

            foreach ($selectedForGroup as $addonId => $quantity) {
                $addon = $availableAddons->get($addonId);
                $quantityLimit = max(1, (int) ($group->quantity_limit ?? 1));

                if ($quantity > $quantityLimit) {
                    throw ValidationException::withMessages([
                        'addon_selections' => "You can select {$addon->name} up to {$quantityLimit} time(s).",
                    ]);
                }

                $selectedAddonIds[] = $addonId;
                $addonQuantities[$addonId] = ($addonQuantities[$addonId] ?? 0) + $quantity;
                $total += ($this->isDefaultAddon($addon) ? 0.0 : (float) $addon->price) * $quantity;
            }
        }

        return [
            'addon_ids' => collect($selectedAddonIds)->unique()->values()->all(),
            'addon_quantities' => $addonQuantities,
            'addon_total' => round($total, 2),
            'selected_addon_quantities_by_group' => $selectedAddonQuantitiesByGroup,
            'customization_snapshot' => $customizationSnapshot,
        ];
    }

    /**
     * @param  array<int|string, mixed>  $selections
     * @return array<int, int>
     */
    public function legacyAddonIdsForSelections(MenuItem $menuItem, array $selections): array
    {
        return array_keys($this->legacyAddonQuantitiesForSelections($menuItem, $selections));
    }

    /**
     * Convert reusable group selections into the legacy item add-ons used by carts and orders.
     * The returned values are quantities, not prices; prices are always read from the database.
     *
     * @return array<int, int>
     */
    public function legacyAddonQuantitiesForSelections(MenuItem $menuItem, array $selections): array
    {
        return $this->legacyAddonDataForSelections($menuItem, $selections)['addon_quantities'];
    }

    /**
     * @return array{addon_quantities: array<int, int>, customization_snapshot: array<int, array<string, mixed>>}
     */
    public function legacyAddonDataForSelections(MenuItem $menuItem, array $selections): array
    {
        $validated = $this->validateGroupedSelections($menuItem, $selections);
        $groups = $this->groupsForMenuItem($menuItem)->keyBy('id');
        $legacyIds = [];

        foreach ($validated['selected_addon_quantities_by_group'] as $groupId => $addonQuantities) {
            $group = $groups->get((int) $groupId);

            foreach ($group?->activeAddons->whereIn('id', array_keys($addonQuantities)) ?? [] as $addon) {
                $legacyAddon = MenuItemAddon::query()->firstOrCreate(
                    [
                        'menu_item_id' => $menuItem->id,
                        'name' => $addon->name,
                        'price' => $this->isDefaultAddon($addon) ? 0 : $addon->price,
                    ],
                    [
                        'type' => $addon->type === 'upsell' ? 'extra' : 'topping',
                        'sort_order' => $addon->sort_order,
                        'is_active' => $addon->is_active && $addon->is_available,
                    ],
                );
                $legacyIds[$legacyAddon->id] = ($legacyIds[$legacyAddon->id] ?? 0)
                    + (int) ($addonQuantities[$addon->id] ?? 1);
            }
        }

        return [
            'addon_quantities' => $legacyIds,
            'customization_snapshot' => $validated['customization_snapshot'],
        ];
    }

    /**
     * @return Collection<int, AddonGroup>
     */
    public function activeGroups(?Restaurant $restaurant = null): Collection
    {
        return AddonGroup::query()
            ->with('activeAddons')
            ->when($restaurant, fn ($query) => $query->where(function ($scope) use ($restaurant): void {
                $scope->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
            }))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int|string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function normalizeSelections(array $selections): array
    {
        return collect($selections)
            ->mapWithKeys(function ($value, $key): array {
                if (is_array($value) && array_key_exists('group_id', $value)) {
                    return [(int) $value['group_id'] => $this->normalizeAddonValues($value['addon_ids'] ?? [])];
                }

                return [(int) $key => $this->normalizeAddonValues($value)];
            })
            ->all();
    }

    /** @return array<int, int> */
    private function normalizeAddonValues(mixed $value): array
    {
        if (! is_array($value)) {
            return [(int) $value => 1];
        }

        // Accept both [12, 13] and {"12": 2, "13": 1} payloads.
        if (array_is_list($value)) {
            return collect($value)
                ->mapWithKeys(fn ($id): array => [(int) $id => 1])
                ->all();
        }

        return collect($value)
            ->mapWithKeys(fn ($quantity, $id): array => [(int) $id => max(0, (int) $quantity)])
            ->all();
    }

    private function isDefaultAddon(object $addon): bool
    {
        return (bool) ($addon->pivot?->is_default ?? false);
    }
}
