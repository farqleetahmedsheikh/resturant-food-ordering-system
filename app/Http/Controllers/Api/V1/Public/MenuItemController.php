<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AddonGroupResource;
use App\Http\Resources\V1\MenuItemResource;
use App\Models\Category;
use App\Models\MenuItem;
use App\Services\AddonCatalogService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct(private AddonCatalogService $addonCatalog) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $category = $request->query('category');

        $items = MenuItem::query()
            ->with(['category', 'activeSizes', 'activeAddons', 'activeAddonGroups.activeAddons'])
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when(! $request->boolean('include_unavailable'), fn ($query) => $query->where('is_available', true))
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->when($category, function ($query) use ($category): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery
                    ->where('id', $category)
                    ->orWhere('slug', $category));
            })
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy(
                Category::query()
                    ->select('sort_order')
                    ->whereColumn('categories.id', 'menu_items.category_id')
                    ->limit(1),
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            MenuItemResource::collection($items)->resolve(),
            meta: ApiResponse::paginationMeta($items),
        );
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        abort_unless($menuItem->category?->is_active ?? true, 404);

        $menuItem->load(['category', 'activeSizes', 'activeAddons']);
        $menuItem->setRelation('customizationGroups', $this->addonCatalog->groupsForMenuItem($menuItem));

        return ApiResponse::success(new MenuItemResource($menuItem));
    }

    public function customizations(MenuItem $menuItem): JsonResponse
    {
        abort_unless($menuItem->category?->is_active ?? true, 404);

        return ApiResponse::success(AddonGroupResource::collection($this->addonCatalog->groupsForMenuItem($menuItem))->resolve());
    }
}
