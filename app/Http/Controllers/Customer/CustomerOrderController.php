<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === request()->user()->id, 403);

        $order->load('items.menuItem', 'rider', 'delivery');

        return view('customer.order-show', compact('order'));
    }
}
