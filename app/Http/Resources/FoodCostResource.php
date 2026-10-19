<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodCostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'food_cost_pct'         => round($this['food_cost_pct'], 2),
        'total_ingredient_cost' => round($this['total_ingredient_cost'], 2),
        'total_revenue'         => round($this['total_revenue'], 2),
        ];
    }
}
