<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\IdempotencyKey;
use App\Models\Order;
use App\Models\User;
use App\Services\Cart\DatabaseCartService;
use App\Services\Security\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private DatabaseCartService $cartService,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function checkout(User $user, array $payload, ?string $idempotencyKey = null, ?Request $request = null): Order
    {
        $requestHash = hash('sha256', json_encode($payload));

        return DB::transaction(function () use ($user, $payload, $idempotencyKey, $requestHash, $request): Order {
            $idempotency = $this->claimIdempotencyKey($user, $idempotencyKey, $requestHash, $request);

            if ($idempotency?->order_id) {
                return Order::query()
                    ->with(['items', 'user', 'rider', 'delivery', 'statusHistories'])
                    ->findOrFail($idempotency->order_id);
            }

            $cart = $this->cartService->get($user);
            $summary = $this->cartService->summary($cart);
            $restaurant = $summary['restaurant'];

            if ($summary['count'] < 1) {
                throw new BusinessRuleException('Your cart is empty. Add a meal before checkout.');
            }

            if (! $restaurant?->is_open) {
                throw new BusinessRuleException('Restaurant is currently closed. Ordering may be unavailable.');
            }

            if ($summary['minimum_order_amount'] > 0 && $summary['subtotal'] < $summary['minimum_order_amount']) {
                throw new BusinessRuleException('Minimum order amount is Rs. '.number_format($summary['minimum_order_amount'], 2).'.');
            }

            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant?->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $payload['customer_name'],
                'customer_phone' => $payload['customer_phone'],
                'customer_email' => $payload['customer_email'] ?? null,
                'delivery_address' => $payload['delivery_address'],
                'order_notes' => $payload['order_notes'] ?? null,
                'subtotal' => $summary['subtotal'],
                'delivery_fee' => $summary['delivery_fee'],
                'total' => $summary['total'],
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($summary['items'] as $item) {
                $order->items()->create([
                    'menu_item_id' => $item['menu_item_id'],
                    'item_name' => $item['name'],
                    'size_name' => $item['size']['name'] ?? null,
                    'size_price' => $item['size']['price'] ?? null,
                    'addons_snapshot' => $item['addons'],
                    'addons_total' => $item['addons_total'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'total' => $item['line_total'],
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => null,
                'new_status' => 'pending',
                'changed_by_user_id' => $user->id,
                'changed_by_role' => $user->role,
                'reason' => 'Order placed',
                'metadata' => ['source' => 'mobile_api'],
            ]);

            $this->cartService->markConverted($cart);

            $idempotency?->update([
                'response_code' => 201,
                'response_body' => ['order_id' => $order->id],
                'order_id' => $order->id,
            ]);

            $this->auditLogger->record('order.created', $user, $order, [], ['total' => $order->total], $request);

            return $order->load(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });
    }

    private function claimIdempotencyKey(User $user, ?string $key, string $requestHash, ?Request $request): ?IdempotencyKey
    {
        if (! $key) {
            return null;
        }

        $idempotency = IdempotencyKey::query()
            ->where('user_id', $user->id)
            ->where('key', $key)
            ->lockForUpdate()
            ->first();

        if ($idempotency) {
            if ($idempotency->request_hash !== $requestHash) {
                throw new BusinessRuleException('Idempotency-Key was already used for a different checkout request.', 409);
            }

            return $idempotency;
        }

        return IdempotencyKey::create([
            'user_id' => $user->id,
            'key' => $key,
            'method' => $request?->method() ?? 'POST',
            'path' => $request?->path() ?? 'api/v1/customer/checkout',
            'request_hash' => $requestHash,
            'expires_at' => now()->addDay(),
        ]);
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
