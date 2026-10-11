<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'description' => $this->description,
            'selection_type' => $this->selection_type,
            'is_required' => (bool) $this->is_required,
            'min_selections' => (int) $this->min_selections,
            'max_selections' => $this->max_selections !== null ? (int) $this->max_selections : null,
            'quantity_limit' => (int) $this->quantity_limit,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'addons' => AddonResource::collection($this->whenLoaded('activeAddons')),
        ];
    }
}
