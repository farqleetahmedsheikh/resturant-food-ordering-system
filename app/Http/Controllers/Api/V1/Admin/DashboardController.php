<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $latestOrders = Order::query()
            ->with(['rider', 'delivery'])
            ->latest()
            ->take(8)
            ->get();

        return ApiResponse::success([
            'cards' => [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('order_status', 'pending')->count(),
                'preparing_orders' => Order::where('order_status', 'preparing')->count(),
                'assigned_deliveries' => Order::where('order_status', 'assigned_to_rider')->count(),
                'out_for_delivery' => Order::where('order_status', 'out_for_delivery')->count(),
                'delivered_orders' => Order::where('order_status', 'delivered')->count(),
                'total_riders' => User::where('role', 'rider')->count(),
                'total_cod_revenue' => (float) Order::where('payment_method', 'cod')
                    ->where('payment_status', 'paid')
                    ->sum('total'),
                'total_categories' => Category::count(),
                'active_categories' => Category::where('is_active', true)->count(),
                'total_menu_items' => MenuItem::count(),
                'available_menu_items' => MenuItem::where('is_available', true)->count(),
                'featured_items' => MenuItem::where('is_featured', true)->count(),
                'restaurant_is_open' => (bool) Restaurant::query()->where('is_active', true)->value('is_open'),
            ],
            'latest_orders' => OrderResource::collection($latestOrders),
        ]);
    }
}
