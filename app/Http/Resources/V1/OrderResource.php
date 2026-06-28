<?php

namespace App\Http\Resources\V1;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => [
                'id' => $this->user_id,
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
            ],
            'rider' => new UserResource($this->whenLoaded('rider')),
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'delivery_address' => $this->delivery_address,
            'delivery_latitude' => $this->delivery_latitude !== null ? (float) $this->delivery_latitude : null,
            'delivery_longitude' => $this->delivery_longitude !== null ? (float) $this->delivery_longitude : null,
            'order_notes' => $this->order_notes,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'currency' => $this->currency ?? Money::code(),
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->payment_method_label,
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->payment_status_label,
            'order_status' => $this->order_status,
            'order_status_label' => Order::STATUSES[$this->order_status] ?? $this->order_status,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'picked_up_at' => $this->picked_up_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery' => new DeliveryResource($this->whenLoaded('delivery')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
