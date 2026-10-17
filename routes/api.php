<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Menu\MenuItemController;
use App\Http\Controllers\Public\PublicMenuController;
use App\Http\Controllers\Public\PublicOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::apiResource('orders', OrderController::class);
});

Route::prefix('public')->group(function () {
    Route::get('/{restaurant}/menu', [PublicMenuController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('public.menu.index');

    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/{restaurant}/orders', [PublicOrderController::class, 'store']);
    });
    Route::get('/track/{order:tracking_token}', [PublicOrderController::class, 'track'])
        ->middleware('throttle:60,1')
        ->name('public.orders.track');
});

// Owner APIs
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // Staff
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});


require __DIR__.'/dashboard.php';

// Ingredients
Route::prefix('ingredients')->middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/', [IngredientController::class, 'index']);
    Route::post('/', [IngredientController::class, 'store']);
    Route::get('/{ingredient}', [IngredientController::class, 'show']);
    Route::put('/{ingredient}', [IngredientController::class, 'update']);
    Route::delete('/{ingredient}', [IngredientController::class, 'destroy']);
});

// Menu Items
Route::prefix('menu-items')->middleware(['auth:sanctum', 'role:owner'])->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::post('/', [MenuItemController::class, 'store']);
        Route::put('/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/{menuItem}', [MenuItemController::class, 'destroy']);
        // Recipe
        Route::put('/{menuItem}/recipe', [RecipeController::class, 'replace']);
    });

require __DIR__.'/dashboard.php';

