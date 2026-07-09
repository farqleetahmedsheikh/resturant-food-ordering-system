<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminOrderStatusUpdateRequest;
use App\Http\Requests\Api\V1\AssignRiderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderStatusService;
use App\Services\Orders\RiderAssignmentService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderStatusService $orderStatusService,
        private RiderAssignmentService $riderAssignmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', '');
        $paymentStatus = (string) $request->query('payment_status', '');
        $search = trim((string) $request->query('search', ''));
        $riderId = $request->integer('rider_id');
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $orders = Order::query()
            ->with(['user', 'rider', 'delivery'])
            ->when($status !== '' && array_key_exists($status, Order::STATUSES), fn ($query) => $query->where('order_status', $status))
            ->when($paymentStatus !== '' && array_key_exists($paymentStatus, Order::PAYMENT_STATUSES), fn ($query) => $query->where('payment_status', $paymentStatus))
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
            ->paginate(min((int) $request->integer('per_page', 20), 75))
            ->withQueryString();

        return ApiResponse::success(
            OrderResource::collection($orders)->resolve(),
            meta: ApiResponse::paginationMeta($orders),
        );
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items', 'restaurant', 'user', 'rider', 'delivery', 'statusHistories']);

        return ApiResponse::success(new OrderResource($order));
    }

    public function updateStatus(AdminOrderStatusUpdateRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderStatusService->change(
            $order,
            $request->string('order_status')->toString(),
            $request->user(),
            $request->input('reason'),
        );

        return ApiResponse::success(new OrderResource($order), 'Order status updated successfully.');
    }

    public function assignRider(AssignRiderRequest $request, Order $order): JsonResponse
    {
        $rider = User::query()->findOrFail($request->integer('rider_id'));
        $order = $this->riderAssignmentService->assign($order, $rider, $request->user());

        return ApiResponse::success(new OrderResource($order), 'Rider assigned successfully.');
    }

    public function unassignRider(Request $request, Order $order): JsonResponse
    {
        $order = $this->riderAssignmentService->unassign($order, $request->user());

        return ApiResponse::success(new OrderResource($order), 'Rider unassigned successfully.');
    }
}
