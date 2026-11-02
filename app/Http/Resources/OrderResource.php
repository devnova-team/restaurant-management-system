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
            'order_number'     => $this->whenHas('daily_number', fn ($number) => (int) $number),
            'channel'          => $this->channel,
            'status'           => $this->status,
            'customer_name'    => $this->customer_name,
            'customer_phone'   => $this->customer_phone,
            'delivery_address' => $this->delivery_address,
            'items'            => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'total'            => $this->whenLoaded('orderItems', fn () => $this->calculateTotal()),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }

    private function calculateTotal(): string
    {
        if ($this->relationLoaded('invoice') && $this->invoice) {
            return number_format((float) $this->invoice->total_amount, 2, '.', '');
        }

        $cents = $this->orderItems->sum(
            fn ($item) => (int) round((float) ($item->menuItem?->price ?? 0) * 100) * (int) $item->quantity
        );

        return number_format($cents / 100, 2, '.', '');
    }
}
