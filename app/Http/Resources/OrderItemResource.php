<?php
// app/Http/Resources/OrderItemResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        
        return [
            'id'           => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'menu_item'    => $this->whenLoaded('menuItem', fn () => [
                'name'  => $this->menuItem->name,
                'price' => $this->menuItem->price,
            ]),
            'quantity'     => $this->quantity,
            'notes'        => $this->notes,
        ];
    }
}
