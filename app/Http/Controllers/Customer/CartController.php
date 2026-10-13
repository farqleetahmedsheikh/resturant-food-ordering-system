<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use App\Services\SmartMenuSuggestionService;
use App\Services\AddonCatalogService;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(RestaurantAvailabilityService $availability): View
    {
        $restaurant = Restaurant::current();

        return view('customer.cart', [
            'cart' => Cart::summary($restaurant),
            'restaurant' => $restaurant,
            'availabilityStatus' => $availability->status($restaurant),
            'suggestions' => app(SmartMenuSuggestionService::class)->forCart(Cart::items()),
        ]);
    }

    public function add(Request $request, MenuItem $menuItem): RedirectResponse|JsonResponse
    {
        $menuItem->load(['category', 'activeSizes', 'activeAddons']);

        if (! $menuItem->is_available || ($menuItem->category && ! $menuItem->category->is_active)) {
            return back()->with('status', 'This item is currently unavailable.');
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'size_id' => ['nullable', 'integer'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer'],
            'addon_selections' => ['nullable', 'array'],
            'addon_selections.*' => ['array'],
        ]);

        $size = null;

        if ($menuItem->activeSizes->isNotEmpty()) {
            if (empty($validated['size_id'])) {
                return redirect($menuItem->public_url)
                    ->with('status', 'Please choose a size before adding this item to your cart.');
            }

            $size = $menuItem->activeSizes->firstWhere('id', (int) $validated['size_id']);

            if (! $size) {
                throw ValidationException::withMessages(['size_id' => 'Please choose an available size.']);
            }
        }

        if (! empty($validated['addon_selections'])) {
            $addonData = app(AddonCatalogService::class)
                ->legacyAddonDataForSelections($menuItem, $validated['addon_selections']);
            $addonQuantities = $addonData['addon_quantities'];
            $menuItem->unsetRelation('activeAddons')->load('activeAddons');
        } else {
            $addonData = ['customization_snapshot' => []];
            $addonQuantities = collect($validated['addon_ids'] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->countBy()
                ->map(fn ($quantity): int => (int) $quantity)
                ->all();
        }

        $addonIds = collect(array_keys($addonQuantities));
        $addons = $menuItem->activeAddons->whereIn('id', $addonIds);

        if ($addonIds->count() !== $addons->count()) {
            throw ValidationException::withMessages(['addon_ids' => 'One or more selected add-ons are unavailable.']);
        }

        Cart::add($menuItem, (int) ($validated['quantity'] ?? 1), $size, $addonQuantities ? $addonQuantities : $addons, $addonData['customization_snapshot']);

        $message = "{$menuItem->name} added to your cart.";

        return $request->expectsJson()
            ? response()->json(['cart' => Cart::summary(Restaurant::current()), 'message' => $message])
            : back()->with('status', $message);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'item' => ['required', 'string', 'max:64'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        Cart::update($validated['item'], $validated['quantity']);

        return $request->expectsJson()
            ? response()->json(['cart' => Cart::summary(Restaurant::current()), 'message' => 'Cart updated.'])
            : back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request, string $item): RedirectResponse|JsonResponse
    {
        Cart::remove($item);

        return $request->expectsJson()
            ? response()->json(['cart' => Cart::summary(Restaurant::current()), 'message' => 'Item removed from cart.'])
            : back()->with('status', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        Cart::clear();

        return back()->with('status', 'Cart cleared.');
    }
}
