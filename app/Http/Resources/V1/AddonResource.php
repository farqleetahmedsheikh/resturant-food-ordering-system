<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'price' => (float) $this->price,
            'image_url' => $this->image_url,
            'sort_order' => (int) $this->sort_order,
            'is_available' => (bool) $this->is_available,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) ($this->pivot?->is_default ?? false),
        ];
    }
}
