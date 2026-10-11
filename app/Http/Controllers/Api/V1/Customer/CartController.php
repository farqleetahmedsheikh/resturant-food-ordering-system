<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CartItemStoreRequest;
use App\Http\Requests\Api\V1\CartItemUpdateRequest;
use App\Http\Resources\V1\CartResource;
use App\Http\Resources\V1\MenuItemResource;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Services\Cart\DatabaseCartService;
use App\Services\AddonCatalogService;
use App\Services\UpsellRecommendationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private DatabaseCartService $cartService,
        private AddonCatalogService $addonCatalog,
        private UpsellRecommendationService $recommendations,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->cartResponse($request);
    }

    public function store(CartItemStoreRequest $request, MenuItem $menuItem): JsonResponse
    {
        $addonData = ['customization_snapshot' => []];

        if ($request->filled('addon_selections')) {
            $addonData = $this->addonCatalog->legacyAddonDataForSelections($menuItem, $request->input('addon_selections', []));
            $addonIds = $addonData['addon_quantities'];
        } else {
            $addonIds = $request->input('addon_ids', []);
        }

        $this->cartService->add(
            $request->user(),
            $menuItem,
            $request->integer('size_id') ?: null,
            $addonIds,
            (int) $request->integer('quantity'),
            $request->input('item_notes'),
            $addonData['customization_snapshot'],
        );

        return $this->cartResponse($request, 'Item added to cart.');
    }

    public function update(CartItemUpdateRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->cartService->update($request->user(), $cartItem, (int) $request->integer('quantity'), $request->input('item_notes'));

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

    public function recommendations(Request $request): JsonResponse
    {
        $cart = $this->cartService->get($request->user());
        $summary = $this->cartService->summary($cart);

        return ApiResponse::success(
            MenuItemResource::collection($this->recommendations->forSummary(
                $summary['items'],
                $request->string('placement', 'cart')->toString(),
                min(max((int) $request->integer('limit', 6), 1), 12),
            ))->resolve(),
        );
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
