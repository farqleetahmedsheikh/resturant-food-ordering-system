<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLiveDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_dashboard_partial_shows_pending_orders(): void
    {
        $admin = $this->createUser('admin');
        $order = $this->createOrder();

        $this->actingAs($admin)
            ->get(route('admin.dashboard.live'))
            ->assertOk()
            ->assertSee('Quick pending orders')
            ->assertSee($order->order_number);
    }

    public function test_admin_can_confirm_pending_order_from_dashboard(): void
    {
        $admin = $this->createUser('admin');
        $order = $this->createOrder();

        $this->actingAs($admin)
            ->postJson(route('admin.dashboard.orders.confirm', $order))
            ->assertOk()
            ->assertJsonPath('message', 'Order confirmed successfully.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'accepted',
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'previous_status' => 'pending',
            'new_status' => 'accepted',
        ]);
    }

    public function test_admin_can_decline_pending_order_with_reason_from_dashboard(): void
    {
        $admin = $this->createUser('admin');
        $order = $this->createOrder();

        $this->actingAs($admin)
            ->postJson(route('admin.dashboard.orders.decline', $order), [
                'reason' => 'Customer requested cancellation.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Order declined successfully.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'previous_status' => 'pending',
            'new_status' => 'cancelled',
            'reason' => 'Customer requested cancellation.',
        ]);
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.'-live@example.com',
            'phone' => '03001234567',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createOrder(): Order
    {
        $customer = $this->createUser('customer');
        $restaurant = Restaurant::create([
            'name' => 'Arcade Kebab House',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        return Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'order_number' => 'ORD-'.now()->format('Ymd').'-1234',
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
        ]);
    }
}
