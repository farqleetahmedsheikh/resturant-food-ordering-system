<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', array_merge($this->dashboardMetrics(), $this->liveMetrics()));
    }

    public function live(): View
    {
        return view('admin.partials.dashboard-live', $this->liveMetrics());
    }

    public function confirmPendingOrder(Request $request, Order $order, OrderStatusService $orderStatusService): JsonResponse
    {
        $this->ensurePending($order);

        $orderStatusService->change(
            $order,
            'accepted',
            $request->user(),
            'Order confirmed from live dashboard.',
            ['source' => 'admin_live_dashboard'],
        );

        return response()->json([
            'message' => 'Order confirmed successfully.',
        ]);
    }

    public function declinePendingOrder(Request $request, Order $order, OrderStatusService $orderStatusService): JsonResponse
    {
        $this->ensurePending($order);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'Please add a reason before declining the order.',
            'reason.min' => 'Please add a clearer reason before declining the order.',
        ]);

        $orderStatusService->change(
            $order,
            'cancelled',
            $request->user(),
            $validated['reason'],
            ['source' => 'admin_live_dashboard'],
        );

        return response()->json([
            'message' => 'Order declined successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardMetrics(): array
    {
        return [
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
            'restaurant' => Restaurant::current(),
            'totalCodRevenue' => Order::where('payment_method', 'cod')
                ->whereNotIn('order_status', ['cancelled'])
                ->sum('total'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveMetrics(): array
    {
        return [
            'pendingQuickOrders' => Order::query()
                ->with(['user'])
                ->where('order_status', 'pending')
                ->oldest()
                ->take(6)
                ->get(),
            'recentOrders' => Order::with(['user', 'rider'])->latest()->take(8)->get(),
            'liveUpdatedAt' => now(),
            'livePendingOrders' => Order::where('order_status', 'pending')->count(),
            'liveAcceptedOrders' => Order::where('order_status', 'accepted')->count(),
            'livePreparingOrders' => Order::where('order_status', 'preparing')->count(),
            'liveOutForDeliveryOrders' => Order::where('order_status', 'out_for_delivery')->count(),
        ];
    }

    private function ensurePending(Order $order): void
    {
        if ($order->order_status !== 'pending') {
            throw ValidationException::withMessages([
                'order' => 'Only pending orders can be handled from quick actions.',
            ]);
        }
    }
}
