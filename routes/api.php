<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Limit to 5 attempts per minute

require __DIR__.'/dashboard.php';
