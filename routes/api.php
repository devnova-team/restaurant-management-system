<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
