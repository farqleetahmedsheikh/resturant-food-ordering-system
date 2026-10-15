<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FinanceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_dashboard_compares_paid_periods_and_shows_live_breakdowns(): void
    {
        $admin = $this->admin();
        [$restaurant, $item] = $this->restaurantAndItem();
        $currentOrder = $this->paidOrder($restaurant, $item, 'ORD-CURRENT-1', '2026-10-10 12:00:00', 26, 20);
        $this->paidOrder($restaurant, $item, 'ORD-PREVIOUS-1', '2026-10-09 12:00:00', 18, 14);

        $this->actingAs($admin)
            ->get(route('admin.finance', [
                'range' => 'custom',
                'date_from' => '2026-10-10',
                'date_to' => '2026-10-10',
                'compare' => 'previous_period',
            ]))
            ->assertOk()
            ->assertSee('Finance overview')
            ->assertSee('A clear view of your restaurant')
            ->assertSee('Total sales')
            ->assertSee('Sales by category')
            ->assertSee('Orders by day')
            ->assertSee('Top selling products')
            ->assertSee($currentOrder->order_number)
            ->assertSee('Mixed Kebab');
    }

    public function test_finance_exports_csv_and_print_report_for_the_selected_period(): void
    {
        $admin = $this->admin();
        [$restaurant, $item] = $this->restaurantAndItem();
        $order = $this->paidOrder($restaurant, $item, 'ORD-EXPORT-1', '2026-10-10 12:00:00', 26, 20);

        $response = $this->actingAs($admin)
            ->get(route('admin.finance', [
                'range' => 'custom',
                'date_from' => '2026-10-10',
                'date_to' => '2026-10-10',
                'export' => 'csv',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Order ID', $csv);
        $this->assertStringContainsString($order->order_number, $csv);

        $this->actingAs($admin)
            ->get(route('admin.finance', ['range' => 'today', 'report' => 1]))
            ->assertOk()
            ->assertSee('window.print()', false);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Finance Admin',
            'email' => 'finance-admin@example.com',
            'phone' => '0400000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function restaurantAndItem(): array
    {
        $restaurant = Restaurant::create([
            'name' => 'Finance Test Restaurant',
            'timezone' => 'UTC',
            'delivery_fee' => 6,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);
        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'finance-kebabs',
            'is_active' => true,
        ]);
        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Mixed Kebab',
            'slug' => 'finance-mixed-kebab',
            'price' => 20,
            'is_available' => true,
        ]);

        return [$restaurant, $item];
    }

    private function paidOrder(Restaurant $restaurant, MenuItem $item, string $number, string $paidAt, float $total, float $subtotal): Order
    {
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => $number,
            'customer_name' => 'Test Customer',
            'customer_phone' => '0400000001',
            'delivery_address' => '1 Test Street',
            'subtotal' => $subtotal,
            'delivery_fee' => $total - $subtotal,
            'total' => $total,
            'currency' => 'AUD',
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'paid_at' => $paidAt,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $item->id,
            'item_name' => $item->name,
            'quantity' => 1,
            'price' => $subtotal,
            'total' => $subtotal,
        ]);

        return $order;
    }
}
