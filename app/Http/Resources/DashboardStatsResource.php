<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'orders'=>$this->resource['orders'],
            'revenue'=>$this->resource['revenue'],
            'low_stock'=>$this->resource['low_stock'],
            'count_of_active_orders'=>$this->resource['count_of_active_orders'],
            'count_active_orders_by_status'=>$this->resource['count_active_orders_by_status'],
        ];
    }
}
