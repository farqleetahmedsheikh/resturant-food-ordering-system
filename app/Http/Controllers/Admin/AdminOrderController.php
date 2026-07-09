<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminOrderStatusUpdateRequest;
use App\Http\Requests\Api\V1\AssignRiderRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderStatusService;
use App\Services\Orders\RiderAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $paymentStatus = $request->string('payment_status')->toString();
        $riderId = $request->integer('rider_id');
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');
        $search = trim($request->string('search')->toString());

        $orders = Order::query()
            ->with(['user', 'rider', 'delivery'])
            ->when(
                $status !== '' && array_key_exists($status, Order::STATUSES),
                fn ($query) => $query->where('order_status', $status),
            )
            ->when(
                $paymentStatus !== '' && array_key_exists($paymentStatus, Order::PAYMENT_STATUSES),
                fn ($query) => $query->where('payment_status', $paymentStatus),
            )
            ->when($riderId > 0, fn ($query) => $query->where('rider_id', $riderId))
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%')
                        ->orWhere('customer_email', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($query) => $query
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'paymentStatuses' => Order::PAYMENT_STATUSES,
            'currentStatus' => $status,
            'filters' => [
                'payment_status' => $paymentStatus,
                'rider_id' => $riderId ?: null,
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $search,
            ],
            'activeRiders' => User::query()
                ->where('role', 'rider')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
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

    public function updateStatus(AdminOrderStatusUpdateRequest $request, Order $order, OrderStatusService $orderStatusService): RedirectResponse
    {
        try {
            $orderStatusService->change(
                $order,
                $request->string('order_status')->toString(),
                $request->user(),
                $request->input('reason'),
                ['source' => 'admin_order_detail'],
            );
        } catch (BusinessRuleException $exception) {
            return back()
                ->withInput()
                ->with('status', $exception->getMessage());
        }

        return back()->with('status', 'Order status updated successfully.');
    }

    public function assignRider(AssignRiderRequest $request, Order $order, RiderAssignmentService $riderAssignmentService): RedirectResponse
    {
        $rider = User::query()->findOrFail($request->integer('rider_id'));

        try {
            $riderAssignmentService->assign($order, $rider, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()
                ->withInput()
                ->with('status', $exception->getMessage());
        }

        return back()->with('status', 'Rider assigned successfully.');
    }

    public function unassignRider(Request $request, Order $order, RiderAssignmentService $riderAssignmentService): RedirectResponse
    {
        try {
            $riderAssignmentService->unassign($order, $request->user());
        } catch (BusinessRuleException $exception) {
            return back()->with('status', $exception->getMessage());
        }

        return back()->with('status', 'Rider unassigned successfully.');
    }
}
