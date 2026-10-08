<?php

namespace App\Providers;

use App\Repositories\BillingStatsRepository;
use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use App\Repositories\Interfaces\LowStockStatsRepositoryInterface;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;
use App\Repositories\LowStockStatsRepository;
use App\Repositories\OrderStatsRepository;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OrderStatsRepositoryInterface::class, OrderStatsRepository::class);
        $this->app->bind(BillingStatsRepositoryInterface::class,BillingStatsRepository::class);
        $this->app->bind(LowStockStatsRepositoryInterface::class,LowStockStatsRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
