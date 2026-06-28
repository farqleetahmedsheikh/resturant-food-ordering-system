<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\Email\OrderEmailService;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\DB;

class DeliveryStatusService
{
    public function __construct(
        private AuditLogger $auditLogger,
        private OrderEmailService $orderEmailService,
    ) {}

    public function update(Order $order, User $rider, string $status, ?string $notes = null): Order
    {
        if ($order->rider_id !== $rider->id) {
            throw new BusinessRuleException('You are not allowed to access this order.', 403);
        }

        if (in_array($order->order_status, ['delivered', 'cancelled'], true)) {
            throw new BusinessRuleException('Delivered or cancelled orders cannot be updated again.');
        }

        if (! $order->canEnterFulfillment()) {
            throw new BusinessRuleException('Stripe payment must be confirmed before delivery can progress.');
        }

        if (! in_array($status, ['picked_up', 'out_for_delivery', 'delivered', 'failed'], true)) {
            throw new BusinessRuleException('Invalid delivery status.');
        }

        if ($status === 'failed' && blank($notes)) {
            throw new BusinessRuleException('Failed delivery reason is required.');
        }

        $updatedOrder = DB::transaction(function () use ($order, $rider, $status, $notes): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->canEnterFulfillment()) {
                throw new BusinessRuleException('Stripe payment must be confirmed before delivery can progress.');
            }

            $delivery = $lockedOrder->delivery()->firstOrCreate(
                ['order_id' => $lockedOrder->id],
                [
                    'rider_id' => $rider->id,
                    'status' => 'assigned',
                ],
            );
            $previousDeliveryStatus = $delivery->status;

            if ($status === 'picked_up') {
                $this->markPickedUp($lockedOrder, $delivery, $rider);
            } elseif ($status === 'out_for_delivery') {
                $this->markOutForDelivery($lockedOrder, $delivery, $rider);
            } elseif ($status === 'delivered') {
                $this->markDelivered($lockedOrder, $delivery, $rider);
            } else {
                $this->markFailed($lockedOrder, $delivery, $rider, (string) $notes);
            }

            $this->auditLogger->record(
                'delivery.status_changed',
                $rider,
                $lockedOrder,
                ['delivery_status' => $previousDeliveryStatus],
                ['delivery_status' => $status],
            );

            return $lockedOrder->fresh(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });

        if ($status === 'delivered') {
            $this->orderEmailService->sendOrderDelivered($updatedOrder);
        }

        return $updatedOrder;
    }

    private function markPickedUp(Order $order, Delivery $delivery, User $rider): void
    {
        $previousStatus = $order->order_status;

        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'picked_up',
            'pickup_time' => $delivery->pickup_time ?? now(),
        ]);

        $order->update([
            'order_status' => 'out_for_delivery',
            'picked_up_at' => $order->picked_up_at ?? now(),
        ]);

        $this->recordStatusHistory($order, $previousStatus, 'out_for_delivery', $rider, 'Delivery picked up');
    }

    private function markOutForDelivery(Order $order, Delivery $delivery, User $rider): void
    {
        $previousStatus = $order->order_status;

        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'out_for_delivery',
        ]);

        $order->update(['order_status' => 'out_for_delivery']);

        $this->recordStatusHistory($order, $previousStatus, 'out_for_delivery', $rider, 'Delivery out for delivery');
    }

    private function markDelivered(Order $order, Delivery $delivery, User $rider): void
    {
        $previousStatus = $order->order_status;

        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'delivered',
            'delivered_time' => $delivery->delivered_time ?? now(),
        ]);

        $order->update([
            'order_status' => 'delivered',
            'delivered_at' => $order->delivered_at ?? now(),
            'payment_status' => $order->payment_method === 'cod' ? 'paid' : $order->payment_status,
        ]);

        $this->recordStatusHistory($order, $previousStatus, 'delivered', $rider, 'Delivery completed');
    }

    private function markFailed(Order $order, Delivery $delivery, User $rider, string $notes): void
    {
        $previousStatus = $order->order_status;

        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'failed',
            'notes' => $notes,
        ]);

        $order->update(['order_status' => 'cancelled']);

        $this->recordStatusHistory($order, $previousStatus, 'cancelled', $rider, 'Delivery failed', ['notes' => $notes]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordStatusHistory(Order $order, string $previousStatus, string $newStatus, User $rider, string $reason, array $metadata = []): void
    {
        if ($previousStatus === $newStatus) {
            return;
        }

        $order->statusHistories()->create([
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by_user_id' => $rider->id,
            'changed_by_role' => $rider->role,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }
}
