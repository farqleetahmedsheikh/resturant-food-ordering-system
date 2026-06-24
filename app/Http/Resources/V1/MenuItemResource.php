<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price !== null ? (float) $this->compare_at_price : null,
            'image_url' => $this->image_url,
            'preparation_time' => $this->preparation_time,
            'calories' => $this->calories,
            'is_featured' => (bool) $this->is_featured,
            'is_available' => (bool) $this->is_available,
            'sort_order' => (int) $this->sort_order,
            'sizes' => MenuItemSizeResource::collection($this->whenLoaded('activeSizes')),
            'addons' => MenuItemAddonResource::collection($this->whenLoaded('activeAddons')),
        ];
    }
}
