<?php
namespace App\Repositories\Interfaces;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;



Interface AnalyticsRepositoryInterface{
    public function totalFoodCost(Carbon $from, Carbon $to, array $statuses): float;

    public function totalRevenue(Carbon $from, Carbon $to, array $statuses): float;

    public function menuItemStats(Carbon $from, Carbon $to, array $statuses): Collection;
}