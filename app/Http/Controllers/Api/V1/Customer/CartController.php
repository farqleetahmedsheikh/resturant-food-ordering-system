<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CartItemStoreRequest;
use App\Http\Requests\Api\V1\CartItemUpdateRequest;
use App\Http\Resources\V1\CartResource;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Services\Cart\DatabaseCartService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private DatabaseCartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        return $this->cartResponse($request);
    }

    public function store(CartItemStoreRequest $request, MenuItem $menuItem): JsonResponse
    {
        $this->cartService->add(
            $request->user(),
            $menuItem,
            $request->integer('size_id') ?: null,
            $request->input('addon_ids', []),
            (int) $request->integer('quantity'),
        );

        return $this->cartResponse($request, 'Item added to cart.');
    }

    public function update(CartItemUpdateRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->update($request->user(), $cartItem, (int) $request->integer('quantity'));

        return $this->cartResponse($request, 'Cart updated successfully.');
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->remove($request->user(), $cartItem);

        return $this->cartResponse($request, 'Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());

        return $this->cartResponse($request, 'Cart cleared successfully.');
    }

    private function cartResponse(Request $request, string $message = 'OK'): JsonResponse
    {
        $cart = $this->cartService->get($request->user());

        return ApiResponse::success(new CartResource([
            'cart' => $cart,
            'summary' => $this->cartService->summary($cart),
        ]), $message);
    }
}
