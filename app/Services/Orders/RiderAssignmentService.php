<?php

namespace App\Services\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\User;
use App\Services\Security\AuditLogger;
use Illuminate\Support\Facades\DB;

class RiderAssignmentService
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function assign(Order $order, User $rider, ?User $actor = null): Order
    {
        if ($rider->role !== 'rider' || ! $rider->is_active) {
            throw new BusinessRuleException('Please choose an active rider.');
        }

        if (in_array($order->order_status, ['delivered', 'cancelled'], true)) {
            throw new BusinessRuleException('Delivered or cancelled orders cannot be assigned.');
        }

        if (! $order->canEnterFulfillment()) {
            throw new BusinessRuleException('Stripe payment must be confirmed before assigning a rider.');
        }

        return DB::transaction(function () use ($order, $rider, $actor): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->canEnterFulfillment()) {
                throw new BusinessRuleException('Stripe payment must be confirmed before assigning a rider.');
            }

            $previousRiderId = $lockedOrder->rider_id;
            $previousStatus = $lockedOrder->order_status;

            $lockedOrder->update([
                'rider_id' => $rider->id,
                'order_status' => 'assigned_to_rider',
                'assigned_at' => now(),
            ]);

            $lockedOrder->delivery()->updateOrCreate(
                ['order_id' => $lockedOrder->id],
                [
                    'rider_id' => $rider->id,
                    'status' => 'assigned',
                ],
            );

            if ($previousStatus !== 'assigned_to_rider') {
                $lockedOrder->statusHistories()->create([
                    'previous_status' => $previousStatus,
                    'new_status' => 'assigned_to_rider',
                    'changed_by_user_id' => $actor?->id,
                    'changed_by_role' => $actor?->role,
                    'reason' => 'Rider assigned',
                    'metadata' => ['rider_id' => $rider->id],
                ]);
            }

            $this->auditLogger->record(
                'order.rider_assigned',
                $actor,
                $lockedOrder,
                ['rider_id' => $previousRiderId],
                ['rider_id' => $rider->id],
            );

            return $lockedOrder->fresh(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });
    }

    public function unassign(Order $order, ?User $actor = null): Order
    {
        if ($order->order_status === 'delivered') {
            throw new BusinessRuleException('Delivered orders cannot be unassigned.');
        }

        return DB::transaction(function () use ($order, $actor): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousRiderId = $lockedOrder->rider_id;
            $nextStatus = $lockedOrder->order_status === 'cancelled' ? 'cancelled' : 'preparing';

            $lockedOrder->update([
                'rider_id' => null,
                'order_status' => $nextStatus,
                'assigned_at' => null,
                'picked_up_at' => null,
            ]);

            $lockedOrder->delivery?->update([
                'rider_id' => null,
                'status' => 'pending',
                'pickup_time' => null,
                'delivered_time' => null,
            ]);

            $this->auditLogger->record(
                'order.rider_unassigned',
                $actor,
                $lockedOrder,
                ['rider_id' => $previousRiderId],
                ['rider_id' => null],
            );

            return $lockedOrder->fresh(['items', 'user', 'rider', 'delivery', 'statusHistories']);
        });
    }
}
