<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Resources\V1\OrderResource;
use App\Services\Orders\CheckoutService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        $order = $this->checkoutService->checkout(
            $request->user(),
            $request->validated(),
            $request->headers->get('Idempotency-Key'),
            $request,
        );

        return ApiResponse::success(new OrderResource($order), 'Order placed successfully.', status: 201);
    }
}
