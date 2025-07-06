<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\IzinController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\LocationController;


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

    // SIMPLE: Absensi Management - NO API ENDPOINTS, STANDARD LARAVEL ONLY
    Route::prefix('absensi')->name('absensi.')->group(function () {
        // Main index page dengan pagination Laravel standard
        Route::get('/', [AbsensiController::class, 'index'])->name('index');

        // CRUD operations - semua pakai redirect
        Route::post('/', [AbsensiController::class, 'store'])->name('store');

        // JSON endpoint HANYA untuk modal detail (UI only, bukan data)
        Route::get('/{attendance}/json', [AbsensiController::class, 'getAttendanceJson'])->name('json');

        // Approval operations - semua pakai REDIRECT BACK
        Route::post('/{attendance}/approve-masuk', [AbsensiController::class, 'approveMasuk'])->name('approve-masuk');
        Route::post('/{attendance}/reject-masuk', [AbsensiController::class, 'rejectMasuk'])->name('reject-masuk');
        Route::post('/{attendance}/approve-keluar', [AbsensiController::class, 'approveKeluar'])->name('approve-keluar');
        Route::post('/{attendance}/reject-keluar', [AbsensiController::class, 'rejectKeluar'])->name('reject-keluar');
        Route::post('/{attendance}/update-status', [AbsensiController::class, 'updateStatus'])->name('update-status');

        // Bulk operations - pakai REDIRECT BACK
        Route::post('/bulk-action', [AbsensiController::class, 'bulkAction'])->name('bulk-action');

        // Recalculate - pakai REDIRECT BACK (bukan AJAX)
        Route::post('/recalculate-late', [AbsensiController::class, 'recalculateLateStatus'])->name('recalculate-late');

        // Export
        Route::post('/export', [AbsensiController::class, 'export'])->name('export');
    });

    // Izin/Leave Management - FIXED dengan PATCH method untuk approve/reject/cancel
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');

        // JSON endpoints untuk AJAX - hanya untuk modal UI
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

        // HANYA SATU VIEW UTAMA
        Route::get('/', [LaporanController::class, 'index'])->name('index');

        // AJAX routes untuk data tabs (return JSON only)
        Route::get('/absensi', [LaporanController::class, 'absensi'])->name('absensi');
        Route::get('/izin', [LaporanController::class, 'izin'])->name('izin');
        Route::get('/kinerja', [LaporanController::class, 'kinerja'])->name('kinerja');

        // AJAX Detail routes untuk modal (return JSON)
        Route::get('/absensi/detail', [LaporanController::class, 'getAbsensiDetail'])->name('absensi.detail');
        Route::get('/izin/detail', [LaporanController::class, 'getIzinDetail'])->name('izin.detail');
        Route::get('/kinerja/detail', [LaporanController::class, 'getKinerjaDetail'])->name('kinerja.detail');

        // Dashboard stats untuk AJAX
        Route::get('/dashboard/stats', [LaporanController::class, 'getDashboardStats'])->name('dashboard.stats');

        // MAIN export method (digunakan di view)
        Route::post('/export', [LaporanController::class, 'export'])->name('export');

        // Individual export routes (backup/alternative)
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

    Route::prefix('location')->name('location.')->group(function () {
        // Main page
        Route::get('/', [LocationController::class, 'index'])->name('index');

        // Office Location CRUD
        Route::post('/store', [LocationController::class, 'store'])->name('store');
        Route::put('/{location}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');

        // JSON endpoint untuk modal edit
        Route::get('/{location}/json', [LocationController::class, 'getLocationJson'])->name('json');

        // Bulk actions
        Route::post('/bulk-action', [LocationController::class, 'bulkAction'])->name('bulk-action');

        // Settings update
        Route::post('/settings', [LocationController::class, 'updateSettings'])->name('settings.update');

        // Import/Export
        Route::post('/import', [LocationController::class, 'import'])->name('import');
        Route::post('/export', [LocationController::class, 'export'])->name('export');

        // Test configuration (untuk debugging)
        Route::get('/test', [LocationController::class, 'testConfiguration'])->name('test');
    });
});

// Karyawan Routes (jika diperlukan nanti)
Route::middleware(['auth', 'karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/dashboard', function () {
        return view('karyawan.dashboard');
    })->name('dashboard');
});

// Error Pages (Custom error handling)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});