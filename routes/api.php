<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Limit to 5 attempts per minute

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
