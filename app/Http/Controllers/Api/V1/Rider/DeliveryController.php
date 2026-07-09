<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeliveryStatusUpdateRequest;
use App\Http\Requests\Api\V1\RiderLocationUpdateRequest;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\UserResource;
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
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 15), 50))
            ->withQueryString();

        return ApiResponse::success(
            OrderResource::collection($orders)->resolve(),
            meta: ApiResponse::paginationMeta($orders),
        );
    }

    public function history(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['delivery'])
            ->where('rider_id', $request->user()->id)
            ->where(function ($query): void {
                $query
                    ->whereIn('order_status', ['delivered', 'cancelled'])
                    ->orWhereHas('delivery', fn ($query) => $query->whereIn('status', ['delivered', 'failed']));
            })
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

        $order->load(['items', 'user', 'rider', 'delivery', 'statusHistories']);

        return ApiResponse::success(new OrderResource($order));
    }

    public function updateStatus(DeliveryStatusUpdateRequest $request, Order $order): JsonResponse
    {
        return $this->setStatus(
            $request,
            $order,
            $request->string('status')->toString(),
            $request->input('notes'),
        );
    }

    public function accept(Request $request, Order $order): JsonResponse
    {
        return $this->setStatus($request, $order, 'accepted', null, 'Delivery accepted successfully.');
    }

    public function markPickedUp(Request $request, Order $order): JsonResponse
    {
        return $this->setStatus($request, $order, 'picked_up', null, 'Delivery marked as picked up.');
    }

    public function markOutForDelivery(Request $request, Order $order): JsonResponse
    {
        return $this->setStatus($request, $order, 'out_for_delivery', null, 'Delivery marked as out for delivery.');
    }

    public function markDelivered(Request $request, Order $order): JsonResponse
    {
        return $this->setStatus($request, $order, 'delivered', null, 'Delivery marked as delivered.');
    }

    public function updateLocation(RiderLocationUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'last_known_latitude' => $request->float('latitude'),
            'last_known_longitude' => $request->float('longitude'),
            'last_location_updated_at' => now(),
        ]);

        return ApiResponse::success(
            new UserResource($request->user()->fresh()),
            'Rider location updated successfully.',
        );
    }

    private function setStatus(
        Request $request,
        Order $order,
        string $status,
        ?string $notes = null,
        string $message = 'Delivery status updated successfully.',
    ): JsonResponse {
        $order = $this->deliveryStatusService->update($order, $request->user(), $status, $notes);

        return ApiResponse::success(new OrderResource($order), $message);
    }
}
