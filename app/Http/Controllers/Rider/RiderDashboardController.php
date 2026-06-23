<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
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

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        if ($order->rider_id !== $request->user()->id) {
            return redirect()->route('rider.orders')->with('status', 'You are not allowed to access this order.');
        }

        if (in_array($order->order_status, ['delivered', 'cancelled'], true)) {
            return back()->with('status', 'Delivered or cancelled orders cannot be updated again.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['picked_up', 'out_for_delivery', 'delivered', 'failed'])],
            'notes' => ['string', 'max:1000', Rule::requiredIf($request->input('status') === 'failed')],
        ], [
            'notes.required' => 'Failed delivery reason is required.',
        ]);

        $delivery = $order->delivery()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'rider_id' => $request->user()->id,
                'status' => 'assigned',
            ],
        );

        if ($validated['status'] === 'picked_up') {
            $delivery->update([
                'rider_id' => $request->user()->id,
                'status' => 'picked_up',
                'pickup_time' => $delivery->pickup_time ?? now(),
            ]);

            $order->update([
                'order_status' => 'out_for_delivery',
                'picked_up_at' => $order->picked_up_at ?? now(),
            ]);

            return back()->with('status', 'Delivery marked as picked up.');
        }

        if ($validated['status'] === 'out_for_delivery') {
            $delivery->update([
                'rider_id' => $request->user()->id,
                'status' => 'out_for_delivery',
            ]);

            $order->update(['order_status' => 'out_for_delivery']);

            return back()->with('status', 'Delivery marked as out for delivery.');
        }

        if ($validated['status'] === 'delivered') {
            $delivery->update([
                'rider_id' => $request->user()->id,
                'status' => 'delivered',
                'delivered_time' => $delivery->delivered_time ?? now(),
            ]);

            $order->update([
                'order_status' => 'delivered',
                'delivered_at' => $order->delivered_at ?? now(),
                'payment_status' => $order->payment_method === 'cod' ? 'paid' : $order->payment_status,
            ]);

            return back()->with('status', 'Delivery marked as delivered.');
        }

        $delivery->update([
            'rider_id' => $request->user()->id,
            'status' => 'failed',
            'notes' => $validated['notes'],
        ]);

        $order->update(['order_status' => 'cancelled']);

        return back()->with('status', 'Delivery marked as failed.');
    }
}
