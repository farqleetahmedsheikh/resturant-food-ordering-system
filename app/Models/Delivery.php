<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryFactory> */
    use HasFactory;

    public const STATUSES = [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'picked_up' => 'Picked Up',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'failed' => 'Failed',
    ];

    protected $fillable = [
        'order_id',
        'rider_id',
        'pickup_time',
        'delivered_time',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pickup_time' => 'datetime',
            'delivered_time' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
