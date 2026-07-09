<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => (bool) $this->is_active,
            'last_known_latitude' => $this->last_known_latitude !== null ? (float) $this->last_known_latitude : null,
            'last_known_longitude' => $this->last_known_longitude !== null ? (float) $this->last_known_longitude : null,
            'last_location_updated_at' => $this->last_location_updated_at?->toISOString(),
            'assigned_orders_count' => $this->whenCounted('assignedOrders'),
            'delivered_orders_count' => $this->whenCounted('deliveredOrders'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
