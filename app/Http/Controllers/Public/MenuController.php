<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\SmartMenuSuggestionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = Restaurant::where('is_active', true)->first();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['menuItems as available_items_count' => fn ($query) => $query->where('is_available', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedCategory = null;

        $featuredItems = MenuItem::query()
            ->with('category')
            ->withCount(['activeSizes', 'activeAddons'])
            ->where('is_available', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(4)
            ->get();

        $menuItems = MenuItem::query()
            ->with('category')
            ->withCount(['activeSizes', 'activeAddons'])
            ->where('is_available', true)
            ->where(function ($query): void {
                $query->whereNull('category_id')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->when($request->filled('category'), function ($query) use ($request, &$selectedCategory): void {
                $selectedCategory = Category::where('is_active', true)->where('slug', (string) $request->string('category'))->first();

                if ($selectedCategory) {
                    $query->where('category_id', $selectedCategory->id);
                }
            })
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.menu', compact('restaurant', 'categories', 'featuredItems', 'menuItems', 'selectedCategory'));
    }

    public function show(MenuItem $menuItem): View
    {
        abort_unless($menuItem->is_available && (! $menuItem->category || $menuItem->category->is_active), 404);

        $menuItem->load(['category', 'activeSizes', 'activeAddons']);

        $relatedItems = MenuItem::query()
            ->with('category')
            ->where('is_available', true)
            ->where(function ($query): void {
                $query->whereNull('category_id')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->where('id', '!=', $menuItem->id)
            ->when($menuItem->category_id, fn ($query) => $query->where('category_id', $menuItem->category_id))
            ->take(3)
            ->get();

        $restaurant = Restaurant::where('is_active', true)->first();
        $suggestions = app(SmartMenuSuggestionService::class)->forItem($menuItem);

        return view('pages.menu-item', compact('restaurant', 'menuItem', 'relatedItems', 'suggestions'));
    }
}
