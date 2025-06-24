<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiLeaveController;
use App\Http\Controllers\Api\ApiProfileController;
use App\Http\Controllers\Api\ApiDashboardController;

Route::prefix('v1')->group(function () {

    // Auth routes (public)
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

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::get('/me', [ApiAuthController::class, 'me']);
        });

        // Dashboard - BARU!
        Route::prefix('dashboard')->group(function () {
            Route::get('/home', [ApiDashboardController::class, 'home']);
            Route::get('/summary', [ApiDashboardController::class, 'summary']);
            Route::get('/quick-actions', [ApiDashboardController::class, 'quickActions']);
        });

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ApiProfileController::class, 'show']);
            Route::put('/', [ApiProfileController::class, 'update']);
            Route::patch('/data', [ApiProfileController::class, 'updateData']);
            Route::post('/photo', [ApiProfileController::class, 'updatePhoto']);
        });

        // Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/today', [ApiAttendanceController::class, 'todayAttendance']);
            Route::post('/check-in', [ApiAttendanceController::class, 'checkIn']);
            Route::post('/check-out', [ApiAttendanceController::class, 'checkOut']);
            Route::get('/history', [ApiAttendanceController::class, 'history']);
            Route::get('/monthly-stats', [ApiAttendanceController::class, 'monthlyStats']);
        });

        // Leave Requests
        Route::prefix('leave-requests')->group(function () {
            Route::get('/', [ApiLeaveController::class, 'index']);
            Route::post('/', [ApiLeaveController::class, 'store']);
            Route::get('/stats', [ApiLeaveController::class, 'stats']);
            Route::get('/{id}', [ApiLeaveController::class, 'show']);
            Route::put('/{id}', [ApiLeaveController::class, 'update']);
            Route::delete('/{id}', [ApiLeaveController::class, 'destroy']);
        });
    });
});