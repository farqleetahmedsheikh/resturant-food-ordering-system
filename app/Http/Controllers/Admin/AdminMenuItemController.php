<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\AddonGroup;
use App\Models\MenuItemAddon;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminMenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $menuItems = MenuItem::query()
            ->with('category')
            ->withCount(['activeSizes', 'activeAddons'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('availability'), function ($query) use ($request): void {
                if ($request->string('availability')->toString() === 'available') {
                    $query->where('is_available', true);
                }

                if ($request->string('availability')->toString() === 'unavailable') {
                    $query->where('is_available', false);
                }
            })
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.menu-items', [
            'menuItems' => $menuItems,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menu-item-form', [
            'menuItem' => new MenuItem(['is_available' => true, 'is_featured' => false, 'sort_order' => 0]),
            'addonGroups' => $this->availableAddonGroups(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMenuItem($request);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'menu-items');
        }

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: str($validated['name'])->slug()->toString());
        $validated['restaurant_id'] = Restaurant::current()?->id;
        $validated['is_available'] = $request->boolean('is_available');
        $validated['is_featured'] = $request->boolean('is_featured');

        DB::transaction(function () use ($validated): void {
            $menuItem = MenuItem::create(Arr::except($validated, ['sizes', 'addons', 'addon_group_ids', 'remove_image']));

            $this->syncOptions($menuItem, $validated);
            $this->syncAddonGroups($menuItem, $validated['addon_group_ids'] ?? []);
            $this->syncGroupedOptionsToLegacyAddons($menuItem);
        });

        return redirect()->route('admin.menu-items.index')->with('status', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $menuItem->load(['sizes', 'addons', 'addonGroups']);

        return view('admin.menu-item-form', [
            'menuItem' => $menuItem,
            'addonGroups' => $this->availableAddonGroups(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $this->validateMenuItem($request, $menuItem);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'menu-items', $menuItem->image);
        } elseif ($request->boolean('remove_image')) {
            ImageUpload::delete($menuItem->image);
            $validated['image'] = null;
        }

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: str($validated['name'])->slug()->toString(), $menuItem);
        $validated['is_available'] = $request->boolean('is_available');
        $validated['is_featured'] = $request->boolean('is_featured');

        DB::transaction(function () use ($menuItem, $validated): void {
            $menuItem->update(Arr::except($validated, ['sizes', 'addons', 'addon_group_ids', 'remove_image']));

            $this->syncOptions($menuItem, $validated);
            $this->syncAddonGroups($menuItem, $validated['addon_group_ids'] ?? []);
            $this->syncGroupedOptionsToLegacyAddons($menuItem);
        });

        return redirect()->route('admin.menu-items.index')->with('status', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->orderItems()->exists()) {
            return back()->with('status', 'Menu item cannot be deleted because it exists in old orders. Mark it unavailable instead.');
        }

        ImageUpload::delete($menuItem->image);
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('status', 'Menu item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMenuItem(Request $request, ?MenuItem $menuItem = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('menu_items', 'slug')->ignore($menuItem?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ImageUpload::validationRules(),
            'remove_image' => ['nullable', 'boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer'],
            'sizes' => ['nullable', 'array'],
            'sizes.*.name' => ['nullable', 'string', 'max:100'],
            'sizes.*.price' => ['nullable', 'numeric', 'min:0'],
            'sizes.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'sizes.*.is_active' => ['nullable', 'boolean'],
            'addon_group_ids' => ['nullable', 'array'],
            'addon_group_ids.*' => ['integer', 'exists:addon_groups,id'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['nullable', 'string', 'max:100'],
            'addons.*.type' => ['nullable', Rule::in(array_keys(MenuItemAddon::TYPES))],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'addons.*.is_active' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncOptions(MenuItem $menuItem, array $validated): void
    {
        $menuItem->sizes()->delete();
        $menuItem->addons()->delete();

        $sizes = $this->cleanOptionRows($validated['sizes'] ?? [], 'sizes')
            ->map(fn (array $row, int $index): array => [
                'name' => $row['name'],
                'price' => $row['price'],
                'sort_order' => $row['sort_order'] ?? $index,
                'is_active' => (bool) ($row['is_active'] ?? false),
            ]);

        if ($sizes->isNotEmpty()) {
            $menuItem->sizes()->createMany($sizes->all());
        }

        $addons = $this->cleanOptionRows($validated['addons'] ?? [], 'addons')
            ->map(fn (array $row, int $index): array => [
                'name' => $row['name'],
                'type' => $row['type'] ?? 'topping',
                'price' => $row['price'],
                'sort_order' => $row['sort_order'] ?? $index,
                'is_active' => (bool) ($row['is_active'] ?? false),
            ]);

        if ($addons->isNotEmpty()) {
            $menuItem->addons()->createMany($addons->all());
        }
    }

    /**
     * @param  array<int, int|string>  $groupIds
     */
    private function syncAddonGroups(MenuItem $menuItem, array $groupIds): void
    {
        $restaurantId = Restaurant::current()?->id;
        $allowedIds = AddonGroup::query()
            ->whereIn('id', collect($groupIds)->map(fn ($id): int => (int) $id)->unique())
            ->where('is_active', true)
            ->where(function ($query) use ($restaurantId): void {
                $query->whereNull('restaurant_id');

                if ($restaurantId) {
                    $query->orWhere('restaurant_id', $restaurantId);
                }
            })
            ->pluck('id');

        $menuItem->addonGroups()->sync(
            $allowedIds->values()->mapWithKeys(fn (int $id, int $index): array => [
                $id => ['sort_order' => $index, 'is_active' => true],
            ])->all(),
        );
    }

    private function syncGroupedOptionsToLegacyAddons(MenuItem $menuItem): void
    {
        $menuItem->loadMissing([
            'activeAddonGroups.activeAddons',
            'category.activeAddonGroups.activeAddons',
            'restaurant.globalAddonGroups.activeAddons',
        ]);

        collect()
            ->concat($menuItem->activeAddonGroups)
            ->concat($menuItem->category?->activeAddonGroups ?? [])
            ->concat($menuItem->restaurant?->globalAddonGroups ?? [])
            ->unique('id')
            ->flatMap(fn (AddonGroup $group) => $group->activeAddons)
            ->unique(fn ($addon): string => mb_strtolower($addon->name).'|'.$addon->price)
            ->each(function ($addon) use ($menuItem): void {
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

    /**
     * @return \Illuminate\Support\Collection<int, AddonGroup>
     */
    private function availableAddonGroups(): \Illuminate\Support\Collection
    {
        $restaurantId = Restaurant::current()?->id;

        return AddonGroup::query()
            ->with('activeAddons')
            ->where('is_active', true)
            ->where(function ($query) use ($restaurantId): void {
                $query->whereNull('restaurant_id');

                if ($restaurantId) {
                    $query->orWhere('restaurant_id', $restaurantId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (AddonGroup $group): bool => $group->activeAddons->isNotEmpty())
            ->values();
    }

    /**
     * @param  mixed  $rows
     */
    private function cleanOptionRows(mixed $rows, string $field): \Illuminate\Support\Collection
    {
        return collect(is_array($rows) ? $rows : [])
            ->map(function (array $row, int $index) use ($field): ?array {
                $name = trim((string) ($row['name'] ?? ''));
                $price = $row['price'] ?? null;
                $hasAnyValue = $name !== '' || $price !== null && $price !== '';

                if (! $hasAnyValue) {
                    return null;
                }

                if ($name === '' || $price === null || $price === '') {
                    throw ValidationException::withMessages([
                        "{$field}.{$index}.name" => 'Each option row needs both a name and price.',
                    ]);
                }

                return [
                    'name' => $name,
                    'type' => $row['type'] ?? 'topping',
                    'price' => round((float) $price, 2),
                    'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ];
            })
            ->filter()
            ->values();
    }

    private function uniqueSlug(string $slug, ?MenuItem $menuItem = null): string
    {
        if (MenuItem::where('slug', $slug)->where('id', '!=', $menuItem?->id)->exists()) {
            throw ValidationException::withMessages(['slug' => 'The slug has already been taken.']);
        }

        return $slug;
    }
}
