<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class SmartMenuSuggestionService
{
    /**
     * @param  array<int|string, array<string, mixed>>  $cartItems
     * @return EloquentCollection<int, MenuItem>
     */
    public function forCart(array $cartItems, int $limit = 4): EloquentCollection
    {
        $cartMenuItemIds = collect($cartItems)->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();

        $categoryNames = MenuItem::query()
            ->with('category')
            ->whereIn('id', $cartMenuItemIds)
            ->get()
            ->pluck('category.name')
            ->filter()
            ->map(fn (string $name) => str($name)->lower()->toString());

        $preferredCategories = $this->preferredCategories($categoryNames);

        return $this->baseSuggestionQuery($cartMenuItemIds)
            ->when($preferredCategories->isNotEmpty(), function ($query) use ($preferredCategories): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->whereIn('name', $preferredCategories));
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return EloquentCollection<int, MenuItem>
     */
    public function forItem(MenuItem $menuItem, int $limit = 4): EloquentCollection
    {
        $categoryName = str($menuItem->category?->name ?? '')->lower()->toString();

        $preferredCategories = $this->preferredCategories(collect([$categoryName]));

        return $this->baseSuggestionQuery(collect([$menuItem->id]))
            ->when($preferredCategories->isNotEmpty(), function ($query) use ($preferredCategories): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->whereIn('name', $preferredCategories));
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, int|string>  $excludedMenuItemIds
     */
    private function baseSuggestionQuery(Collection $excludedMenuItemIds)
    {
        return MenuItem::query()
            ->with('category')
            ->withCount([
                'activeSizes',
                'activeAddons',
            ])
            ->where('is_available', true)
            ->whereNotIn('id', $excludedMenuItemIds)
            ->where(function ($query): void {
                $query->whereNull('category_id')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            });
    }

    /**
     * @param  Collection<int, string>  $categoryNames
     * @return Collection<int, string>
     */
    private function preferredCategories(Collection $categoryNames): Collection
    {
        if ($categoryNames->contains(fn (string $name) => in_array($name, ['pizza', 'burgers', 'pasta'], true))) {
            return collect(['Drinks', 'Desserts']);
        }

        if ($categoryNames->contains('drinks')) {
            return collect(['Pizza', 'Burgers', 'Pasta', 'Desserts']);
        }

        return collect(['Drinks', 'Desserts']);
    }
}
