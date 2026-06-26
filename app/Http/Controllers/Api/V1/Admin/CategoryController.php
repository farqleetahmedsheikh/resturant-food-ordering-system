<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminCategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Models\Restaurant;
use App\Services\Security\AuditLogger;
use App\Support\Api\ApiResponse;
use App\Support\ImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->withCount('menuItems')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('status'), function ($query) use ($request): void {
                if ($request->query('status') === 'active') {
                    $query->where('is_active', true);
                }

                if ($request->query('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 20), 75))
            ->withQueryString();

        return ApiResponse::success(
            CategoryResource::collection($categories)->resolve(),
            meta: ApiResponse::paginationMeta($categories),
        );
    }

    public function store(AdminCategoryRequest $request): JsonResponse
    {
        $payload = $this->payload($request);

        if ($request->hasFile('image')) {
            $payload['image'] = ImageUpload::store($request->file('image'), 'categories');
        }

        $category = Category::create($payload);
        $this->auditLogger->record('category.created', $request->user(), $category, [], $category->toArray());

        return ApiResponse::success(new CategoryResource($category->loadCount('menuItems')), 'Category created successfully.', status: 201);
    }

    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success(new CategoryResource($category->loadCount('menuItems')));
    }

    public function update(AdminCategoryRequest $request, Category $category): JsonResponse
    {
        $old = $category->toArray();
        $payload = $this->payload($request, $category);

        if ($request->hasFile('image')) {
            $payload['image'] = ImageUpload::store($request->file('image'), 'categories', $category->image);
        }

        $category->update($payload);
        $this->auditLogger->record('category.updated', $request->user(), $category, $old, $category->fresh()->toArray());

        return ApiResponse::success(new CategoryResource($category->fresh()->loadCount('menuItems')), 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        if ($category->menuItems()->exists()) {
            throw new BusinessRuleException('This category has menu items and cannot be deleted.');
        }

        $old = $category->toArray();
        ImageUpload::delete($category->image);
        $category->delete();

        $this->auditLogger->record('category.deleted', $request->user(), $category, $old, []);

        return ApiResponse::success(null, 'Category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AdminCategoryRequest $request, ?Category $category = null): array
    {
        $validated = $request->validated();
        $slug = $validated['slug'] ?? null;

        return [
            'restaurant_id' => $validated['restaurant_id'] ?? Restaurant::current()?->id,
            'name' => $validated['name'],
            'slug' => $slug ?: $this->uniqueSlug(Category::class, Str::slug($validated['name']), $category?->id),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', $category?->is_active ?? true),
        ];
    }

    private function uniqueSlug(string $modelClass, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: Str::random(8);
        $candidate = $slug;
        $count = 2;

        while ($modelClass::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $slug.'-'.$count++;
        }

        return $candidate;
    }
}
