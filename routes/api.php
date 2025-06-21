<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiLeaveController;
use App\Http\Controllers\Api\ApiProfileController;


Route::prefix('v1')->group(function () {


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


    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::get('/me', [ApiAuthController::class, 'me']);
        });


        Route::prefix('profile')->group(function () {
            Route::get('/', [ApiProfileController::class, 'show']);
            Route::put('/', [ApiProfileController::class, 'update']);
            Route::patch('/data', [ApiProfileController::class, 'updateData']);
            Route::post('/photo', [ApiProfileController::class, 'updatePhoto']);
        });


        Route::prefix('attendance')->group(function () {
            Route::get('/today', [ApiAttendanceController::class, 'todayAttendance']);
            Route::post('/check-in', [ApiAttendanceController::class, 'checkIn']);
            Route::post('/check-out', [ApiAttendanceController::class, 'checkOut']);
        });


        Route::prefix('leave-requests')->group(function () {
            Route::get('/', [ApiLeaveController::class, 'index']);
            Route::post('/', [ApiLeaveController::class, 'store']);
        });
    });
});