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
     * @return array{addon_ids: array<int, int>, addon_total: float}
     */
    public function validateGroupedSelections(MenuItem $menuItem, array $selections): array
    {
        $groups = $this->groupsForMenuItem($menuItem);
        $selectionMap = $this->normalizeSelections($selections);
        $selectedAddonIds = [];
        $total = 0.0;

        foreach ($groups as $group) {
            $selectedForGroup = collect($selectionMap[$group->id] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values();

            $count = $selectedForGroup->count();
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

            if ($count === 0) {
                continue;
            }

            $availableAddons = $group->activeAddons->keyBy('id');
            $missing = $selectedForGroup->reject(fn (int $id): bool => $availableAddons->has($id));

            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'addon_selections' => "One or more selected options for {$group->name} are unavailable.",
                ]);
            }

            foreach ($selectedForGroup as $addonId) {
                $addon = $availableAddons->get($addonId);
                $selectedAddonIds[] = $addonId;
                $total += (float) $addon->price;
            }
        }

        return [
            'addon_ids' => collect($selectedAddonIds)->unique()->values()->all(),
            'addon_total' => round($total, 2),
        ];
    }

    /**
     * @param  array<int|string, mixed>  $selections
     * @return array<int, int>
     */
    public function legacyAddonIdsForSelections(MenuItem $menuItem, array $selections): array
    {
        $this->validateGroupedSelections($menuItem, $selections);
        $groups = $this->groupsForMenuItem($menuItem)->keyBy('id');
        $selectionMap = $this->normalizeSelections($selections);
        $legacyIds = [];

        foreach ($selectionMap as $groupId => $addonIds) {
            $group = $groups->get((int) $groupId);

            if (! $group) {
                continue;
            }

            $addons = $group->activeAddons->whereIn('id', collect($addonIds)->map(fn ($id): int => (int) $id));

            foreach ($addons as $addon) {
                $legacyIds[] = MenuItemAddon::query()->firstOrCreate(
                    [
                        'menu_item_id' => $menuItem->id,
                        'name' => $addon->name,
                        'price' => $addon->price,
                    ],
                    [
                        'type' => $addon->type === 'upsell' ? 'extra' : 'topping',
                        'sort_order' => $addon->sort_order,
                        'is_active' => $addon->is_active && $addon->is_available,
                    ],
                )->id;
            }
        }

        return collect($legacyIds)->unique()->values()->all();
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
                    return [(int) $value['group_id'] => collect($value['addon_ids'] ?? [])->all()];
                }

                return [(int) $key => is_array($value) ? $value : [$value]];
            })
            ->all();
    }
}
