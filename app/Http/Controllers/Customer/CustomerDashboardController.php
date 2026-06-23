<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders();

        return view('customer.dashboard', [
            'totalOrders' => (clone $orders)->count(),
            'latestOrder' => (clone $orders)->latest()->first(),
        ]);
    }
}
