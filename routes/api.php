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
});

// Owner APIs
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // Staff
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

    // Ingredients
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);

    // Recipe
    Route::put('/menu-items/{menuItem}/recipe', [RecipeController::class, 'replace']);
});

// Menu Items
Route::prefix('menu-items')
    ->middleware(['auth:sanctum', 'role:owner'])
    ->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::post('/', [MenuItemController::class, 'store']);
        Route::put('/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/{menuItem}', [MenuItemController::class, 'destroy']);
    });

require __DIR__.'/dashboard.php';
