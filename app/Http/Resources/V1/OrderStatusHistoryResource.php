<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'changed_by_user_id' => $this->changed_by_user_id,
            'changed_by_role' => $this->changed_by_role,
            'reason' => $this->reason,
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
