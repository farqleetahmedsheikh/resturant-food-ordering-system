<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PasswordResetOtp;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeStripeCheckout();
    }

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
        ];

        $this->withHeader('Idempotency-Key', 'test-checkout-001')
            ->postJson('/api/v1/customer/checkout', $payload)
            ->assertCreated()
            ->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/session')
            ->assertJsonPath('data.stripe_checkout_session_id', 'cs_test_checkout_session')
            ->assertJsonPath('data.order.order_status', 'pending_payment')
            ->assertJsonPath('data.order.payment_method', 'stripe')
            ->assertJsonPath('data.order.payment_status', 'pending')
            ->assertJsonPath('data.order.total', 28.99);

        $this->withHeader('Idempotency-Key', 'test-checkout-001')
            ->postJson('/api/v1/customer/checkout', $payload)
            ->assertCreated();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', [
            'item_name' => $menuItem->name,
            'quantity' => 2,
        ]);
    }

    public function test_mobile_cart_supports_size_addons_and_item_notes(): void
    {
        $menuItem = $this->createMenuItem(['price' => 12]);
        $size = $menuItem->sizes()->create([
            'name' => 'Large',
            'price' => 15,
            'is_active' => true,
        ]);
        $addon = $menuItem->addons()->create([
            'name' => 'Garlic Sauce',
            'type' => 'dip',
            'price' => 1.5,
            'is_active' => true,
        ]);
        $customer = $this->createUser('customer');

        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/v1/customer/cart/items/'.$menuItem->id, [
            'quantity' => 2,
            'size_id' => $size->id,
            'addon_ids' => [$addon->id],
            'item_notes' => 'No onion',
        ])->assertOk()
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.subtotal', 33)
            ->assertJsonPath('data.items.0.size.name', 'Large')
            ->assertJsonPath('data.items.0.addons.0.name', 'Garlic Sauce')
            ->assertJsonPath('data.items.0.item_notes', 'No onion');

        $this->withHeader('Idempotency-Key', 'test-checkout-options-001')
            ->postJson('/api/v1/customer/checkout', [
                'customer_name' => $customer->name,
                'customer_phone' => '03001234567',
                'customer_email' => $customer->email,
                'delivery_address' => 'Demo delivery address',
            ])
            ->assertCreated()
            ->assertJsonPath('data.order.items.0.size_name', 'Large')
            ->assertJsonPath('data.order.items.0.item_notes', 'No onion');

        $this->assertDatabaseHas('order_items', [
            'item_name' => $menuItem->name,
            'size_name' => 'Large',
            'item_notes' => 'No onion',
            'quantity' => 2,
        ]);
    }

    public function test_mobile_password_reset_otp_endpoints_reset_password(): void
    {
        $user = $this->createUser('customer', [
            'email' => 'reset@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $this->postJson('/api/v1/auth/password/otp', [
            'email' => $user->email,
        ])->assertOk()
            ->assertJsonPath('success', true);

        PasswordResetOtp::query()->updateOrCreate(
            ['email' => $user->email],
            [
                'otp_hash' => Hash::make('123456'),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'verified_at' => null,
            ],
        );

        $this->postJson('/api/v1/auth/password/otp/verify', [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertOk()
            ->assertJsonPath('data.verified', true);

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_otps', [
            'email' => $user->email,
        ]);
    }

    public function test_customer_can_manage_saved_addresses_and_devices(): void
    {
        $customer = $this->createUser('customer');

        Sanctum::actingAs($customer, ['customer']);

        $addressId = $this->postJson('/api/v1/customer/addresses', [
            'label' => 'Home',
            'recipient_name' => 'Customer User',
            'phone' => '03001234567',
            'address' => '10 Demo Street Sydney NSW',
            'latitude' => -33.8688,
            'longitude' => 151.2093,
            'delivery_notes' => 'Ring bell',
            'is_default' => true,
        ])->assertCreated()
            ->assertJsonPath('data.label', 'Home')
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $this->getJson('/api/v1/customer/addresses')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->putJson('/api/v1/customer/addresses/'.$addressId, [
            'label' => 'Office',
            'recipient_name' => 'Customer User',
            'phone' => '03001234567',
            'address' => '20 Demo Street Sydney NSW',
            'is_default' => true,
        ])->assertOk()
            ->assertJsonPath('data.label', 'Office');

        $deviceId = $this->postJson('/api/v1/devices', [
            'device_uuid' => 'test-device',
            'device_name' => 'iPhone Test',
            'platform' => 'ios',
            'push_token' => 'ExponentPushToken[test]',
            'app_version' => '1.0.0',
        ])->assertOk()
            ->assertJsonPath('data.device_name', 'iPhone Test')
            ->json('data.id');

        $this->getJson('/api/v1/devices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing(['push_token' => 'ExponentPushToken[test]']);

        $this->deleteJson('/api/v1/devices/'.$deviceId)
            ->assertOk();

        $this->deleteJson('/api/v1/customer/addresses/'.$addressId)
            ->assertOk();
    }

    public function test_customer_profile_update_does_not_change_email(): void
    {
        $customer = $this->createUser('customer', [
            'email' => 'locked-email@example.com',
        ]);

        Sanctum::actingAs($customer, ['customer']);

        $this->putJson('/api/v1/customer/profile', [
            'name' => 'Updated Customer',
            'email' => 'changed-email@example.com',
            'phone' => '0400000000',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Customer')
            ->assertJsonPath('data.email', 'locked-email@example.com')
            ->assertJsonPath('data.phone', '0400000000');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'locked-email@example.com',
            'phone' => '0400000000',
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

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('data.delivery.status', 'accepted');

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/picked-up')
            ->assertOk()
            ->assertJsonPath('data.delivery.status', 'picked_up');

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/out-for-delivery')
            ->assertOk()
            ->assertJsonPath('data.order_status', 'out_for_delivery');

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/delivered')
            ->assertOk()
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

    public function test_customer_order_tracking_exposes_assigned_rider_location(): void
    {
        $customer = $this->createUser('customer', ['email' => 'tracking-customer@example.com']);
        $rider = $this->createUser('rider', [
            'email' => 'tracking-rider@example.com',
            'last_known_latitude' => -33.8688,
            'last_known_longitude' => 151.2093,
            'last_location_updated_at' => now(),
        ]);
        $order = $this->createOrder([
            'user_id' => $customer->id,
            'rider_id' => $rider->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'order_status' => 'out_for_delivery',
        ]);

        $order->delivery()->create([
            'rider_id' => $rider->id,
            'status' => 'out_for_delivery',
        ]);

        Sanctum::actingAs($customer, ['customer']);

        $this->getJson('/api/v1/customer/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rider.id', $rider->id)
            ->assertJsonPath('data.rider.last_known_latitude', -33.8688)
            ->assertJsonPath('data.rider.last_known_longitude', 151.2093)
            ->assertJsonPath('data.delivery.status', 'out_for_delivery');
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
            'currency' => 'AUD',
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'order_status' => 'pending',
        ], $overrides));
    }
}
