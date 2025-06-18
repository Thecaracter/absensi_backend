<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\LaporanController;

/*
|--------------------------------------------------------------------------
| Web Routes - FIXED HTTP METHODS untuk Izin Management
|--------------------------------------------------------------------------
*/

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Karyawan Management
    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/', [KaryawanController::class, 'index'])->name('index');
        Route::post('/', [KaryawanController::class, 'store'])->name('store');

        // Generate ID endpoint - harus sebelum route parameter
        Route::get('/generate-id', [KaryawanController::class, 'generateIdKaryawan'])->name('generate-id');

        // Export & Bulk Actions
        Route::post('/export', [KaryawanController::class, 'export'])->name('export');
        Route::post('/bulk-action', [KaryawanController::class, 'bulkAction'])->name('bulk-action');

        // Employee specific routes (parameter routes di akhir)
        Route::put('/{karyawan}', [KaryawanController::class, 'update'])->name('update');
        Route::delete('/{karyawan}', [KaryawanController::class, 'destroy'])->name('destroy');
    });

    // Absensi Management
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');

        // JSON endpoint
        Route::get('/{attendance}/json', [AbsensiController::class, 'getAttendanceJson'])->name('json');

        Route::post('/', [AbsensiController::class, 'store'])->name('store');

        // Approval actions
        Route::post('/{attendance}/approve-masuk', [AbsensiController::class, 'approveMasuk'])->name('approve-masuk');
        Route::post('/{attendance}/reject-masuk', [AbsensiController::class, 'rejectMasuk'])->name('reject-masuk');
        Route::post('/{attendance}/approve-keluar', [AbsensiController::class, 'approveKeluar'])->name('approve-keluar');
        Route::post('/{attendance}/reject-keluar', [AbsensiController::class, 'rejectKeluar'])->name('reject-keluar');
        Route::post('/{attendance}/update-status', [AbsensiController::class, 'updateStatus'])->name('update-status');

        // Bulk actions dan export
        Route::post('/bulk-action', [AbsensiController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/export', [AbsensiController::class, 'export'])->name('export');
        Route::post('/recalculate-late', [AbsensiController::class, 'recalculateLateStatus'])->name('recalculate-late');

        // Route parameter biasa di akhir
        Route::get('/{attendance}', [AbsensiController::class, 'show'])->name('show');
        Route::put('/{attendance}', [AbsensiController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AbsensiController::class, 'destroy'])->name('destroy');
    });

    // Izin/Leave Management - FIXED dengan PATCH method untuk approve/reject/cancel
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');

        // JSON endpoints untuk AJAX - harus sebelum route parameter biasa
        Route::get('/{leaveRequest}/json', [IzinController::class, 'getLeaveRequestJson'])->name('json');

        // CRUD operations
        Route::post('/', [IzinController::class, 'store'])->name('store');
        Route::put('/{leaveRequest}', [IzinController::class, 'update'])->name('update');
        Route::delete('/{leaveRequest}', [IzinController::class, 'destroy'])->name('destroy');

        // FIXED: Approval operations - UBAH JADI PATCH untuk konsistensi dengan modal form
        Route::patch('/{leaveRequest}/approve', [IzinController::class, 'approve'])->name('approve');
        Route::patch('/{leaveRequest}/reject', [IzinController::class, 'reject'])->name('reject');
        Route::patch('/{leaveRequest}/cancel', [IzinController::class, 'cancel'])->name('cancel');

        // Bulk operations dengan otomatis jadwal
        Route::post('/bulk-action', [IzinController::class, 'bulkAction'])->name('bulk-action');

        // Export with enhanced filtering
        Route::post('/export', [IzinController::class, 'export'])->name('export');

        // Detail page (jika diperlukan) - di akhir
        Route::get('/{leaveRequest}', [IzinController::class, 'show'])->name('show');
    });

    // Jadwal & Shift Management (Monthly Only)
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/', [JadwalController::class, 'index'])->name('index');

        // Schedule management
        Route::post('/create', [JadwalController::class, 'createSchedule'])->name('create');
        Route::post('/{attendance}/update', [JadwalController::class, 'updateSchedule'])->name('update');
        Route::delete('/{attendance}', [JadwalController::class, 'deleteSchedule'])->name('delete');
        Route::post('/export', [JadwalController::class, 'exportJadwal'])->name('export');

        // Auto Generate Routes
        Route::post('/auto-generate', [JadwalController::class, 'autoGenerateMonthlySchedule'])->name('auto-generate');
        Route::post('/clear-schedule', [JadwalController::class, 'clearMonthlySchedule'])->name('clear-schedule');
        Route::post('/regenerate', [JadwalController::class, 'regenerateRandomSchedule'])->name('regenerate');
        Route::get('/generation-stats', [JadwalController::class, 'getGenerationStats'])->name('generation-stats');

        // Detail Schedule Modal Route
        Route::get('/date-detail', [JadwalController::class, 'getDateScheduleDetail'])->name('date-detail');

        // Fix Status Route
        Route::post('/fix-status', [JadwalController::class, 'updatePendingStatus'])->name('fix-status');

        // Shift Management
        Route::prefix('shift')->name('shift.')->group(function () {
            Route::get('/', [JadwalController::class, 'indexShift'])->name('index');
            Route::post('/', [JadwalController::class, 'storeShift'])->name('store');
            Route::put('/{shift}', [JadwalController::class, 'updateShift'])->name('update');
            Route::delete('/{shift}', [JadwalController::class, 'destroyShift'])->name('destroy');
        });
    });

    // Laporan Management
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/absensi', [LaporanController::class, 'absensi'])->name('absensi');
        Route::get('/izin', [LaporanController::class, 'izin'])->name('izin');
        Route::get('/kinerja', [LaporanController::class, 'kinerja'])->name('kinerja');

        // Export routes
        Route::post('/absensi/export', [LaporanController::class, 'exportAbsensi'])->name('absensi.export');
        Route::post('/izin/export', [LaporanController::class, 'exportIzin'])->name('izin.export');
        Route::post('/kinerja/export', [LaporanController::class, 'exportKinerja'])->name('kinerja.export');
    });

    // Settings & Configuration (jika diperlukan)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminController::class, 'settings'])->name('index');
        Route::post('/update', [AdminController::class, 'updateSettings'])->name('update');
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminController::class, 'profile'])->name('index');
        Route::put('/update', [AdminController::class, 'updateProfile'])->name('update');
        Route::put('/password', [AdminController::class, 'updatePassword'])->name('password');
    });
});

// User/Employee Routes (untuk karyawan biasa jika diperlukan)
Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    // Dashboard karyawan
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Absensi karyawan
    Route::get('/absensi', [UserController::class, 'absensi'])->name('absensi');
    Route::post('/absensi/masuk', [UserController::class, 'absensiMasuk'])->name('absensi.masuk');
    Route::post('/absensi/keluar', [UserController::class, 'absensiKeluar'])->name('absensi.keluar');

    // Izin karyawan
    Route::get('/izin', [UserController::class, 'izin'])->name('izin');
    Route::post('/izin', [UserController::class, 'storeIzin'])->name('izin.store');

    // Profile karyawan
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
});



// Error Pages (Custom error handling)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});


