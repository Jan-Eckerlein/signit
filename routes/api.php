<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    // Registration and Login
    Route::post('/register', [AuthController::class, 'register']); // Register a new user
    Route::post('/login', [AuthController::class, 'login']);       // Login user

    // Authenticated user actions
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);         // Logout user
        Route::get('/me', [AuthController::class, 'me']);                  // Get authenticated user
        Route::put('/profile', [AuthController::class, 'updateProfile']);  // Update user profile
        Route::post('/refresh', [AuthController::class, 'refresh']);       // Refresh session
    });
});

