<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpsellRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'trigger_type' => $this->trigger_type,
            'trigger_config' => $this->trigger_config ?? [],
            'placement' => $this->placement,
            'priority' => (int) $this->priority,
            'max_recommendations' => $this->max_recommendations !== null ? (int) $this->max_recommendations : null,
            'allow_duplicates' => (bool) $this->allow_duplicates,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'products' => MenuItemResource::collection($this->whenLoaded('products')),
        ];
    }
}
