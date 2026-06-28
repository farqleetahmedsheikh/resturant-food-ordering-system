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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeStripeCheckout();
    }

    public function test_checkout_page_no_longer_shows_cod_and_uses_aud_card_payment(): void
    {
        $customer = $this->user('customer');
        $menuItem = $this->menuItem(['price' => 14.50]);

        $this->actingAs($customer)
            ->post(route('cart.add', $menuItem));

        $this->actingAs($customer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Secure card payment')
            ->assertSee('$14.50')
            ->assertDontSee('Cash on Delivery')
            ->assertDontSee('COD')
            ->assertDontSee('Rs.')
            ->assertDontSee('PKR');
    }

    public function test_missing_stripe_config_returns_safe_error_and_does_not_create_order(): void
    {
        config(['services.stripe.secret_key' => null]);

        $customer = $this->user('customer');
        $menuItem = $this->menuItem();

        $this->actingAs($customer)
            ->post(route('cart.add', $menuItem));

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->checkoutPayload($customer))
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_success_and_cancel_urls_do_not_mark_order_paid(): void
    {
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer, [
            'stripe_checkout_session_id' => 'cs_test_success_page',
        ]);

        $this->actingAs($customer)
            ->get(route('checkout.success', ['session_id' => 'cs_test_success_page']))
            ->assertOk()
            ->assertSee('Payment is being confirmed');

        $this->assertSame('pending', $order->fresh()->payment_status);

        $this->actingAs($customer)
            ->get(route('checkout.cancel'))
            ->assertOk()
            ->assertSee('Payment cancelled');

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_invalid_webhook_signature_is_rejected(): void
    {
        $payload = json_encode([
            'id' => 'evt_invalid_signature',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_missing']],
        ], JSON_THROW_ON_ERROR);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);

        $this->assertDatabaseCount('stripe_events', 0);
    }

    public function test_checkout_session_completed_webhook_marks_order_paid_once(): void
    {
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer, [
            'stripe_checkout_session_id' => 'cs_test_completed',
        ]);

        $payload = $this->eventPayload('evt_completed_once', 'checkout.session.completed', [
            'id' => 'cs_test_completed',
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'payment_intent' => 'pi_test_completed',
            'client_reference_id' => (string) $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        $this->postStripeWebhook($payload)->assertOk();
        $this->postStripeWebhook($payload)->assertOk();

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pi_test_completed', $order->stripe_payment_intent_id);
        $this->assertNotNull($order->paid_at);

        $this->assertDatabaseCount('stripe_events', 1);
        $this->assertDatabaseCount('order_status_histories', 2);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'previous_status' => 'pending_payment',
            'new_status' => 'pending',
            'reason' => 'Stripe payment confirmed',
        ]);
    }

    public function test_checkout_session_expired_marks_unpaid_order_cancelled(): void
    {
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer, [
            'stripe_checkout_session_id' => 'cs_test_expired',
        ]);

        $payload = $this->eventPayload('evt_expired_once', 'checkout.session.expired', [
            'id' => 'cs_test_expired',
            'object' => 'checkout.session',
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
        ]);

        $this->postStripeWebhook($payload)->assertOk();

        $order->refresh();

        $this->assertSame('cancelled', $order->payment_status);
        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('expired', $order->stripe_payment_status);
        $this->assertNotNull($order->payment_cancelled_at);
    }

    public function test_payment_intent_failed_marks_matchable_order_failed(): void
    {
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer);

        $payload = $this->eventPayload('evt_failed_once', 'payment_intent.payment_failed', [
            'id' => 'pi_test_failed',
            'object' => 'payment_intent',
            'status' => 'requires_payment_method',
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
            'last_payment_error' => [
                'message' => 'Your card was declined.',
            ],
        ]);

        $this->postStripeWebhook($payload)->assertOk();

        $order->refresh();

        $this->assertSame('failed', $order->payment_status);
        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('pi_test_failed', $order->stripe_payment_intent_id);
        $this->assertSame('Your card was declined.', $order->payment_failure_reason);
    }

    public function test_unpaid_stripe_order_cannot_be_assigned_to_rider(): void
    {
        $admin = $this->user('admin', ['email' => 'admin@example.com']);
        $rider = $this->user('rider', ['email' => 'rider@example.com']);
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer);

        $this->actingAs($admin)
            ->post(route('admin.orders.assign-rider', $order), [
                'rider_id' => $rider->id,
            ])
            ->assertSessionHas('status', 'Stripe payment must be confirmed before assigning a rider.');

        $this->assertNull($order->fresh()->rider_id);
    }

    public function test_api_rejects_cod_checkout_method(): void
    {
        $customer = $this->user('customer');
        Sanctum::actingAs($customer, ['customer']);

        $this->postJson('/api/v1/customer/checkout', array_merge(
            $this->checkoutPayload($customer),
            ['payment_method' => 'cod'],
        ))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');
    }

    public function test_admin_can_see_stripe_payment_status_and_reference(): void
    {
        $admin = $this->user('admin', ['email' => 'admin-reference@example.com']);
        $customer = $this->user('customer');
        $order = $this->stripeOrder($customer, [
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'stripe_checkout_session_id' => 'cs_test_admin_reference',
            'stripe_payment_intent_id' => 'pi_test_admin_reference',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Stripe card')
            ->assertSee('Paid')
            ->assertSee('cs_test_admin_reference')
            ->assertSee('pi_test_admin_reference');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function menuItem(array $overrides = []): MenuItem
    {
        $restaurant = Restaurant::query()->first() ?: Restaurant::create([
            'name' => 'Arcade Kebab House',
            'email' => 'orders@example.com',
            'phone' => '+61 400 000 000',
            'address' => 'Sydney NSW',
            'formatted_address' => 'Sydney NSW',
            'timezone' => 'Australia/Sydney',
            'opening_time' => null,
            'closing_time' => null,
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        $category = Category::query()->first() ?: Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
        ]);

        return MenuItem::create(array_merge([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Kebab',
            'slug' => 'chicken-kebab-'.random_int(1000, 9999),
            'description' => 'Grilled chicken kebab.',
            'price' => 12.00,
            'is_available' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function user(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role).' User',
            'email' => $role.random_int(1000, 9999).'@example.com',
            'phone' => '+61 400 000 000',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function stripeOrder(User $customer, array $overrides = []): Order
    {
        $restaurant = Restaurant::query()->first() ?: Restaurant::create([
            'name' => 'Arcade Kebab House',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        $order = Order::create(array_merge([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.random_int(1000, 9999),
            'customer_name' => $customer->name,
            'customer_phone' => '+61 400 000 000',
            'customer_email' => $customer->email,
            'delivery_address' => 'Customer address',
            'subtotal' => 20,
            'delivery_fee' => 4.99,
            'total' => 24.99,
            'currency' => 'AUD',
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'order_status' => 'pending_payment',
            'stripe_checkout_session_id' => 'cs_test_'.random_int(10000, 99999),
            'stripe_payment_status' => 'unpaid',
        ], $overrides));

        $order->statusHistories()->create([
            'previous_status' => null,
            'new_status' => $order->order_status,
            'changed_by_user_id' => $customer->id,
            'changed_by_role' => 'customer',
            'reason' => 'Stripe Checkout session requested',
            'metadata' => ['source' => 'test'],
        ]);

        return $order;
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
        ], $overrides);
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
