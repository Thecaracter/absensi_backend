<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiLeaveController;
use App\Http\Controllers\Api\ApiProfileController;

Route::prefix('v1')->group(function () {

    // Public routes
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

    // Protected routes (require authentication)
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
            Route::patch('/data', [ApiProfileController::class, 'updateData']);
            Route::post('/photo', [ApiProfileController::class, 'updatePhoto']);
        });

        // Attendance routes
        Route::prefix('attendance')->group(function () {
            Route::get('/today', [ApiAttendanceController::class, 'todayAttendance']);
            Route::post('/check-in', [ApiAttendanceController::class, 'checkIn']);
            Route::post('/check-out', [ApiAttendanceController::class, 'checkOut']);
        });

        // Leave Request routes - COMPLETE CRUD + Stats
        Route::prefix('leave-requests')->group(function () {

            Route::get('/', [ApiLeaveController::class, 'index']);

            // POST /api/v1/leave-requests - Create new leave request
            Route::post('/', [ApiLeaveController::class, 'store']);

            // GET /api/v1/leave-requests/stats - Get leave statistics
            Route::get('/stats', [ApiLeaveController::class, 'stats']);

            // GET /api/v1/leave-requests/{id} - Get specific leave request
            Route::get('/{id}', [ApiLeaveController::class, 'show']);

            // PUT/PATCH /api/v1/leave-requests/{id} - Update leave request
            Route::put('/{id}', [ApiLeaveController::class, 'update']);
            Route::patch('/{id}', [ApiLeaveController::class, 'update']);

            // DELETE /api/v1/leave-requests/{id} - Delete leave request
            Route::delete('/{id}', [ApiLeaveController::class, 'destroy']);
        });
    });
});