<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\Orders\OrderStatusService;
use App\Services\RestaurantAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(RestaurantAvailabilityService $availability): View
    {
        return view('admin.dashboard', array_merge($this->dashboardMetrics($availability), $this->liveMetrics()));
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
    private function dashboardMetrics(RestaurantAvailabilityService $availability): array
    {
        $restaurant = Restaurant::current();
        $timezone = $availability->timezone($restaurant);
        $today = CarbonImmutable::now($timezone);
        $todayStart = $today->startOfDay()->setTimezone(config('app.timezone', 'UTC'));
        $todayEnd = $today->endOfDay()->setTimezone(config('app.timezone', 'UTC'));
        $todayOrders = Order::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd]);

        return [
            'totalOrders' => Order::count(),
            'todayOrders' => (clone $todayOrders)->count(),
            'todaySalesTotal' => (clone $todayOrders)
                ->where('payment_status', 'paid')
                ->whereNotIn('order_status', ['cancelled'])
                ->sum('total'),
            'pendingOrders' => $this->paidOrderQuery()
                ->where('order_status', 'pending')
                ->count(),
            'acceptedOrders' => Order::where('order_status', 'accepted')->count(),
            'preparingOrders' => Order::where('order_status', 'preparing')->count(),
            'readyOrders' => Order::where('order_status', 'ready')->count(),
            'assignedDeliveries' => Order::where('order_status', 'assigned_to_rider')->count(),
            'outForDeliveryOrders' => Order::where('order_status', 'out_for_delivery')->count(),
            'deliveredOrders' => Order::where('order_status', 'delivered')->count(),
            'cancelledOrders' => Order::where('order_status', 'cancelled')->count(),
            'todayPendingOrders' => (clone $todayOrders)->where('order_status', 'pending')->count(),
            'todayPreparingOrders' => (clone $todayOrders)->where('order_status', 'preparing')->count(),
            'todayOutForDeliveryOrders' => (clone $todayOrders)->where('order_status', 'out_for_delivery')->count(),
            'todayCompletedOrders' => (clone $todayOrders)->where('order_status', 'delivered')->count(),
            'todayCancelledOrders' => (clone $todayOrders)->where('order_status', 'cancelled')->count(),
            'totalCategories' => Category::count(),
            'activeCategories' => Category::where('is_active', true)->count(),
            'totalMenuItems' => MenuItem::count(),
            'availableMenuItems' => MenuItem::where('is_available', true)->count(),
            'disabledMenuItems' => MenuItem::where('is_available', false)->count(),
            'disabledMenuItemsList' => MenuItem::query()
                ->with('category')
                ->where('is_available', false)
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get(),
            'featuredMenuItems' => MenuItem::where('is_featured', true)->count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'totalRiders' => User::where('role', 'rider')->count(),
            'restaurant' => $restaurant,
            'availabilityStatus' => $availability->status($restaurant),
            'manualOrderingPaused' => $restaurant ? ! (bool) $restaurant->is_open : true,
            'totalPaidRevenue' => Order::where('payment_status', 'paid')
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
                ->where(fn ($query) => $query
                    ->where('payment_status', 'paid')
                    ->orWhere('payment_method', 'cod'))
                ->oldest()
                ->take(6)
                ->get(),
            'recentOrders' => Order::with(['user', 'rider'])->latest()->take(8)->get(),
            'liveUpdatedAt' => now(),
            'livePendingOrders' => $this->paidOrderQuery()
                ->where('order_status', 'pending')
                ->count(),
            'liveAcceptedOrders' => Order::where('order_status', 'accepted')->count(),
            'livePreparingOrders' => Order::where('order_status', 'preparing')->count(),
            'liveReadyOrders' => Order::where('order_status', 'ready')->count(),
            'liveOutForDeliveryOrders' => Order::where('order_status', 'out_for_delivery')->count(),
        ];
    }

    private function paidOrderQuery()
    {
        return Order::query()
            ->where(fn ($query) => $query
                ->where('payment_status', 'paid')
                ->orWhere('payment_method', 'cod'));
    }

    private function ensurePending(Order $order): void
    {
        if ($order->order_status !== 'pending' || ! $order->canEnterFulfillment()) {
            throw ValidationException::withMessages([
                'order' => 'Only paid pending orders can be handled from quick actions.',
            ]);
        }
    }
}
