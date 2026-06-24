<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cart = $this->resource['cart'];
        $summary = $this->resource['summary'];

        return [
            'id' => $cart->id,
            'status' => $cart->status,
            'count' => $summary['count'],
            'subtotal' => $summary['subtotal'],
            'delivery_fee' => $summary['delivery_fee'],
            'minimum_order_amount' => $summary['minimum_order_amount'],
            'total' => $summary['total'],
            'currency' => $summary['currency'],
            'restaurant' => $summary['restaurant'] ? new RestaurantResource($summary['restaurant']) : null,
            'items' => $summary['items'],
        ];
    }
}
