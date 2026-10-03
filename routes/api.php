<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Limit to 5 attempts per minute


// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function(){
    //CRUD
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

});
