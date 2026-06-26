<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminMenuItemRequest;
use App\Http\Resources\V1\MenuItemResource;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\Security\AuditLogger;
use App\Support\Api\ApiResponse;
use App\Support\ImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $items = MenuItem::query()
            ->with(['category', 'activeSizes', 'activeAddons'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('availability'), function ($query) use ($request): void {
                if ($request->query('availability') === 'available') {
                    $query->where('is_available', true);
                }

                if ($request->query('availability') === 'unavailable') {
                    $query->where('is_available', false);
                }
            })
            ->orderBy(
                Category::query()
                    ->select('sort_order')
                    ->whereColumn('categories.id', 'menu_items.category_id')
                    ->limit(1),
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 20), 75))
            ->withQueryString();

        return ApiResponse::success(
            MenuItemResource::collection($items)->resolve(),
            meta: ApiResponse::paginationMeta($items),
        );
    }

    public function store(AdminMenuItemRequest $request): JsonResponse
    {
        $payload = $this->payload($request);

        if ($request->hasFile('image')) {
            $payload['image'] = ImageUpload::store($request->file('image'), 'menu-items');
        }

        $menuItem = MenuItem::create($payload)->load(['category', 'activeSizes', 'activeAddons']);
        $this->auditLogger->record('menu_item.created', $request->user(), $menuItem, [], $menuItem->toArray());

        return ApiResponse::success(new MenuItemResource($menuItem), 'Menu item created successfully.', status: 201);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        return ApiResponse::success(new MenuItemResource($menuItem->load(['category', 'activeSizes', 'activeAddons'])));
    }

    public function update(AdminMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $old = $menuItem->toArray();
        $payload = $this->payload($request, $menuItem);

        if ($request->hasFile('image')) {
            $payload['image'] = ImageUpload::store($request->file('image'), 'menu-items', $menuItem->image);
        }

        $menuItem->update($payload);
        $menuItem = $menuItem->fresh(['category', 'activeSizes', 'activeAddons']);

        $this->auditLogger->record('menu_item.updated', $request->user(), $menuItem, $old, $menuItem->toArray());

        return ApiResponse::success(new MenuItemResource($menuItem), 'Menu item updated successfully.');
    }

    public function destroy(Request $request, MenuItem $menuItem): JsonResponse
    {
        if ($menuItem->orderItems()->exists()) {
            throw new BusinessRuleException('This menu item appears in past orders and cannot be deleted. Mark it unavailable instead.');
        }

        $old = $menuItem->toArray();
        ImageUpload::delete($menuItem->image);
        $menuItem->delete();

        $this->auditLogger->record('menu_item.deleted', $request->user(), $menuItem, $old, []);

        return ApiResponse::success(null, 'Menu item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AdminMenuItemRequest $request, ?MenuItem $menuItem = null): array
    {
        $validated = $request->validated();
        $slug = $validated['slug'] ?? null;

        return [
            'restaurant_id' => $validated['restaurant_id'] ?? Restaurant::current()?->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug ?: $this->uniqueSlug(Str::slug($validated['name']), $menuItem?->id),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'preparation_time' => $validated['preparation_time'] ?? null,
            'calories' => $validated['calories'] ?? null,
            'is_featured' => $request->boolean('is_featured', $menuItem?->is_featured ?? false),
            'is_available' => $request->boolean('is_available', $menuItem?->is_available ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: Str::random(8);
        $candidate = $slug;
        $count = 2;

        while (MenuItem::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $slug.'-'.$count++;
        }

        return $candidate;
    }
}
