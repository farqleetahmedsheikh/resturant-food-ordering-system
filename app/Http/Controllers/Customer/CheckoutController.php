<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\Orders\SessionCartCheckoutService;
use App\Services\Payments\StripeConfigurationException;
use App\Services\RestaurantAvailabilityService;
use App\Services\SmartMenuSuggestionService;
use App\Support\Cart;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(RestaurantAvailabilityService $availability): View|RedirectResponse
    {
        $restaurant = Restaurant::current();
        $cart = Cart::summary($restaurant);
        $availabilityStatus = $availability->status($restaurant);

        if ($cart['count'] < 1) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty. Add a meal before checkout.');
        }

        if ($this->cartHasUnavailableItems($cart)) {
            return redirect()->route('cart.index')->with('status', 'One or more cart items are unavailable. Please update your cart.');
        }

        if (! $availabilityStatus['is_open']) {
            return redirect()->route('cart.index')->with('status', $this->restaurantClosedCartMessage());
        }

        if ($restaurant && (float) $restaurant->minimum_order_amount > 0 && $cart['subtotal'] < (float) $restaurant->minimum_order_amount) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Minimum order amount is '.Money::format($restaurant->minimum_order_amount).'.');
        }

        return view('customer.checkout', [
            'cart' => $cart,
            'restaurant' => $restaurant,
            'availabilityStatus' => $availabilityStatus,
            'user' => request()->user(),
            'suggestions' => app(SmartMenuSuggestionService::class)->forCart($cart['items']),
        ]);
    }

    public function store(Request $request, SessionCartCheckoutService $checkoutService): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'order_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $result = $checkoutService->checkout($request->user(), $validated);
        } catch (StripeConfigurationException $exception) {
            return back()
                ->withErrors(['payment' => $exception->getMessage()])
                ->withInput();
        } catch (BusinessRuleException $exception) {
            return redirect()
                ->route('cart.index')
                ->with('status', $exception->getMessage());
        }

        return redirect()->away($result->checkoutUrl);
    }

    public function success(Request $request): View
    {
        $sessionId = (string) $request->query('session_id', '');
        $order = null;

        if ($sessionId !== '') {
            $order = Order::query()
                ->with('items')
                ->where('stripe_checkout_session_id', $sessionId)
                ->first();

            if ($order) {
                abort_unless($order->user_id === $request->user()->id, 403);
                Cart::clear();
            }
        }

        return view('customer.order-success', [
            'order' => $order,
            'sessionId' => $sessionId,
        ]);
    }

    public function cancel(): View
    {
        return view('customer.checkout-cancel');
    }

    private function restaurantClosedCartMessage(): string
    {
        return 'Restaurant is closed now. Your items are in cart and you can checkout later when restaurant opens.';
    }

    /**
     * @param  array{items: array<int|string, array<string, mixed>>}  $cart
     */
    private function cartHasUnavailableItems(array $cart): bool
    {
        $itemIds = collect($cart['items'])->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();

        $menuItems = MenuItem::query()
            ->with(['category', 'activeSizes', 'activeAddons'])
            ->whereIn('id', $itemIds)
            ->get()
            ->keyBy('id');

        if ($menuItems->count() !== $itemIds->count()) {
            return true;
        }

        foreach ($cart['items'] as $item) {
            /** @var MenuItem|null $menuItem */
            $menuItem = $menuItems->get((int) $item['id']);

            if (! $menuItem || ! $menuItem->is_available || ($menuItem->category && ! $menuItem->category->is_active)) {
                return true;
            }

            if ($menuItem->activeSizes->isNotEmpty()) {
                $sizeId = $item['size_id'] ?? null;

                if (! $sizeId || ! $menuItem->activeSizes->contains('id', (int) $sizeId)) {
                    return true;
                }
            }

            $addonIds = collect($item['addons'] ?? [])->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique();

            if ($addonIds->isNotEmpty() && $addonIds->count() !== $menuItem->activeAddons->whereIn('id', $addonIds)->count()) {
                return true;
            }
        }

        return false;
    }
}
