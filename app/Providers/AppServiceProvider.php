<?php

namespace App\Providers;

use App\Interfaces\InvoiceInterface;
use App\Interfaces\OrderInterface;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrderInterface::class,
            OrderRepository::class
        );

        $this->app->bind(
            InvoiceInterface::class,
            InvoiceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
