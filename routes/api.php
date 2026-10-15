<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Public\PublicMenuController;
use App\Http\Controllers\Public\PublicOrderController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Menu\MenuItemController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::apiResource('orders', OrderController::class);
});
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');



Route::prefix('public')->group(function () {
    Route::get('/{restaurant}/menu', [PublicMenuController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('public.menu.index');
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/{restaurant}/orders', [PublicOrderController::class, 'store']);
    });
});


Route::middleware('auth:sanctum')->group(function () {

    // Order
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);

    Route::middleware('role:owner,cashier')->group(function () {
        Route::post('orders', [OrderController::class, 'store']);
        Route::put('orders/{id}', [OrderController::class, 'update']);
        Route::put('orders/{id}/final-status', [OrderController::class, 'updateFinalStatus']);
    });

    // kitchen
    Route::put('kitchen/orders/{id}/status', [OrderController::class, 'updateKitchenStatus'])
        ->middleware('role:kitchen');
});

// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // CRUD
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});

// Menu Items
Route::prefix('menu-items')->middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/', [MenuItemController::class, 'index']);
    Route::post('/', [MenuItemController::class, 'store']);
    Route::put('/{menuItem}', [MenuItemController::class, 'update']);
    Route::delete('/{menuItem}', [MenuItemController::class, 'destroy']);
});


require __DIR__ . '/dashboard.php';
