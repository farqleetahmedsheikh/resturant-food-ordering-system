<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemAddon;
use App\Models\Restaurant;
use App\Models\UpsellRule;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAddonUpsellController extends Controller
{
    public function index(Request $request): View
    {
        return $this->page($request, 'overview');
    }

    public function addons(Request $request): View
    {
        return $this->page($request, 'addons');
    }

    public function groups(Request $request): View
    {
        return $this->page($request, 'groups');
    }

    public function assignments(Request $request): View
    {
        return $this->page($request, 'assignments');
    }

    public function rules(Request $request): View
    {
        return $this->page($request, 'rules');
    }

    private function page(Request $request, string $section): View
    {
        $restaurant = Restaurant::current();
        $addonSorts = [
            'name' => ['name', 'asc'],
            'price' => ['price', 'asc'],
            'type' => ['type', 'asc'],
            'newest' => ['created_at', 'desc'],
        ];
        [$addonSortColumn, $addonSortDirection] = $addonSorts[$request->string('sort', 'name')->toString()] ?? $addonSorts['name'];

        $menuItemsQuery = MenuItem::query()
            ->with(['category', 'activeAddonGroups.activeAddons'])
            ->where('is_available', true)
            ->when($request->filled('product_q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('product_q')->toString().'%'))
            ->orderBy('name');

        $menuItems = $menuItemsQuery->get();
        $selectedProduct = null;

        if ($section === 'assignments') {
            $selectedProduct = MenuItem::query()
                ->with(['category', 'activeAddonGroups.activeAddons', 'category.activeAddonGroups.activeAddons', 'restaurant.globalAddonGroups.activeAddons'])
                ->when($request->integer('product_id') > 0, fn ($query) => $query->whereKey($request->integer('product_id')))
                ->where('is_available', true)
                ->orderBy('name')
                ->first()
                ?? MenuItem::query()
                    ->with(['category', 'activeAddonGroups.activeAddons', 'category.activeAddonGroups.activeAddons', 'restaurant.globalAddonGroups.activeAddons'])
                    ->where('is_available', true)
                    ->orderBy('name')
                    ->first();
        }

        return view('admin.addons-upselling', [
            'section' => $section,
            'restaurant' => $restaurant,
            'addons' => Addon::query()
                ->withCount(['groups as assigned_groups_count'])
                ->whereNull('archived_at')
                ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
                ->when($request->filled('status'), function ($query) use ($request): void {
                    if ($request->string('status')->toString() === 'active') {
                        $query->where('is_active', true);
                    }

                    if ($request->string('status')->toString() === 'inactive') {
                        $query->where('is_active', false);
                    }
                })
                ->when($request->filled('availability'), function ($query) use ($request): void {
                    if ($request->string('availability')->toString() === 'available') {
                        $query->where('is_available', true);
                    }

                    if ($request->string('availability')->toString() === 'unavailable') {
                        $query->where('is_available', false);
                    }
                })
                ->orderBy($addonSortColumn, $addonSortDirection)
                ->orderBy('name')
                ->when($section === 'addons', fn ($query) => $query->paginate(15)->withQueryString(), fn ($query) => $query->get()),
            'groups' => AddonGroup::query()
                ->with(['addons', 'menuItems', 'categories'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'rules' => UpsellRule::query()
                ->with('products')
                ->orderByDesc('priority')
                ->orderBy('name')
                ->get(),
            'menuItems' => $menuItems,
            'selectedProduct' => $selectedProduct,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'addonTypes' => Addon::TYPES,
            'selectionTypes' => AddonGroup::SELECTION_TYPES,
            'triggers' => UpsellRule::TRIGGERS,
            'placements' => UpsellRule::PLACEMENTS,
            'addonTotal' => Addon::query()->whereNull('archived_at')->count(),
            'groupTotal' => AddonGroup::query()->count(),
            'ruleTotal' => UpsellRule::query()->count(),
        ]);
    }

    public function storeAddon(Request $request): RedirectResponse
    {
        $validated = $this->validateAddon($request);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'addons');
        }

        $validated['restaurant_id'] = $validated['restaurant_id'] ?? Restaurant::current()?->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_available'] = $request->boolean('is_available', true);

        Addon::create($validated);

        return back()->with('status', 'Add-on created.');
    }

    public function updateAddon(Request $request, Addon $addon): RedirectResponse
    {
        $validated = $this->validateAddon($request);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'addons', $addon->image);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_available'] = $request->boolean('is_available');

        $addon->update($validated);

        return back()->with('status', 'Add-on updated.');
    }

    public function destroyAddon(Addon $addon): RedirectResponse
    {
        $addon->update(['is_active' => false, 'is_available' => false, 'archived_at' => now()]);

        return back()->with('status', 'Add-on archived.');
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $this->validateGroup($request);
        $validated['restaurant_id'] = $validated['restaurant_id'] ?? Restaurant::current()?->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $group = AddonGroup::create($validated);
        $this->syncGroupAddons($group, $request->input('addon_ids', []), $request->input('default_addon_ids', []));

        return back()->with('status', 'Add-on group created.');
    }

    public function updateGroup(Request $request, AddonGroup $group): RedirectResponse
    {
        $validated = $this->validateGroup($request);
        $validated['is_active'] = $request->boolean('is_active');

        $group->update($validated);
        $this->syncGroupAddons($group, $request->input('addon_ids', []), $request->input('default_addon_ids', []));

        return back()->with('status', 'Add-on group updated.');
    }

    public function assignProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'addon_group_ids' => ['nullable', 'array'],
            'addon_group_ids.*' => ['integer', 'exists:addon_groups,id'],
        ]);

        $menuItem = MenuItem::query()->findOrFail($validated['menu_item_id']);
        $ids = collect($validated['addon_group_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique();

        $menuItem->addonGroups()->sync($ids->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['sort_order' => $index, 'is_active' => true],
        ])->all());

        $this->syncLegacyAddonsForMenuItem($menuItem);

        return back()->with('status', 'Product add-on groups updated.');
    }

    public function assignCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'addon_group_ids' => ['nullable', 'array'],
            'addon_group_ids.*' => ['integer', 'exists:addon_groups,id'],
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
        $ids = collect($validated['addon_group_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique();

        $category->addonGroups()->sync($ids->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['sort_order' => $index, 'is_active' => true],
        ])->all());

        $category->menuItems()->get()->each(fn (MenuItem $menuItem) => $this->syncLegacyAddonsForMenuItem($menuItem));

        return back()->with('status', 'Category add-on groups updated.');
    }

    public function assignGlobal(Request $request): RedirectResponse
    {
        $restaurant = Restaurant::current();
        abort_unless($restaurant, 404);

        $validated = $request->validate([
            'addon_group_ids' => ['nullable', 'array'],
            'addon_group_ids.*' => ['integer', 'exists:addon_groups,id'],
        ]);

        $ids = collect($validated['addon_group_ids'] ?? [])->map(fn ($id): int => (int) $id)->unique();
        $restaurant->globalAddonGroups()->sync($ids->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['sort_order' => $index, 'is_active' => true],
        ])->all());

        MenuItem::query()->where('restaurant_id', $restaurant->id)->get()->each(fn (MenuItem $menuItem) => $this->syncLegacyAddonsForMenuItem($menuItem));

        return back()->with('status', 'Global add-on groups updated.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $this->validateRule($request);
        $validated['restaurant_id'] = $validated['restaurant_id'] ?? Restaurant::current()?->id;
        $validated['trigger_config'] = $this->triggerConfig($validated, $request);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['allow_duplicates'] = $request->boolean('allow_duplicates');

        DB::transaction(function () use ($validated, $request): void {
            $rule = UpsellRule::create($validated);
            $this->syncRuleProducts($rule, $request->input('recommended_product_ids', []));
        });

        return back()->with('status', 'Upselling rule created.');
    }

    public function updateRule(Request $request, UpsellRule $rule): RedirectResponse
    {
        $validated = $this->validateRule($request);
        $validated['trigger_config'] = $this->triggerConfig($validated, $request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['allow_duplicates'] = $request->boolean('allow_duplicates');

        DB::transaction(function () use ($rule, $validated, $request): void {
            $rule->update($validated);
            $this->syncRuleProducts($rule, $request->input('recommended_product_ids', []));
        });

        return back()->with('status', 'Upselling rule updated.');
    }

    public function destroyRule(UpsellRule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('status', 'Upselling rule deleted.');
    }

    private function validateAddon(Request $request): array
    {
        return $request->validate([
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Addon::TYPES))],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ImageUpload::validationRules(),
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateGroup(Request $request): array
    {
        return $request->validate([
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selection_type' => ['required', Rule::in(array_keys(AddonGroup::SELECTION_TYPES))],
            'is_required' => ['nullable', 'boolean'],
            'min_selections' => ['nullable', 'integer', 'min:0'],
            'max_selections' => ['nullable', 'integer', 'min:1'],
            'quantity_limit' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:addons,id'],
            'default_addon_ids' => ['nullable', 'array'],
            'default_addon_ids.*' => ['integer', 'exists:addons,id'],
        ]);
    }

    private function validateRule(Request $request): array
    {
        return $request->validate([
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'name' => ['required', 'string', 'max:255'],
            'trigger_type' => ['required', Rule::in(array_keys(UpsellRule::TRIGGERS))],
            'placement' => ['required', Rule::in(array_keys(UpsellRule::PLACEMENTS))],
            'priority' => ['nullable', 'integer', 'min:0'],
            'max_recommendations' => ['nullable', 'integer', 'min:1', 'max:20'],
            'allow_duplicates' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'recommended_product_ids' => ['required', 'array', 'min:1'],
            'recommended_product_ids.*' => ['integer', 'exists:menu_items,id'],
        ]);
    }

    private function syncGroupAddons(AddonGroup $group, mixed $addonIds, mixed $defaultAddonIds = []): void
    {
        $ids = collect(is_array($addonIds) ? $addonIds : [])->map(fn ($id): int => (int) $id)->unique();
        $defaults = collect(is_array($defaultAddonIds) ? $defaultAddonIds : [])
            ->map(fn ($id): int => (int) $id)
            ->intersect($ids)
            ->flip();
        $group->addons()->sync($ids->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['sort_order' => $index, 'is_default' => $defaults->has($id)],
        ])->all());
    }

    private function syncRuleProducts(UpsellRule $rule, mixed $productIds): void
    {
        $ids = collect(is_array($productIds) ? $productIds : [])->map(fn ($id): int => (int) $id)->unique();
        $rule->products()->sync($ids->mapWithKeys(fn (int $id, int $index): array => [
            $id => ['sort_order' => $index],
        ])->all());
    }

    private function triggerConfig(array $validated, Request $request): array
    {
        return match ($validated['trigger_type']) {
            'product', 'product_combo' => ['product_ids' => collect($request->input('trigger_product_ids', []))->map(fn ($id): int => (int) $id)->unique()->values()->all()],
            'category' => ['category_ids' => collect($request->input('trigger_category_ids', []))->map(fn ($id): int => (int) $id)->unique()->values()->all()],
            'subtotal' => ['min_subtotal' => round((float) $request->input('min_subtotal', 0), 2)],
            'missing_category' => ['category_id' => (int) $request->input('missing_category_id')],
            default => [],
        };
    }

    private function syncLegacyAddonsForMenuItem(MenuItem $menuItem): void
    {
        $menuItem->loadMissing(['activeAddonGroups.activeAddons', 'category.activeAddonGroups.activeAddons', 'restaurant.globalAddonGroups.activeAddons']);

        collect()
            ->concat($menuItem->activeAddonGroups)
            ->concat($menuItem->category?->activeAddonGroups ?? [])
            ->concat($menuItem->restaurant?->globalAddonGroups ?? [])
            ->unique('id')
            ->flatMap(fn (AddonGroup $group) => $group->activeAddons)
            ->unique(fn (Addon $addon): string => mb_strtolower($addon->name).'|'.$addon->price)
            ->each(function (Addon $addon) use ($menuItem): void {
                MenuItemAddon::query()->firstOrCreate(
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
                );
            });
    }
}
