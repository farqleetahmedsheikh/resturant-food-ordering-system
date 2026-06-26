<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_backend_blocks_checkout_when_restaurant_is_manually_closed(): void
    {
        $customer = $this->user();
        $menuItem = $this->menuItem([
            'restaurant' => ['is_open' => false],
        ]);
        $closedMessage = 'Restaurant is closed now. Your items are in cart and you can checkout later when restaurant opens.';

        $this->actingAs($customer)
            ->from(route('menu'))
            ->post(route('cart.add', $menuItem))
            ->assertRedirect(route('menu'));

        $this->assertSame(1, Cart::count());

        $this->actingAs($customer)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', $closedMessage);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->checkoutPayload($customer))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', $closedMessage);

        $this->assertSame(1, Cart::count());
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_saves_optional_delivery_coordinates(): void
    {
        $customer = $this->user();
        $menuItem = $this->menuItem();

        $this->actingAs($customer)
            ->from(route('menu'))
            ->post(route('cart.add', $menuItem))
            ->assertRedirect(route('menu'));

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload($customer, [
                'delivery_latitude' => '-33.8688000',
                'delivery_longitude' => '151.2093000',
            ]))
            ->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame('-33.8688000', $order->delivery_latitude);
        $this->assertSame('151.2093000', $order->delivery_longitude);
    }

    public function test_checkout_rejects_invalid_delivery_coordinates(): void
    {
        $customer = $this->user();
        $menuItem = $this->menuItem();

        $this->actingAs($customer)
            ->from(route('menu'))
            ->post(route('cart.add', $menuItem));

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->checkoutPayload($customer, [
                'delivery_latitude' => '91',
                'delivery_longitude' => '181',
            ]))
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors(['delivery_latitude', 'delivery_longitude']);

        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function menuItem(array $overrides = []): MenuItem
    {
        $restaurant = Restaurant::create(array_merge([
            'name' => 'Arcade Kebab House',
            'email' => 'orders@example.com',
            'phone' => '+61 400 000 000',
            'address' => 'Address to be configured',
            'formatted_address' => 'Address to be configured',
            'timezone' => 'Australia/Sydney',
            'opening_time' => null,
            'closing_time' => null,
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ], $overrides['restaurant'] ?? []));

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
        ]);

        return MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Kebab',
            'slug' => 'chicken-kebab',
            'description' => 'Grilled chicken kebab.',
            'price' => 14.50,
            'is_available' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function checkoutPayload(User $customer, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => $customer->name,
            'customer_phone' => '+61 400 000 000',
            'customer_email' => $customer->email,
            'delivery_address' => 'Customer entered delivery address',
            'payment_method' => 'cod',
        ], $overrides);
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Customer User',
            'email' => 'customer-checkout@example.com',
            'phone' => '+61 400 000 000',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);
    }
}
