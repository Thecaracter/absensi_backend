<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiLeaveController;
use App\Http\Controllers\Api\ApiProfileController;

// API Version 1
Route::prefix('v1')->group(function () {

    // PUBLIC ROUTES
    Route::prefix('auth')->group(function () {
        Route::post('/login', [ApiAuthController::class, 'login']);
    });

    Route::get('/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is working!',
            'version' => 'v1',
            'timestamp' => now()->toISOString()
        ]);
    });

    // PROTECTED ROUTES
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::get('/me', [ApiAuthController::class, 'me']);
        });

        // Profile routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [ApiProfileController::class, 'show']);
            Route::put('/', [ApiProfileController::class, 'update']);
        });

        // Attendance routes
        Route::prefix('attendance')->group(function () {
            Route::get('/today', [ApiAttendanceController::class, 'todayAttendance']);
            Route::post('/check-in', [ApiAttendanceController::class, 'checkIn']);
            Route::post('/check-out', [ApiAttendanceController::class, 'checkOut']);
        });

        // Leave requests
        Route::prefix('leave-requests')->group(function () {
            Route::get('/', [ApiLeaveController::class, 'index']);
            Route::post('/', [ApiLeaveController::class, 'store']);
        });
    });
});