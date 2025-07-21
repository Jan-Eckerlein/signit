<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentLogsController;
use App\Http\Controllers\DocumentSignerController;
use App\Http\Controllers\DocumentFieldController;
use App\Http\Controllers\SignController;
use App\Http\Controllers\DocumentFieldValueController;
use App\Http\Controllers\PdfProcessUploadController;

Route::prefix('auth')->group(function () {
    // Registration and Login
    Route::post('/register', [AuthController::class, 'register']); // Register a new user
    Route::post('/login', [AuthController::class, 'login']);       // Login user

    // Authenticated user actions
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);         // Logout user
        Route::get('/me', [AuthController::class, 'me']);                  // Get authenticated user
        Route::put('/profile', [AuthController::class, 'updateProfile']);  // Update user profile
        Route::post('/refresh', [AuthController::class, 'refresh']);       // Refresh session
    });
});

// Protected API routes - all require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('document-signers', DocumentSignerController::class);
    Route::post('document-signers/{documentSigner}/complete-signature', [DocumentSignerController::class, 'completeSignature']);
    Route::apiResource('document-fields', DocumentFieldController::class);
    Route::apiResource('document-field-values', DocumentFieldValueController::class)->only(['store']);
    Route::apiResource('document-logs', DocumentLogsController::class)->only(['index', 'show']);
    Route::apiResource('signs', SignController::class);
    Route::delete('signs/{sign}/force', [SignController::class, 'forceDelete']);
    Route::post('signs/{sign}/restore', [SignController::class, 'restore']);
    Route::prefix('documents')->group(function () {
        Route::get('{document}/progress', [DocumentController::class, 'getProgress']);
        Route::post('{document}/open-for-signing', [DocumentController::class, 'openForSigning']);
        Route::post('{document}/revert-to-draft', [DocumentController::class, 'revertToDraft']);
    });
    Route::apiResource('pdf-process-uploads', PdfProcessUploadController::class)->only(['store']);
});

