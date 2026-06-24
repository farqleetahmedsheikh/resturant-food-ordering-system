<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['rider', 'delivery'])
            ->where('user_id', $request->user()->id)
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
        if ($order->user_id !== $request->user()->id) {
            throw new BusinessRuleException('You are not allowed to access this order.', 403);
        }

        $order->load(['items', 'restaurant', 'rider', 'delivery', 'statusHistories']);

        return ApiResponse::success(new OrderResource($order));
    }
}
