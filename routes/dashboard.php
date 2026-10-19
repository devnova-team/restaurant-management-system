<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum','role:owner'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    Route::prefix('analytics')->group(function(){
        Route::get('/food-cost', [AnalyticsController::class,'foodCost']);
        Route::get('/menu-profitability',[AnalyticsController::class,'menuProfitability']);
    });
});