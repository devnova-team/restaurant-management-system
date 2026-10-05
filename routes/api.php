<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Public\PublicMenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // Limit to 5 attempts per minute

Route::prefix('public')->group(function () {
    Route::get('/{restaurant}/menu', [PublicMenuController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('public.menu.index');
});

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
});

Route::prefix('invoices')->group(function () {
    Route::post('/', [InvoiceController::class, 'store']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::patch('/{id}/payment-status', [InvoiceController::class, 'updatePaymentStatus']);
    Route::post('/{id}/pay', [PaymentGatewayController::class, 'pay']);
});

Route::prefix('payments')->group(function () {
    Route::get('/{gateway}/success', [PaymentGatewayController::class, 'handleSuccess'])->name('payments.success');
    Route::get('/{gateway}/cancel', [PaymentGatewayController::class, 'handleCancel'])->name('payments.cancel');
    Route::get('/{gateway}/callback', [PaymentGatewayController::class, 'handleSuccess'])->name('payments.callback');
    Route::post('/{gateway}/webhook', [PaymentGatewayController::class, 'handleWebhook'])->name('payments.webhook');
});

// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // CRUD
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});
