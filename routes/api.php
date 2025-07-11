<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentLogsController;
use App\Http\Controllers\DocumentSignerController;
use App\Http\Controllers\SignerDocumentFieldController;
use App\Http\Controllers\SignController;
use App\Http\Controllers\SignerDocumentFieldValueController;
use App\Http\Controllers\TemplateController;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

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
    Route::post('templates/{template}/create-document', [TemplateController::class, 'copyToDocument']);
    Route::apiResource('document-signers', DocumentSignerController::class);
    Route::post('document-signers/{documentSigner}/complete-signature', [DocumentSignerController::class, 'completeSignature']);
    Route::apiResource('signer-document-fields', SignerDocumentFieldController::class);
    Route::apiResource('signer-document-field-values', SignerDocumentFieldValueController::class);
    Route::apiResource('document-logs', DocumentLogsController::class)->only(['index', 'show']);
    Route::apiResource('signs', SignController::class);
    Route::prefix('documents')->group(function () {
        Route::get('{document}/signers', [DocumentController::class, 'signers']);
        Route::get('{document}/fields', [DocumentController::class, 'fields']);
        Route::get('{document}/progress', [DocumentController::class, 'getProgress']);
    });
    
    Route::prefix('contacts')->group(function () {
        Route::get('my-contacts', [ContactController::class, 'myContacts']);
        Route::get('contacts-of', [ContactController::class, 'contactsOf']);
    });
    
    Route::prefix('signs')->group(function () {
        Route::get('my-signs', [SignController::class, 'mySigns']);
        Route::post('{sign}/duplicate', [SignController::class, 'duplicate']);
    });
});

