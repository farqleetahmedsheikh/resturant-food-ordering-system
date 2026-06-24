<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'short_description' => $this->short_description,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'delivery_fee' => (float) $this->delivery_fee,
            'minimum_order_amount' => (float) $this->minimum_order_amount,
            'logo_url' => $this->logo_url,
            'cover_image_url' => $this->cover_image_url,
            'is_open' => (bool) $this->is_open,
            'is_active' => (bool) $this->is_active,
            'currency' => 'PKR',
        ];
    }
}
