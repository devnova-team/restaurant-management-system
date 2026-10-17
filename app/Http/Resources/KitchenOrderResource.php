<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'channel'    => $this->channel,
            'status'     => $this->status,
            'items'      => $this->orderItems->map(fn ($item) => [
                'menu_item' => $item->menuItem?->name,
                'quantity'  => $item->quantity,
                'notes'     => $item->notes,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
