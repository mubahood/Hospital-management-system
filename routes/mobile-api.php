<?php

use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| These routes are specifically designed for mobile application access
| with enterprise-aware functionality and modern API standards.
|
*/

// Authentication routes (no middleware required)
Route::prefix('mobile')->group(function () {
    Route::post('login', [MobileController::class, 'login']);
    Route::post('register', [MobileController::class, 'register']);

    // Protected routes (require authentication)
    Route::middleware('auth:api')->group(function () {
        // User profile
        Route::get('me', [MobileController::class, 'me']);
        Route::post('update-profile', [MobileController::class, 'updateProfile']);
        Route::post('change-password', [MobileController::class, 'changePassword']);

        // Consultations
        Route::get('consultations', [MobileController::class, 'consultations']);
        Route::post('consultations', [MobileController::class, 'createConsultation']);

        // Medical services
        Route::get('medical-services', [MobileController::class, 'medicalServices']);

        // Services
        Route::get('services', [MobileController::class, 'services']);

        // Payment records
        Route::get('payment-records', [MobileController::class, 'paymentRecords']);
    });
});
