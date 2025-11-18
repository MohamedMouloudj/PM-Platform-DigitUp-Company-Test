<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // Rate limit: 5 attempts per minute

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});
