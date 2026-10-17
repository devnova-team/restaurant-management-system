<?php
namespace App\Services;

use App\Repositories\Interfaces\AnalyticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService{

    private const COUNTED_STATUSES=['served','delivered'];
    public function __construct(protected AnalyticsRepositoryInterface $analytics){}

    public function foodCost(Carbon $from,Carbon $to):array{
        $cost=$this->analytics->totalFoodCost($from,$to,self::COUNTED_STATUSES);
        $revenue=$this->analytics->totalRevenue($from,$to,self::COUNTED_STATUSES);
        return [
            'food_cost_pct'         => $revenue > 0 ? round($cost / $revenue * 100, 2) : 0,
            'total_ingredient_cost' => round($cost, 2),
            'total_revenue'         => round($revenue, 2),
        ];
    }

    public function menuProfitability(Carbon $from, Carbon $to):Collection{
        return $this->analytics->menuItemStats($from,$to,self::COUNTED_STATUSES)
            ->map(function($row){
                $revenue=(float) $row->revenue;
                $cost=(float) $row->total_cost;
                $profit= $revenue - $cost;
                return [
                'menu_item_id'      => $row->menu_item_id,
                'name'              => $row->name,
                'units_sold'        => (int) $row->units_sold,
                'total_cost'        => $cost,
                'total_revenue'     => $revenue,
                'profit_margin'     => $profit,
                'profit_margin_pct' => $revenue > 0 ? $profit / $revenue * 100 : 0,
                ];
            })->sortByDesc('profit_margin')
            ->values();  
    }

}