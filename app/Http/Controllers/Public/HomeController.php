<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $restaurant = Restaurant::where('is_active', true)->first();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['menuItems as available_items_count' => fn ($query) => $query->where('is_available', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(5)
            ->get();

        $featuredItems = MenuItem::query()
            ->with('category')
            ->withCount(['activeSizes', 'activeAddons'])
            ->where('is_available', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(6)
            ->get();

        $topSellerWindow = 'weekly';
        $topSellingItems = $this->topSellingItems(now()->subDays(7));

        if ($topSellingItems->isEmpty()) {
            $topSellerWindow = 'all_time';
            $topSellingItems = $this->topSellingItems();
        }

        if ($topSellingItems->isEmpty()) {
            $topSellerWindow = 'featured';
            $topSellingItems = $featuredItems->take(4)->values();
        }

        return view('pages.home', compact('restaurant', 'categories', 'featuredItems', 'topSellingItems', 'topSellerWindow'));
    }

    private function topSellingItems(mixed $from = null, int $limit = 4)
    {
        $sales = OrderItem::query()
            ->selectRaw('menu_item_id, SUM(quantity) as sold_quantity')
            ->whereNotNull('menu_item_id')
            ->whereHas('order', fn ($query) => $query->where('order_status', '!=', 'cancelled'))
            ->when($from, fn ($query) => $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('created_at', '>=', $from)))
            ->groupBy('menu_item_id')
            ->orderByDesc('sold_quantity')
            ->limit($limit)
            ->get();

        if ($sales->isEmpty()) {
            return collect();
        }

        $items = MenuItem::query()
            ->with('category')
            ->withCount(['activeSizes', 'activeAddons'])
            ->where('is_available', true)
            ->whereIn('id', $sales->pluck('menu_item_id'))
            ->where(function ($query): void {
                $query->whereNull('category_id')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->get()
            ->keyBy('id');

        return $sales
            ->map(function (OrderItem $sale) use ($items): ?MenuItem {
                $item = $items->get((int) $sale->menu_item_id);

                if (! $item) {
                    return null;
                }

                $item->setAttribute('sold_quantity', (int) $sale->sold_quantity);

                return $item;
            })
            ->filter()
            ->values();
    }
}
