<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PendingStripeCheckout;
use App\Models\PasswordResetOtp;
use App\Models\Restaurant;
use App\Models\UpsellRule;
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

    public function test_public_restaurant_endpoint_exposes_active_configured_menu_hero_slides(): void
    {
        Restaurant::create([
            'name' => 'Arcade Kebab House',
            'menu_hero_slides' => [
                ['id' => 'active', 'eyebrow' => 'Fresh food', 'title' => 'Authentic', 'highlight' => 'kebabs', 'description' => 'Made fresh.', 'image' => '/images/menu-hero/kebab-grill.jpg', 'is_active' => true, 'sort_order' => 1],
                ['id' => 'inactive', 'eyebrow' => 'Hidden', 'title' => 'Not shown', 'highlight' => 'now', 'description' => 'Inactive slide.', 'image' => '/images/menu-hero/pizza.jpg', 'is_active' => false, 'sort_order' => 0],
            ],
        ]);

        $this->getJson('/api/v1/restaurant')
            ->assertOk()
            ->assertJsonCount(1, 'data.menu_hero_slides')
            ->assertJsonPath('data.menu_hero_slides.0.id', 'active')
            ->assertJsonPath('data.menu_hero_slides.0.image_url', url('/images/menu-hero/kebab-grill.jpg'));
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

    public function test_customer_cart_accepts_grouped_addon_selections_and_enforces_required_groups(): void
    {
        $menuItem = $this->createMenuItem(['name' => 'Zinger Burger', 'slug' => 'zinger-burger', 'price' => 650]);
        $addon = Addon::create([
            'restaurant_id' => $menuItem->restaurant_id,
            'name' => 'Extra Cheese',
            'type' => 'customization',
            'price' => 100,
            'is_available' => true,
            'is_active' => true,
        ]);
        $group = AddonGroup::create([
            'restaurant_id' => $menuItem->restaurant_id,
            'name' => 'Burger Extras',
            'selection_type' => 'single',
            'is_required' => true,
            'min_selections' => 1,
            'max_selections' => 1,
            'quantity_limit' => 1,
            'is_active' => true,
        ]);
        $group->addons()->sync([$addon->id => ['sort_order' => 0, 'is_default' => false]]);
        $menuItem->addonGroups()->sync([$group->id => ['sort_order' => 0, 'is_active' => true]]);

        Sanctum::actingAs($this->createUser('customer'), ['customer']);

        $this->postJson('/api/v1/customer/cart/items/'.$menuItem->id, [
            'quantity' => 1,
            'addon_selections' => [],
        ])->assertStatus(422);

        $this->postJson('/api/v1/customer/cart/items/'.$menuItem->id, [
            'quantity' => 1,
            'addon_selections' => [
                ['group_id' => $group->id, 'addon_ids' => [$addon->id]],
            ],
        ])->assertOk()
            ->assertJsonPath('data.subtotal', 750)
            ->assertJsonPath('data.items.0.addons.0.name', 'Extra Cheese');
    }

    public function test_cart_recommendations_use_configured_upsell_rules(): void
    {
        $burger = $this->createMenuItem(['name' => 'Zinger Burger', 'slug' => 'zinger-burger', 'price' => 650]);
        $drink = MenuItem::create([
            'restaurant_id' => $burger->restaurant_id,
            'category_id' => $burger->category_id,
            'name' => 'Coca-Cola',
            'slug' => 'coca-cola',
            'price' => 150,
            'is_featured' => false,
            'is_available' => true,
            'sort_order' => 2,
        ]);

        $rule = UpsellRule::create([
            'restaurant_id' => $burger->restaurant_id,
            'name' => 'Burger meal',
            'trigger_type' => 'product',
            'trigger_config' => ['product_ids' => [$burger->id]],
            'placement' => 'cart',
            'priority' => 10,
            'max_recommendations' => 4,
            'is_active' => true,
        ]);
        $rule->products()->sync([$drink->id => ['sort_order' => 0]]);

        Sanctum::actingAs($this->createUser('customer'), ['customer']);

        $this->postJson('/api/v1/customer/cart/items/'.$burger->id, [
            'quantity' => 1,
        ])->assertOk();

        $this->getJson('/api/v1/customer/cart/recommendations?placement=cart')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Coca-Cola');
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
            ->assertJsonPath('data.order_id', null)
            ->assertJsonPath('data.order', null);

        $this->withHeader('Idempotency-Key', 'test-checkout-001')
            ->postJson('/api/v1/customer/checkout', $payload)
            ->assertCreated();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('pending_stripe_checkouts', 1);
        $this->assertDatabaseHas('pending_stripe_checkouts', [
            'stripe_checkout_session_id' => 'cs_test_checkout_session',
            'status' => PendingStripeCheckout::STATUS_SESSION_CREATED,
            'total' => 28.99,
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
            ->assertJsonPath('data.order', null);

        $pendingCheckout = PendingStripeCheckout::firstOrFail();
        $pendingItem = $pendingCheckout->summary['items'][0];

        $this->assertSame('Large', $pendingItem['size']['name']);
        $this->assertSame('No onion', $pendingItem['item_notes']);

        $payload = $this->eventPayload('evt_mobile_options_paid', 'checkout.session.completed', [
            'id' => $pendingCheckout->stripe_checkout_session_id,
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'payment_intent' => 'pi_test_mobile_options',
            'client_reference_id' => 'pending_checkout:'.$pendingCheckout->id,
            'metadata' => [
                'pending_checkout_id' => (string) $pendingCheckout->id,
            ],
        ]);

        $this->postStripeWebhook($payload)->assertOk();

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

    /**
     * @param  array<string, mixed>  $object
     */
    private function eventPayload(string $eventId, string $type, array $object): string
    {
        return json_encode([
            'id' => $eventId,
            'object' => 'event',
            'type' => $type,
            'data' => [
                'object' => $object,
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function postStripeWebhook(string $payload)
    {
        $timestamp = time();
        $secret = (string) config('services.stripe.webhook_secret');
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
    }
}
