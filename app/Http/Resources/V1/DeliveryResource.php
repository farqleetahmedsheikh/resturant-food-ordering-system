<?php

namespace App\Http\Resources\V1;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'rider_id' => $this->rider_id,
            'status' => $this->status,
            'status_label' => $this->status ? (Delivery::STATUSES[$this->status] ?? $this->status) : null,
            'pickup_time' => $this->pickup_time?->toISOString(),
            'delivered_time' => $this->delivered_time?->toISOString(),
            'notes' => $this->notes,
        ];
    }
}
