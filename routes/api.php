<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Public\PublicMenuController;
use App\Http\Controllers\Api\IngredientController;
use Illuminate\Support\Facades\Route;

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

// Owner
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // CRUD
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});

// Ingredient
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    // CRUD
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
});