<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CafeController;
use Illuminate\Support\Facades\Route;

// Satu-satunya endpoint publik
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Semua endpoint lain wajib token (header: Authorization: Bearer <token>)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('cafes', CafeController::class);
});