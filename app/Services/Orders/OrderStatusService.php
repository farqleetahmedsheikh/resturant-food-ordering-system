<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\User;
use App\Services\Email\OrderEmailService;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public function __construct(
        private AuditLogger $auditLogger,
        private OrderEmailService $orderEmailService,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function change(Order $order, string $newStatus, ?User $actor = null, ?string $reason = null, array $metadata = []): Order
    {
        if (! array_key_exists($newStatus, Order::STATUSES)) {
            throw new BusinessRuleException('Invalid order status.');
        }

        $notificationStatus = null;

        $updatedOrder = DB::transaction(function () use ($order, $newStatus, $actor, $reason, $metadata, &$notificationStatus): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousStatus = $lockedOrder->order_status;

            if ($previousStatus === $newStatus) {
                return $lockedOrder->fresh(['items', 'user', 'rider', 'delivery', 'statusHistories']);
            }

            $payload = ['order_status' => $newStatus];

            if ($newStatus === 'delivered') {
                $payload['payment_status'] = $lockedOrder->payment_method === 'cod' ? 'paid' : $lockedOrder->payment_status;
                $payload['delivered_at'] = $lockedOrder->delivered_at ?? now();
            }

            if ($newStatus === 'cancelled') {
                $payload['payment_status'] = $lockedOrder->payment_method === 'cod' ? 'cancelled' : $lockedOrder->payment_status;
            }

            $lockedOrder->update($payload);

            if ($lockedOrder->delivery) {
                $this->syncDeliveryFromOrderStatus($lockedOrder, $newStatus);
            }

            $lockedOrder->statusHistories()->create([
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'changed_by_user_id' => $actor?->id,
                'changed_by_role' => $actor?->role,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            $this->auditLogger->record(
                'order.status_changed',
                $actor,
                $lockedOrder,
                ['order_status' => $previousStatus],
                ['order_status' => $newStatus],
            );

            $notificationStatus = $newStatus;

            return $lockedOrder->fresh(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });

        if ($notificationStatus === 'accepted') {
            $this->orderEmailService->sendOrderConfirmed($updatedOrder);
        }

        if ($notificationStatus === 'delivered') {
            $this->orderEmailService->sendOrderDelivered($updatedOrder);
        }

        return $updatedOrder;
    }

    private function syncDeliveryFromOrderStatus(Order $order, string $status): void
    {
        if ($status === 'out_for_delivery') {
            $order->delivery->update(['status' => 'out_for_delivery']);
        }

        if ($status === 'delivered') {
            $order->delivery->update([
                'status' => 'delivered',
                'delivered_time' => $order->delivery->delivered_time ?? now(),
            ]);
        }

        if ($status === 'cancelled') {
            $order->delivery->update(['status' => 'failed']);
        }
    }
}
