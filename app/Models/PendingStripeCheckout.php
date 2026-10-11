<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingStripeCheckout extends Model
{
    public const STATUS_CREATED = 'created';
    public const STATUS_SESSION_CREATED = 'session_created';
    public const STATUS_PAID = 'paid';
    public const STATUS_ORDER_CREATED = 'order_created';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'cart_id',
        'order_id',
        'source',
        'status',
        'customer_payload',
        'summary',
        'currency',
        'total',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_payment_status',
        'failure_reason',
        'paid_at',
        'failed_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'customer_payload' => 'array',
            'summary' => 'array',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
