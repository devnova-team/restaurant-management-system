<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum','role:owner'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});