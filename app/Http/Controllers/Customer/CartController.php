<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\SmartMenuSuggestionService;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $restaurant = Restaurant::where('is_active', true)->first();

        return view('customer.cart', [
            'cart' => Cart::summary($restaurant),
            'restaurant' => $restaurant,
            'suggestions' => app(SmartMenuSuggestionService::class)->forCart(Cart::items()),
        ]);
    }

    public function add(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->load(['category', 'activeSizes', 'activeAddons']);

        if (! $menuItem->is_available || ($menuItem->category && ! $menuItem->category->is_active)) {
            return back()->with('status', 'This item is currently unavailable.');
        }

        $validated = $request->validate([
            'size_id' => ['nullable', 'integer'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer'],
        ]);

        $size = null;

        if ($menuItem->activeSizes->isNotEmpty()) {
            if (empty($validated['size_id'])) {
                return redirect()
                    ->route('menu.show', $menuItem)
                    ->with('status', 'Please choose a size before adding this item to your cart.');
            }

            $size = $menuItem->activeSizes->firstWhere('id', (int) $validated['size_id']);

            if (! $size) {
                throw ValidationException::withMessages(['size_id' => 'Please choose an available size.']);
            }
        }

        $addonIds = collect($validated['addon_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $addons = $menuItem->activeAddons->whereIn('id', $addonIds);

        if ($addonIds->count() !== $addons->count()) {
            throw ValidationException::withMessages(['addon_ids' => 'One or more selected add-ons are unavailable.']);
        }

        Cart::add($menuItem, 1, $size, $addons);

        return back()->with('status', "{$menuItem->name} added to your cart.");
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item' => ['required', 'string', 'max:64'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        Cart::update($validated['item'], $validated['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(string $item): RedirectResponse
    {
        Cart::remove($item);

        return back()->with('status', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        Cart::clear();

        return back()->with('status', 'Cart cleared.');
    }
}
