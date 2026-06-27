<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_menu_is_available_and_customer_cart_requires_authentication(): void
    {
        $menuItem = $this->createMenuItem();

        $this->getJson('/api/v1/menu-items')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $this->postJson('/api/v1/customer/cart/items/'.$menuItem->id, [
            'quantity' => 1,
        ])->assertUnauthorized();
    }

    public function test_public_restaurant_endpoint_exposes_backend_availability_state(): void
    {
        Restaurant::create([
            'name' => 'Arcade Kebab House',
            'email' => 'hello@example.com',
            'phone' => '+61 400 000 000',
            'address' => 'Demo address',
            'timezone' => 'Australia/Sydney',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 20,
            'is_open' => false,
        ]);

        $this->getJson('/api/v1/restaurant')
            ->assertOk()
            ->assertJsonPath('data.is_open', false)
            ->assertJsonPath('data.is_open_for_orders', false)
            ->assertJsonPath('data.availability_label', 'Ordering paused')
            ->assertJsonPath('data.timezone', 'Australia/Sydney')
            ->assertJsonPath('data.currency', 'AUD');
    }

    public function test_public_menu_can_include_unavailable_items_for_mobile_state(): void
    {
        $available = $this->createMenuItem();
        $unavailable = MenuItem::create([
            'restaurant_id' => $available->restaurant_id,
            'category_id' => $available->category_id,
            'name' => 'Sold Out Kebab',
            'slug' => 'sold-out-kebab',
            'description' => 'Returns only when requested by mobile.',
            'price' => 13,
            'is_featured' => false,
            'is_available' => false,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/v1/menu-items')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/menu-items?include_unavailable=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/menu-items/'.$unavailable->id)
            ->assertOk()
            ->assertJsonPath('data.is_available', false);
    }

    public function test_customer_can_checkout_with_database_cart_and_idempotency_key(): void
    {
        $menuItem = $this->createMenuItem(['price' => 12]);
        $customer = $this->createUser('customer');

        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/v1/customer/cart/items/'.$menuItem->id, [
            'quantity' => 2,
        ])->assertOk()
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.subtotal', 24);

        $payload = [
            'customer_name' => $customer->name,
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'delivery_address' => 'Demo delivery address',
            'payment_method' => 'cod',
        ];

        $this->withHeader('Idempotency-Key', 'test-checkout-001')
            ->postJson('/api/v1/customer/checkout', $payload)
            ->assertCreated()
            ->assertJsonPath('data.order_status', 'pending')
            ->assertJsonPath('data.total', 28.99);

        $this->withHeader('Idempotency-Key', 'test-checkout-001')
            ->postJson('/api/v1/customer/checkout', $payload)
            ->assertCreated();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', [
            'item_name' => $menuItem->name,
            'quantity' => 2,
        ]);
    }

    public function test_admin_can_assign_order_and_rider_can_deliver_it(): void
    {
        $admin = $this->createUser('admin');
        $rider = $this->createUser('rider');
        $order = $this->createOrder(['order_status' => 'preparing']);

        Sanctum::actingAs($admin, ['admin']);

        $this->postJson('/api/v1/admin/orders/'.$order->id.'/assign-rider', [
            'rider_id' => $rider->id,
        ])->assertOk()
            ->assertJsonPath('data.order_status', 'assigned_to_rider')
            ->assertJsonPath('data.rider.id', $rider->id);

        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($rider, ['rider']);

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/status', [
            'status' => 'delivered',
        ])->assertOk()
            ->assertJsonPath('data.order_status', 'delivered')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    public function test_rider_cannot_view_another_riders_delivery(): void
    {
        $assignedRider = $this->createUser('rider', ['email' => 'assigned@example.com']);
        $otherRider = $this->createUser('rider', ['email' => 'other@example.com']);
        $order = $this->createOrder([
            'rider_id' => $assignedRider->id,
            'order_status' => 'assigned_to_rider',
        ]);

        $order->delivery()->create([
            'rider_id' => $assignedRider->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($otherRider, ['rider']);

        $this->getJson('/api/v1/rider/deliveries/'.$order->id)
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createUser(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role).' User',
            'email' => $role.'@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createMenuItem(array $overrides = []): MenuItem
    {
        $restaurant = Restaurant::create([
            'name' => 'Arcade Kebab House',
            'email' => 'hello@example.com',
            'phone' => '03001234567',
            'address' => 'Demo address',
            'timezone' => 'Australia/Sydney',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pizza',
            'slug' => 'pizza',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return MenuItem::create(array_merge([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Margherita Pizza',
            'slug' => 'margherita-pizza',
            'description' => 'Classic pizza with cheese and tomato.',
            'price' => 10,
            'is_featured' => true,
            'is_available' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createOrder(array $overrides = []): Order
    {
        $customer = $this->createUser('customer');
        $restaurant = Restaurant::first() ?: Restaurant::create([
            'name' => 'Arcade Kebab House',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        return Order::create(array_merge([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.random_int(1000, 9999),
            'customer_name' => $customer->name,
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'delivery_address' => 'Demo delivery address',
            'subtotal' => 30,
            'delivery_fee' => 4.99,
            'total' => 34.99,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ], $overrides));
    }
}
