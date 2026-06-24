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
        $status = $request->query('status');

        $orders = Order::query()
            ->with(['user', 'rider', 'delivery'])
            ->when($status && array_key_exists((string) $status, Order::STATUSES), fn ($query) => $query->where('order_status', $status))
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
