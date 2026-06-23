<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('order_status', 'pending')->count(),
            'preparingOrders' => Order::where('order_status', 'preparing')->count(),
            'assignedDeliveries' => Order::where('order_status', 'assigned_to_rider')->count(),
            'outForDeliveryOrders' => Order::where('order_status', 'out_for_delivery')->count(),
            'deliveredOrders' => Order::where('order_status', 'delivered')->count(),
            'totalCategories' => Category::count(),
            'activeCategories' => Category::where('is_active', true)->count(),
            'totalMenuItems' => MenuItem::count(),
            'availableMenuItems' => MenuItem::where('is_available', true)->count(),
            'featuredMenuItems' => MenuItem::where('is_featured', true)->count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalRiders' => User::where('role', 'rider')->count(),
            'restaurant' => Restaurant::where('is_active', true)->first(),
            'totalCodRevenue' => Order::where('payment_method', 'cod')
                ->whereNotIn('order_status', ['cancelled'])
                ->sum('total'),
            'recentOrders' => Order::with(['user', 'rider'])->latest()->take(8)->get(),
        ]);
    }
}
