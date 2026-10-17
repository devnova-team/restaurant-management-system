<?php
namespace App\Repositories;

use App\Models\Invoice;
use App\Models\OrderItem;
use App\Models\OrderItemIngredientUsage;
use App\Repositories\Interfaces\AnalyticsRepositoryInterface;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface{

    // Filter order items by their order's date range and status.
    public function orderFilter(Carbon $from,Carbon $to,array $statuses):Closure{
        return fn(Builder $q)=>
            $q->whereIn('status',$statuses)
                ->whereBetween('created_at',[$from,$to]);
    }
    public function totalFoodCost(Carbon $from, Carbon $to, array $statuses): float
    {
        return (float) OrderItemIngredientUsage::query()
            ->whereHas('orderItem.order',$this->orderFilter($from,$to,$statuses))
                ->sum(DB::raw('quantity_used * cost_at_time_of_use'));
    }

    public function totalRevenue(Carbon $from,Carbon $to,array $statuses):float{
        return (float) Invoice::query()
            ->whereHas('order',$this->orderFilter($from,$to,$statuses))
                ->sum('total_amount');
    }

    public function menuItemStats(Carbon $from, Carbon $to, array $statuses): Collection
    {
            // Calculate the total ingredient cost for each order item separately
            // to prevent duplicate rows when joining ingredient usage records.
        $costPerLine=OrderItemIngredientUsage::query()
            ->select('order_item_id',DB::raw('SUM(quantity_used * cost_at_time_of_use)'))
                ->groupBy('order_item_id');
            return OrderItem::query()
                ->whereHas('order',$this->orderFilter($from,$to,$statuses))
                        // Join menu items to retrieve each item's name and current price.
                ->join('menu_items as m','m.id','=','order_items.menu_item_id')
                ->leftJoinSub($costPerLine,'c.order_item_id','=','order_items.id')
                ->groupBy('m.id','m.name')
                ->selectRaw('
                m.id as menu_item_id,
                m.name,
                SUM(order_items.quantity) as units_sold,
                SUM(order_items.quantity * m.price) as revenue,
                COALESCE(SUM(c.line_cost), 0) as total_cost
            ')
            ->toBase()
            ->get();
    }
}