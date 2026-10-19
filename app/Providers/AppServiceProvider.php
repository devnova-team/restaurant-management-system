<?php

namespace App\Providers;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\AnalyticsRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\Interfaces\AnalyticsRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(AnalyticsRepositoryInterface::class, AnalyticsRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
