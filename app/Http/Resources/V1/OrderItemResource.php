<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'item_name' => $this->item_name,
            'size_name' => $this->size_name,
            'size_price' => $this->size_price !== null ? (float) $this->size_price : null,
            'addons' => $this->addons_snapshot ?? [],
            'addons_total' => (float) $this->addons_total,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'total' => (float) $this->total,
        ];
    }
}
