<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUSES = [
        'pending_payment' => 'Pending Payment',
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'preparing' => 'Preparing',
        'assigned_to_rider' => 'Assigned to Rider',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_METHODS = [
        'stripe' => 'Stripe card',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Pending payment',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ];

    protected $fillable = [
        'user_id',
        'rider_id',
        'restaurant_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'order_notes',
        'subtotal',
        'delivery_fee',
        'total',
        'currency',
        'payment_method',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_payment_status',
        'paid_at',
        'payment_failed_at',
        'payment_cancelled_at',
        'payment_failure_reason',
        'order_status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'paid_at' => 'datetime',
            'payment_failed_at' => 'datetime',
            'payment_cancelled_at' => 'datetime',
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method]
            ?? str((string) $this->payment_method)->headline()->toString();
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status]
            ?? str((string) $this->payment_status)->headline()->toString();
    }

    public function isStripePayment(): bool
    {
        return $this->payment_method === 'stripe';
    }

    public function hasConfirmedPayment(): bool
    {
        return $this->payment_status === 'paid'
            || $this->payment_method === 'cod';
    }

    public function canEnterFulfillment(): bool
    {
        return $this->order_status !== 'pending_payment'
            && $this->hasConfirmedPayment();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
