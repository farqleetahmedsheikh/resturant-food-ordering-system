<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.category-form', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
            'restaurants' => Restaurant::orderBy('name')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'categories');
        }

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: str($validated['name'])->slug()->toString());
        $validated['is_active'] = $request->boolean('is_active');

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        return view('admin.category-form', [
            'category' => $category,
            'restaurants' => Restaurant::orderBy('name')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategory($request, $category);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageUpload::store($request->file('image'), 'categories', $category->image);
        }

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: str($validated['name'])->slug()->toString(), $category);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->menuItems()->exists()) {
            return back()->with('status', 'Category cannot be deleted while it has menu items.');
        }

        ImageUpload::delete($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $slug, ?Category $category = null): string
    {
        if (Category::where('slug', $slug)->where('id', '!=', $category?->id)->exists()) {
            throw ValidationException::withMessages(['slug' => 'The slug has already been taken.']);
        }

        return $slug;
    }
}
