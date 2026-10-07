<?php

namespace App\Providers;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
