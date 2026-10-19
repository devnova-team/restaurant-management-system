<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Menu\MenuItemController;
use App\Http\Controllers\Public\PublicMenuController;
use App\Http\Controllers\Public\PublicOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Cashier
    Route::middleware('role:cashier')->group(function () {
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::put('orders/{order}', [OrderController::class, 'update']);
        Route::put('orders/{order}/final-status', [OrderController::class, 'updateFinalStatus']);
        Route::post('invoices', [InvoiceController::class, 'store']);
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'storePayment']);
    });

    // Kitchen
    Route::middleware('role:kitchen')->group(function () {
        Route::put('kitchen/orders/{order}/status', [OrderController::class, 'updateKitchenStatus']);
    });

    // Owner
    Route::middleware('role:owner')->group(function () {
        Route::get('orders/{order}', [OrderController::class, 'show']);

        Route::get('/staff', [StaffController::class, 'index']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::put('/staff/{staff}', [StaffController::class, 'update']);
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

        Route::prefix('ingredients')->group(function () {
            Route::get('/', [IngredientController::class, 'index']);
            Route::post('/', [IngredientController::class, 'store']);
            Route::get('/{ingredient}', [IngredientController::class, 'show']);
            Route::put('/{ingredient}', [IngredientController::class, 'update']);
            Route::delete('/{ingredient}', [IngredientController::class, 'destroy']);
        });

        Route::prefix('menu-items')->group(function () {
            Route::get('/', [MenuItemController::class, 'index']);
            Route::post('/', [MenuItemController::class, 'store']);
            Route::put('/{menuItem}', [MenuItemController::class, 'update']);
            Route::delete('/{menuItem}', [MenuItemController::class, 'destroy']);
            Route::put('/{menuItem}/recipe', [RecipeController::class, 'replace']);
        });
    });
});

require __DIR__.'/dashboard.php';
