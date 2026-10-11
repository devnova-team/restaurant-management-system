<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'channel'          => $this->channel,
            'status'           => $this->status,
            'customer_name'    => $this->customer_name,
            'customer_phone'   => $this->customer_phone,
            'delivery_address' => $this->delivery_address,
            'total'            => $this->whenLoaded('orderItems', fn () => $this->calculateTotal()),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }


    private function calculateTotal(): string
    {
        $total = $this->orderItems->sum(
            fn ($item) => $item->quantity * ($item->menuItem?->price ?? 0)
        );

        return number_format($total, 2, '.', '');
    }
}
