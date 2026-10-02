<?php

namespace App\Providers;

use App\Repositories\Fakes\FakeBillingStatsRepository;
use App\Repositories\Fakes\FakeOrderStatsRepository;
use App\Repositories\Interfaces\BillingStatsRepositoryInterface;
use App\Repositories\Interfaces\OrderStatsRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(OrderStatsRepositoryInterface::class, FakeOrderStatsRepository::class);
        $this->app->bind(BillingStatsRepositoryInterface::class,FakeBillingStatsRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
