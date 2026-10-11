<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\UpsellRule;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class UpsellRecommendationService
{
    /**
     * @param  array<int, array<string, mixed>>  $summaryItems
     * @return EloquentCollection<int, MenuItem>
     */
    public function forSummary(array $summaryItems, string $placement = 'cart', int $limit = 6): EloquentCollection
    {
        $restaurant = Restaurant::current();
        $cartProductIds = collect($summaryItems)->pluck('menu_item_id')->filter()->map(fn ($id): int => (int) $id)->unique();
        $cartProducts = MenuItem::query()
            ->with('category')
            ->whereIn('id', $cartProductIds)
            ->get();
        $subtotal = round((float) collect($summaryItems)->sum('line_total'), 2);

        return $this->eligibleProducts($restaurant, $cartProducts, $subtotal, $placement, $limit);
    }

    /**
     * @return EloquentCollection<int, MenuItem>
     */
    public function forCart(Cart $cart, string $placement = 'cart', int $limit = 6): EloquentCollection
    {
        $cart->loadMissing(['items.menuItem.category', 'restaurant']);

        $cartProducts = $cart->items->pluck('menuItem')->filter()->values();
        $subtotal = round((float) $cart->items->sum(fn ($item): float => (float) $item->menuItem?->price * (int) $item->quantity), 2);

        return $this->eligibleProducts($cart->restaurant, $cartProducts, $subtotal, $placement, $limit);
    }

    /**
     * @param  Collection<int, MenuItem>  $cartProducts
     * @return EloquentCollection<int, MenuItem>
     */
    private function eligibleProducts(?Restaurant $restaurant, Collection $cartProducts, float $subtotal, string $placement, int $limit): EloquentCollection
    {
        $cartProductIds = $cartProducts->pluck('id')->map(fn ($id): int => (int) $id)->unique()->values();
        $cartCategoryIds = $cartProducts->pluck('category_id')->filter()->map(fn ($id): int => (int) $id)->unique()->values();
        $now = now();

        $rules = UpsellRule::query()
            ->with(['products.category', 'products.activeSizes', 'products.activeAddons'])
            ->where('is_active', true)
            ->where('placement', $placement)
            ->when($restaurant, fn ($query) => $query->where(function ($scope) use ($restaurant): void {
                $scope->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurant->id);
            }))
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();

        $recommendations = collect();

        foreach ($rules as $rule) {
            if (! $this->matchesRule($rule, $cartProductIds, $cartCategoryIds, $subtotal)) {
                continue;
            }

            $ruleProducts = $rule->products
                ->where('is_available', true)
                ->filter(fn (MenuItem $product): bool => $product->category === null || (bool) $product->category->is_active)
                ->when(! $rule->allow_duplicates, fn (Collection $products) => $products->reject(fn (MenuItem $product): bool => $cartProductIds->contains($product->id)));

            $max = $rule->max_recommendations ?: $limit;

            foreach ($ruleProducts->take($max) as $product) {
                if (! $rule->allow_duplicates && $recommendations->has($product->id)) {
                    continue;
                }

                $recommendations->put($product->id, $product);
            }

            if ($recommendations->count() >= $limit) {
                break;
            }
        }

        return new EloquentCollection($recommendations->take($limit)->values()->all());
    }

    /**
     * @param  Collection<int, int>  $cartProductIds
     * @param  Collection<int, int>  $cartCategoryIds
     */
    private function matchesRule(UpsellRule $rule, Collection $cartProductIds, Collection $cartCategoryIds, float $subtotal): bool
    {
        $config = $rule->trigger_config ?? [];

        return match ($rule->trigger_type) {
            'product' => $cartProductIds->intersect($this->ids($config['product_ids'] ?? []))->isNotEmpty(),
            'category' => $cartCategoryIds->intersect($this->ids($config['category_ids'] ?? []))->isNotEmpty(),
            'subtotal' => $subtotal >= (float) ($config['min_subtotal'] ?? 0),
            'missing_category' => ! $cartCategoryIds->contains((int) ($config['category_id'] ?? 0)),
            'product_combo' => $this->ids($config['product_ids'] ?? [])->diff($cartProductIds)->isEmpty(),
            default => true,
        };
    }

    /**
     * @param  mixed  $value
     * @return Collection<int, int>
     */
    private function ids(mixed $value): Collection
    {
        return collect(is_array($value) ? $value : [$value])
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
    }
}
