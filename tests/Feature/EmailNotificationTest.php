<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderPlacedMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_placed_email_is_sent_with_checkout(): void
    {
        Mail::fake();

        $customer = $this->user('customer', 'customer-email@example.com');
        $restaurant = $this->restaurant();
        $category = \App\Models\Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
        ]);
        $menuItem = \App\Models\MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Kebab',
            'slug' => 'chicken-kebab',
            'price' => 14.50,
            'is_available' => true,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $menuItem));

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'customer_name' => $customer->name,
                'customer_phone' => '+61 400 000 000',
                'customer_email' => $customer->email,
                'delivery_address' => 'Demo delivery address',
                'payment_method' => 'cod',
            ])
            ->assertRedirect();

        Mail::assertSent(OrderPlacedMail::class, fn (OrderPlacedMail $mail) => $mail->order->items->contains('item_name', 'Chicken Kebab'));
    }

    public function test_order_confirmation_email_is_sent_when_admin_accepts_order(): void
    {
        Mail::fake();

        $admin = $this->user('admin', 'admin-email@example.com');
        $order = $this->order();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['order_status' => 'accepted'])
            ->assertRedirect();

        Mail::assertSent(OrderConfirmedMail::class, fn (OrderConfirmedMail $mail) => $mail->order->is($order));
    }

    public function test_order_delivered_email_is_sent_when_rider_completes_delivery(): void
    {
        Mail::fake();

        $rider = $this->user('rider', 'rider-email@example.com');
        $order = $this->order(['rider_id' => $rider->id, 'order_status' => 'out_for_delivery']);
        $order->delivery()->create([
            'rider_id' => $rider->id,
            'status' => 'out_for_delivery',
        ]);

        $this->actingAs($rider)
            ->post(route('rider.orders.update-status', $order), ['status' => 'delivered'])
            ->assertRedirect();

        Mail::assertSent(OrderDeliveredMail::class, fn (OrderDeliveredMail $mail) => $mail->order->is($order));
    }

    public function test_password_reset_otp_email_uses_custom_mailable(): void
    {
        Mail::fake();

        $user = $this->user('customer', 'reset-email@example.com');

        $this->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.otp'));

        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user): bool {
            return $mail->user->is($user)
                && preg_match('/^\d{6}$/', $mail->otp) === 1
                && $mail->expiresInMinutes === 10;
        });
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
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function order(array $overrides = []): Order
    {
        $customer = $this->user('customer', 'order-customer-'.uniqid().'@example.com');
        $restaurant = $this->restaurant();

        $order = Order::create(array_merge([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.random_int(1000, 9999),
            'customer_name' => $customer->name,
            'customer_phone' => '+61 400 000 000',
            'customer_email' => $customer->email,
            'delivery_address' => 'Demo delivery address',
            'subtotal' => 30,
            'delivery_fee' => 4.99,
            'total' => 34.99,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ], $overrides));

        $order->items()->create([
            'item_name' => 'Adana Kebab',
            'quantity' => 2,
            'price' => 15,
            'total' => 30,
            'addons_snapshot' => [],
            'addons_total' => 0,
        ]);

        return $order;
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::firstOrCreate(
            ['name' => 'Arcade Kebab House'],
            [
                'email' => 'orders@arcadekebab.test',
                'phone' => '+61 400 000 000',
                'delivery_fee' => 4.99,
                'minimum_order_amount' => 0,
                'is_open' => true,
            ],
        );
    }
}
