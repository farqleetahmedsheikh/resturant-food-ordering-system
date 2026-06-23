<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->with(['user', 'rider', 'delivery'])
            ->when(
                $status !== '' && array_key_exists($status, Order::STATUSES),
                fn ($query) => $query->where('order_status', $status),
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'currentStatus' => $status,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items', 'user', 'rider', 'delivery');

        return view('admin.order-show', [
            'order' => $order,
            'statuses' => Order::STATUSES,
            'deliveryStatuses' => Delivery::STATUSES,
            'activeRiders' => User::query()
                ->where('role', 'rider')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $payload = ['order_status' => $validated['order_status']];

        if ($validated['order_status'] === 'delivered') {
            $payload['payment_status'] = 'paid';
            $payload['delivered_at'] = $order->delivered_at ?? now();
        }

        if ($validated['order_status'] === 'cancelled') {
            $payload['payment_status'] = 'cancelled';
        }

        $order->update($payload);

        if ($order->delivery) {
            if ($validated['order_status'] === 'out_for_delivery') {
                $order->delivery->update(['status' => 'out_for_delivery']);
            }

            if ($validated['order_status'] === 'delivered') {
                $order->delivery->update([
                    'status' => 'delivered',
                    'delivered_time' => $order->delivery->delivered_time ?? now(),
                ]);
            }

            if ($validated['order_status'] === 'cancelled') {
                $order->delivery->update(['status' => 'failed']);
            }
        }

        return back()->with('status', 'Order status updated successfully.');
    }

    public function assignRider(Request $request, Order $order): RedirectResponse
    {
        if (in_array($order->order_status, ['delivered', 'cancelled'], true)) {
            return back()->with('status', 'Delivered or cancelled orders cannot be assigned.');
        }

        $validated = $request->validate([
            'rider_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'rider')
                    ->where('is_active', true)),
            ],
        ]);

        $order->update([
            'rider_id' => $validated['rider_id'],
            'order_status' => 'assigned_to_rider',
            'assigned_at' => now(),
        ]);

        $order->delivery()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'rider_id' => $validated['rider_id'],
                'status' => 'assigned',
            ],
        );

        return back()->with('status', 'Rider assigned successfully.');
    }

    public function unassignRider(Order $order): RedirectResponse
    {
        if ($order->order_status === 'delivered') {
            return back()->with('status', 'Delivered orders cannot be unassigned.');
        }

        $order->update([
            'rider_id' => null,
            'order_status' => $order->order_status === 'cancelled' ? 'cancelled' : 'preparing',
            'assigned_at' => null,
            'picked_up_at' => null,
        ]);

        $order->delivery?->update([
            'rider_id' => null,
            'status' => 'pending',
            'pickup_time' => null,
            'delivered_time' => null,
        ]);

        return back()->with('status', 'Rider unassigned successfully.');
    }
}
