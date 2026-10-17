<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuProfitabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'menu_item_id'      => $this['menu_item_id'],
        'name'              => $this['name'],
        'units_sold'        => $this['units_sold'],
        'total_cost'        => round($this['total_cost'], 2),
        'total_revenue'     => round($this['total_revenue'], 2),
        'profit_margin'     => round($this['profit_margin'], 2),
        'profit_margin_pct' => round($this['profit_margin_pct'], 1),
        ];
    }
}
