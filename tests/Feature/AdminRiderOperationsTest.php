<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRiderOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_rider_web_areas_are_role_protected(): void
    {
        $this->restaurant();

        $guestResponse = $this->get(route('admin.dashboard'));
        $guestResponse->assertRedirect(route('login'));

        $customer = $this->user('customer', 'customer-protected@example.com');
        $rider = $this->user('rider', 'rider-protected@example.com');
        $admin = $this->user('admin', 'admin-protected@example.com');

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($rider)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('rider.dashboard'))->assertForbidden();
    }

    public function test_admin_can_filter_update_and_assign_orders_through_safe_flow(): void
    {
        Mail::fake();

        $restaurant = $this->restaurant();
        $admin = $this->user('admin', 'admin-orders@example.com');
        $rider = $this->user('rider', 'rider-orders@example.com');
        $order = $this->order($restaurant, [
            'customer_name' => 'Sarah Walker',
            'order_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index', [
                'search' => 'Sarah',
                'payment_status' => 'paid',
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Sarah Walker');

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['order_status' => 'ready'])
            ->assertSessionHas('status', 'Order status cannot move from Pending to Ready.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'pending',
        ]);

        foreach (['accepted', 'preparing', 'ready'] as $status) {
            $this->actingAs($admin)
                ->patch(route('admin.orders.status', $order), [
                    'order_status' => $status,
                    'reason' => 'Operational test step.',
                ])
                ->assertRedirect()
                ->assertSessionHas('status', 'Order status updated successfully.');
        }

        $this->actingAs($admin)
            ->post(route('admin.orders.assign-rider', $order), ['rider_id' => $rider->id])
            ->assertRedirect()
            ->assertSessionHas('status', 'Rider assigned successfully.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'rider_id' => $rider->id,
            'order_status' => 'assigned_to_rider',
        ]);

        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'status' => 'assigned',
        ]);
    }

    public function test_rider_can_only_access_and_update_assigned_orders(): void
    {
        $restaurant = $this->restaurant();
        $rider = $this->user('rider', 'rider-own@example.com');
        $otherRider = $this->user('rider', 'rider-other@example.com');

        $ownOrder = $this->assignedOrder($restaurant, $rider);
        $otherOrder = $this->assignedOrder($restaurant, $otherRider);

        $this->actingAs($rider)
            ->get(route('rider.orders.show', $ownOrder))
            ->assertOk()
            ->assertSee($ownOrder->order_number);

        $this->actingAs($rider)
            ->get(route('rider.orders.show', $otherOrder))
            ->assertRedirect(route('rider.orders'))
            ->assertSessionHas('status', 'You are not allowed to access this order.');

        $this->actingAs($rider)
            ->post(route('rider.orders.update-status', $otherOrder), ['status' => 'accepted'])
            ->assertRedirect(route('rider.orders'))
            ->assertSessionHas('status', 'You are not allowed to access this order.');

        $this->assertDatabaseHas('deliveries', [
            'order_id' => $otherOrder->id,
            'status' => 'assigned',
        ]);
    }

    public function test_rider_api_actions_location_and_history_use_consistent_json(): void
    {
        Mail::fake();

        $restaurant = $this->restaurant();
        $rider = $this->user('rider', 'rider-api@example.com');
        $order = $this->assignedOrder($restaurant, $rider);

        Sanctum::actingAs($rider, ['rider']);

        $this->getJson('/api/v1/rider/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.active_deliveries', 1);

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/accept')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.delivery.status', 'accepted');

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/picked-up')
            ->assertOk()
            ->assertJsonPath('data.delivery.status', 'picked_up');

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/out-for-delivery')
            ->assertOk()
            ->assertJsonPath('data.order_status', 'out_for_delivery')
            ->assertJsonPath('data.delivery.status', 'out_for_delivery');

        $this->postJson('/api/v1/rider/location', [
            'latitude' => -33.8688,
            'longitude' => 151.2093,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.last_known_latitude', -33.8688)
            ->assertJsonPath('data.last_known_longitude', 151.2093);

        $this->postJson('/api/v1/rider/deliveries/'.$order->id.'/delivered')
            ->assertOk()
            ->assertJsonPath('data.order_status', 'delivered')
            ->assertJsonPath('data.delivery.status', 'delivered');

        $this->getJson('/api/v1/rider/deliveries/history')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.order_number', $order->order_number);
    }

    public function test_admin_image_upload_validation_rejects_webp_files(): void
    {
        Storage::fake('public');

        $restaurant = $this->restaurant();
        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $admin = $this->user('admin', 'admin-upload@example.com');

        $this->actingAs($admin)
            ->from(route('admin.menu-items.create'))
            ->post(route('admin.menu-items.store'), [
                'restaurant_id' => $restaurant->id,
                'category_id' => $category->id,
                'name' => 'Upload Test Kebab',
                'slug' => 'upload-test-kebab',
                'description' => 'Testing upload validation.',
                'price' => '14.50',
                'sort_order' => 1,
                'is_available' => '1',
                'image' => UploadedFile::fake()->image('bad.webp', 640, 480),
            ])
            ->assertRedirect(route('admin.menu-items.create'))
            ->assertSessionHasErrors('image');

        $this->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'restaurant_id' => $restaurant->id,
                'name' => 'Upload Category',
                'slug' => 'upload-category',
                'description' => 'Testing upload validation.',
                'sort_order' => 1,
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('bad-category.webp', 640, 480),
            ])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors('image');
    }

    public function test_menu_item_page_uses_aud_money_formatting_without_raw_blade_variables(): void
    {
        $restaurant = $this->restaurant();
        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Doner Kebab',
            'slug' => 'chicken-doner-kebab',
            'description' => 'Fresh chicken doner wrap.',
            'price' => 13.90,
            'compare_at_price' => 15.90,
            'is_available' => true,
            'sort_order' => 1,
        ]);
        $item->sizes()->create([
            'name' => 'Regular',
            'price' => 13.90,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $item->addons()->create([
            'name' => 'Garlic Sauce',
            'type' => 'dip',
            'price' => 1.50,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('menu.show', $item))
            ->assertOk()
            ->assertSee('$13.90')
            ->assertSee('$15.90')
            ->assertSee('$1.50')
            ->assertDontSee('($')
            ->assertDontSee('A$')
            ->assertDontSee('Rs.')
            ->assertDontSee('PKR');
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::create([
            'name' => 'Arcade Kebab House',
            'email' => 'orders@example.com',
            'phone' => '+61 400 000 000',
            'address' => '10 George Street, Sydney NSW',
            'formatted_address' => '10 George Street, Sydney NSW',
            'timezone' => 'Australia/Sydney',
            'opening_time' => '10:00',
            'closing_time' => '23:00',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 18,
            'is_open' => true,
        ]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'phone' => '+61 400 000 000',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function order(Restaurant $restaurant, array $overrides = []): Order
    {
        $customer = $this->user('customer', 'customer-'.Str::uuid().'@example.com');

        return Order::create(array_merge([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'order_number' => 'ORD-'.Str::upper(Str::random(8)),
            'customer_name' => $customer->name,
            'customer_phone' => '+61 400 000 000',
            'customer_email' => $customer->email,
            'delivery_address' => '20 Market Street, Sydney NSW',
            'delivery_latitude' => -33.8688,
            'delivery_longitude' => 151.2093,
            'subtotal' => 30,
            'delivery_fee' => 4.99,
            'total' => 34.99,
            'currency' => 'AUD',
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'stripe_checkout_session_id' => 'cs_test_'.Str::random(24),
            'stripe_payment_intent_id' => 'pi_test_'.Str::random(24),
            'stripe_payment_status' => 'paid',
            'paid_at' => now(),
            'order_status' => 'pending',
        ], $overrides));
    }

    private function assignedOrder(Restaurant $restaurant, User $rider): Order
    {
        $order = $this->order($restaurant, [
            'rider_id' => $rider->id,
            'order_status' => 'assigned_to_rider',
        ]);

        Delivery::create([
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'status' => 'assigned',
        ]);

        return $order;
    }
}
