<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use App\Services\SmartMenuSuggestionService;
use App\Support\Cart;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request, RestaurantAvailabilityService $availability): RedirectResponse
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

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'order_notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod'],
        ]);

        $order = DB::transaction(function () use ($request, $validated, $cart, $restaurant): Order {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'restaurant_id' => $restaurant?->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'delivery_latitude' => $validated['delivery_latitude'] ?? null,
                'delivery_longitude' => $validated['delivery_longitude'] ?? null,
                'order_notes' => $validated['order_notes'] ?? null,
                'subtotal' => $cart['subtotal'],
                'delivery_fee' => $cart['delivery_fee'],
                'total' => $cart['total'],
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($cart['items'] as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'size_name' => $item['size_name'] ?? null,
                    'size_price' => $item['size_price'] ?? null,
                    'addons_snapshot' => $item['addons'] ?? [],
                    'addons_total' => $item['addons_total'] ?? 0,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);
            }

            return $order;
        });

        Cart::clear();

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order): View
    {
        abort_unless($order->user_id === request()->user()->id, 403);

        $order->load('items');

        return view('customer.order-success', compact('order'));
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
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
