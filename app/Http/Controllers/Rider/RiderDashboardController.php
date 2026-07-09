<?php

namespace App\Http\Controllers\Rider;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\Orders\DeliveryStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RiderDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = Order::query()->where('rider_id', $request->user()->id);

        return view('rider.dashboard', [
            'totalAssignedOrders' => (clone $baseQuery)->count(),
            'activeDeliveries' => (clone $baseQuery)->whereNotIn('order_status', ['delivered', 'cancelled'])->count(),
            'deliveredOrders' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
            'failedDeliveries' => Delivery::query()
                ->where('rider_id', $request->user()->id)
                ->where('status', 'failed')
                ->count(),
            'latestOrders' => (clone $baseQuery)
                ->with('delivery')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }

    public function orders(Request $request): View
    {
        $assignedOrders = Order::query()
            ->with('delivery')
            ->where('rider_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('rider.orders', compact('assignedOrders'));
    }

    public function show(Request $request, Order $order): View|RedirectResponse
    {
        if ($order->rider_id !== $request->user()->id) {
            return redirect()->route('rider.orders')->with('status', 'You are not allowed to access this order.');
        }

        $order->load('items', 'delivery', 'user');

        return view('rider.order-show', [
            'order' => $order,
            'deliveryStatuses' => Delivery::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order, DeliveryStatusService $deliveryStatusService): RedirectResponse
    {
        if ($order->rider_id !== $request->user()->id) {
            return redirect()->route('rider.orders')->with('status', 'You are not allowed to access this order.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'picked_up', 'out_for_delivery', 'delivered', 'failed'])],
            'notes' => ['string', 'max:1000', Rule::requiredIf($request->input('status') === 'failed')],
        ], [
            'notes.required' => 'Failed delivery reason is required.',
        ]);

        try {
            $deliveryStatusService->update(
                $order,
                $request->user(),
                $validated['status'],
                $validated['notes'] ?? null,
            );
        } catch (BusinessRuleException $exception) {
            return back()
                ->withInput()
                ->with('status', $exception->getMessage());
        }

        $message = match ($validated['status']) {
            'accepted' => 'Delivery accepted.',
            'picked_up' => 'Delivery marked as picked up.',
            'out_for_delivery' => 'Delivery marked as out for delivery.',
            'delivered' => 'Delivery marked as delivered.',
            'failed' => 'Delivery marked as failed.',
        };

        return back()->with('status', $message);
    }
}
