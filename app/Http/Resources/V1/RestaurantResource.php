<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\RestaurantAvailabilityService;
use App\Support\Money;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $availability = app(RestaurantAvailabilityService::class)->status($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'formatted_address' => $this->formatted_address,
            'short_description' => $this->short_description,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'timezone' => $this->timezone,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'delivery_fee' => (float) $this->delivery_fee,
            'minimum_order_amount' => (float) $this->minimum_order_amount,
            'logo_url' => $this->logo_url,
            'cover_image_url' => $this->cover_image_url,
            'initials' => $this->initials,
            'is_open' => (bool) $this->is_open,
            'is_open_for_orders' => (bool) $availability['is_open'],
            'availability_label' => $availability['label'],
            'availability_reason' => $availability['reason'],
            'next_opening_time' => $availability['opens_at'] ?? null,
            'current_closing_time' => $availability['closes_at'] ?? null,
            'currency' => Money::code(),
        ];
    }
}
