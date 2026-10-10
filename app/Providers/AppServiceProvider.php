<?php

namespace App\Providers;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\OrderRepositoryInterface; 
use App\Repositories\Eloquent\OrderRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            InvoiceRepository::class
        );
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}


