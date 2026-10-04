<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Public\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Dashboard routes
require __DIR__.'/dashboard.php';

// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});

// Public menu
Route::prefix('public')->group(function () {
    Route::get('/{restaurant}/menu', [PublicMenuController::class, 'index'])
        ->name('public.menu.index');
});