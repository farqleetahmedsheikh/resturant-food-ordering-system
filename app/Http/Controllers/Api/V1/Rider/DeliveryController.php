<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryStatusUpdateRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\Orders\DeliveryStatusService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryStatusService $deliveryStatusService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $baseQuery = Order::query()->where('rider_id', $request->user()->id);

        $latestOrders = (clone $baseQuery)
            ->with(['delivery'])
            ->latest()
            ->take(6)
            ->get();

        return ApiResponse::success([
            'total_assigned_orders' => (clone $baseQuery)->count(),
            'active_deliveries' => (clone $baseQuery)->whereNotIn('order_status', ['delivered', 'cancelled'])->count(),
            'delivered_orders' => (clone $baseQuery)->where('order_status', 'delivered')->count(),
            'failed_deliveries' => Delivery::query()
                ->where('rider_id', $request->user()->id)
                ->where('status', 'failed')
                ->count(),
            'latest_orders' => OrderResource::collection($latestOrders),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['delivery'])
            ->where('rider_id', $request->user()->id)
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 15), 50))
            ->withQueryString();

        return ApiResponse::success(
            OrderResource::collection($orders)->resolve(),
            meta: ApiResponse::paginationMeta($orders),
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->rider_id !== $request->user()->id) {
            throw new BusinessRuleException('You are not allowed to access this order.', 403);
        }

        $order->load(['items', 'user', 'delivery', 'statusHistories']);

        return ApiResponse::success(new OrderResource($order));
    }

    public function updateStatus(DeliveryStatusUpdateRequest $request, Order $order): JsonResponse
    {
        $order = $this->deliveryStatusService->update(
            $order,
            $request->user(),
            $request->string('status')->toString(),
            $request->input('notes'),
        );

        return ApiResponse::success(new OrderResource($order), 'Delivery status updated successfully.');
    }
}
