@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Analisis dan laporan data kehadiran karyawan')

@push('styles')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .quick-action-card {
        transition: all 0.3s ease;
    }
    .quick-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .tab-active {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    .performance-meter {
        background: conic-gradient(
            #10b981 0deg,
            #10b981 calc(var(--percentage) * 3.6deg),
            #e5e7eb calc(var(--percentage) * 3.6deg),
            #e5e7eb 360deg
        );
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .performance-meter::before {
        content: '';
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 50%;
        position: absolute;
    }
    .performance-text {
        position: relative;
        z-index: 1;
        font-weight: bold;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan</h1>
            <p class="mt-1 text-sm text-gray-600">
                Analisis dan laporan data kehadiran karyawan
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button type="button" 
                    onclick="exportCurrentReport()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Print
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button onclick="showTab('dashboard')" 
                    class="tab-link py-2 px-1 border-b-2 font-medium text-sm tab-active" 
                    id="tab-dashboard">
                Dashboard
            </button>
            <button onclick="showTab('absensi')" 
                    class="tab-link py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                    id="tab-absensi">
                Laporan Absensi
            </button>
            <button onclick="showTab('izin')" 
                    class="tab-link py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                    id="tab-izin">
                Laporan Izin
            </button>
            <button onclick="showTab('kinerja')" 
                    class="tab-link py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                    id="tab-kinerja">
                Laporan Kinerja
            </button>
        </nav>
    </div>

    <!-- Dashboard Tab -->
    <div id="content-dashboard" class="tab-content active">
        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="stats-card bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summaryStats['total_karyawan'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Absensi Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summaryStats['absensi_bulan_ini'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Izin Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summaryStats['izin_bulan_ini'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rata-rata Kehadiran</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryStats['rata_rata_kehadiran'] ?? 0, 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Trend Chart -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Trend Kehadiran 6 Bulan Terakhir</h3>
                </div>
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <!-- Quick Metrics -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Metrik Cepat</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-800">Kehadiran Tertinggi</p>
                            <p class="text-2xl font-bold text-green-900">{{ number_format($summaryStats['rata_rata_kehadiran'] ?? 0, 1) }}%</p>
                            <p class="text-xs text-green-600">Bulan ini</p>
                        </div>
                        <div class="text-green-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Total Izin</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ $summaryStats['izin_bulan_ini'] ?? 0 }}</p>
                            <p class="text-xs text-yellow-600">Bulan ini</p>
                        </div>
                        <div class="text-yellow-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-800">Total Karyawan</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $summaryStats['total_karyawan'] ?? 0 }}</p>
                            <p class="text-xs text-blue-600">Aktif</p>
                        </div>
                        <div class="text-blue-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Akses Cepat Laporan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <button onclick="showTab('absensi')" 
                        class="quick-action-card block p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200 hover:from-blue-100 hover:to-blue-150 transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-blue-900">Laporan Absensi</h4>
                            <p class="text-sm text-blue-700">Analisis kehadiran karyawan</p>
                        </div>
                    </div>
                </button>

                <button onclick="showTab('izin')" 
                        class="quick-action-card block p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg border border-yellow-200 hover:from-yellow-100 hover:to-yellow-150 transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4h6m-6 0V9a2 2 0 012-2h4a2 2 0 012 2v5"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-yellow-900">Laporan Izin</h4>
                            <p class="text-sm text-yellow-700">Data izin dan cuti karyawan</p>
                        </div>
                    </div>
                </button>

                <button onclick="showTab('kinerja')" 
                        class="quick-action-card block p-6 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200 hover:from-green-100 hover:to-green-150 transition-all duration-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-green-900">Laporan Kinerja</h4>
                            <p class="text-sm text-green-700">Evaluasi performa karyawan</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Laporan Absensi Tab -->
    <div id="content-absensi" class="tab-content">
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- Filter Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <h3 class="text-lg font-medium text-gray-900">Laporan Absensi - Ringkasan per Karyawan</h3>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <input type="month" id="absensi-periode" value="{{ now()->format('Y-m') }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <select id="absensi-karyawan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawan ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <select id="absensi-shift" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Shift</option>
                            @foreach($shifts ?? [] as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->nama }}</option>
                            @endforeach
                        </select>
                        <button onclick="loadAbsensiData()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900" id="total-hari-kerja">{{ $stats['total_hari_kerja'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Hari Kerja</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="total-hadir">{{ $stats['total_hadir'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Hadir</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="total-terlambat">{{ $stats['total_terlambat'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Terlambat</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600" id="total-tidak-hadir">{{ $stats['total_tidak_hadir'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Tidak Hadir</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="total-izin">{{ $stats['total_izin'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Izin</div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Hadir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Terlambat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Tidak Hadir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Izin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody id="absensi-table-body" class="bg-white divide-y divide-gray-200">
                        @foreach($absensi as $emp)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-xs font-medium text-gray-600">{{ substr($emp['karyawan'] ?? 'N/A', 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $emp['karyawan'] ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $emp['id_karyawan'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $emp['shift'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">{{ $emp['total_hadir'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">{{ $emp['total_terlambat'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">{{ $emp['total_tidak_hadir'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ $emp['total_izin'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $emp['tingkat_kehadiran'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $emp['tingkat_kehadiran'] ?? 0 }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination placeholder -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Menampilkan {{ count($absensi) }} karyawan untuk bulan {{ now()->format('F Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Izin Tab -->
    <div id="content-izin" class="tab-content">
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- Filter Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <h3 class="text-lg font-medium text-gray-900">Laporan Izin</h3>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <input type="month" id="izin-periode" value="{{ now()->format('Y-m') }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <select id="izin-karyawan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawan ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <select id="izin-jenis" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Jenis</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti_tahunan">Cuti Tahunan</option>
                            <option value="keperluan_pribadi">Keperluan Pribadi</option>
                            <option value="darurat">Darurat</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <select id="izin-status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua Status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <button onclick="loadIzinData()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900" id="total-pengajuan">{{ $stats['total_pengajuan'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Pengajuan</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="izin-disetujui">{{ $stats['disetujui'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Disetujui</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="izin-menunggu">{{ $stats['menunggu'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Menunggu</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600" id="izin-ditolak">{{ $stats['ditolak'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Ditolak</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="total-hari-izin">{{ $stats['total_hari_izin'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Hari</div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pengajuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Izin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="izin-table-body" class="bg-white divide-y divide-gray-200">
                        @foreach($izin as $leave)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-xs font-medium text-gray-600">{{ substr($leave->user->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $leave->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $leave->user->id_karyawan }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->getJenisIzinText() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->tanggal_mulai->format('d/m/Y') }} - {{ $leave->tanggal_selesai->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $leave->total_hari }} hari</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClass = match($leave->status) {
                                        'menunggu' => 'bg-yellow-100 text-yellow-800',
                                        'disetujui' => 'bg-green-100 text-green-800',
                                        'ditolak' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $leave->getStatusText() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="viewLeaveDetail({{ $leave->id }})" class="text-blue-600 hover:text-blue-900">Detail</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination placeholder -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Menampilkan {{ count($izin) }} pengajuan izin untuk bulan {{ now()->format('F Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Kinerja Tab -->
    <div id="content-kinerja" class="tab-content">
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- Filter Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <h3 class="text-lg font-medium text-gray-900">Laporan Kinerja Karyawan</h3>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <input type="month" id="kinerja-periode" value="{{ now()->format('Y-m') }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <button onclick="loadKinerjaData()" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overall Statistics -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900" id="rata-rata-kehadiran">{{ $overallStats['rata_rata_kehadiran'] ?? 0 }}%</div>
                        <div class="text-sm text-gray-600">Rata-rata Kehadiran</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="karyawan-excellent">{{ $overallStats['karyawan_excellent'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Excellent</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="karyawan-good">{{ $overallStats['karyawan_good'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Good</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="karyawan-average">{{ $overallStats['karyawan_average'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Average</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600" id="karyawan-poor">{{ $overallStats['karyawan_poor'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Poor</div>
                    </div>
                </div>
            </div>

            <!-- Performance Cards -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="kinerja-cards">
                    @foreach($karyawanKinerja as $emp)
                    <div class="bg-white border rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-sm font-medium text-gray-600">{{ substr($emp->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $emp->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $emp->id_karyawan }}</p>
                                </div>
                            </div>
                            <div class="performance-meter" style="--percentage: {{ $emp->tingkat_kehadiran }}">
                                <div class="performance-text">{{ $emp->tingkat_kehadiran }}%</div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Hadir:</span>
                                <span class="font-medium">{{ $emp->total_hadir }} hari</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Terlambat:</span>
                                <span class="font-medium">{{ $emp->total_terlambat }} hari</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tidak Hadir:</span>
                                <span class="font-medium">{{ $emp->total_tidak_hadir }} hari</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                @php
                                    $ratingClass = match($emp->rating) {
                                        'Excellent' => 'bg-green-100 text-green-800',
                                        'Good' => 'bg-blue-100 text-blue-800', 
                                        'Average' => 'bg-yellow-100 text-yellow-800',
                                        'Poor' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingClass }}">
                                    {{ $emp->rating }}
                                </span>
                                <button onclick="viewEmployeeDetail({{ $emp->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                    Detail
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal PDF - UPDATED -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🖨️ Export Laporan</h3>
            <form id="exportForm" action="{{ route('admin.laporan.export') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan</label>
                        <select name="jenis_laporan" id="export-jenis" required class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="toggleUserSelect()">
                            <option value="semua">📊 Laporan Gabungan (Absensi + Izin)</option>
                            <option value="absensi_only">✅ Laporan Absensi Saja</option>
                            <option value="izin_only">📋 Laporan Izin Saja</option>
                            <option value="individual">👤 Laporan Individual</option>
                        </select>
                    </div>
                    
                    <!-- User Selection - Only for Individual Report -->
                    <div id="user-selection" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Karyawan</label>
                        <select name="user_id" id="export-user" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawan ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->id_karyawan }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <input type="month" name="periode" id="export-periode" required value="{{ now()->format('Y-m') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    
                    <!-- Fixed Format -->
                    <input type="hidden" name="format" value="pdf">
                    
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <p class="text-sm text-blue-700">
                            <strong>🖨️ Format:</strong> HTML Print<br>
                            <strong>📊 Laporan Gabungan:</strong> Absensi + Izin dalam satu dokumen<br>
                            <strong>✅ Laporan Absensi:</strong> Hanya data kehadiran karyawan<br>
                            <strong>📋 Laporan Izin:</strong> Hanya data izin & cuti karyawan<br>
                            <strong>👤 Laporan Individual:</strong> Detail lengkap satu karyawan
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExportModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                        🖨️ Export Print
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let currentTab = 'dashboard';
let attendanceChart = null;

// Tab management
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab links
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('tab-active');
        link.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected tab
    document.getElementById(`content-${tabName}`).classList.add('active');
    
    // Add active class to selected tab link
    const activeTab = document.getElementById(`tab-${tabName}`);
    activeTab.classList.add('tab-active');
    activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    
    currentTab = tabName;
}

// Chart initialization
function initChart() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;
    
    const chartData = @json($chartData ?? []);
    
    if (attendanceChart) {
        attendanceChart.destroy();
    }
    
    attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.month),
            datasets: [{
                label: 'Kehadiran',
                data: chartData.map(item => item.absensi),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }, {
                label: 'Izin',
                data: chartData.map(item => item.izin),
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Load data functions
function loadAbsensiData() {
    const periode = document.getElementById('absensi-periode').value;
    const userId = document.getElementById('absensi-karyawan').value;
    const shiftId = document.getElementById('absensi-shift').value;
    
    // Show loading
    document.getElementById('absensi-table-body').innerHTML = `
        <tr><td colspan="7" class="text-center py-8">Memuat data...</td></tr>
    `;
    
    const params = new URLSearchParams({
        bulan: periode
    });
    
    if (userId) params.append('user_id', userId);
    if (shiftId) params.append('shift_id', shiftId);
    
    fetch(`{{ route('admin.laporan.absensi') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateAbsensiStats(data.stats);
            updateAbsensiTable(data.absensi);
        })
        .catch(error => {
            console.error('Error loading absensi data:', error);
            document.getElementById('absensi-table-body').innerHTML = `
                <tr><td colspan="7" class="text-center py-8 text-red-600">Gagal memuat data</td></tr>
            `;
        });
}

function loadIzinData() {
    const periode = document.getElementById('izin-periode').value;
    const userId = document.getElementById('izin-karyawan').value;
    const jenisIzin = document.getElementById('izin-jenis').value;
    const status = document.getElementById('izin-status').value;
    
    // Show loading
    document.getElementById('izin-table-body').innerHTML = `
        <tr><td colspan="7" class="text-center py-8">Memuat data...</td></tr>
    `;
    
    const params = new URLSearchParams({
        bulan: periode
    });
    
    if (userId) params.append('user_id', userId);
    if (jenisIzin) params.append('jenis_izin', jenisIzin);
    if (status) params.append('status', status);
    
    fetch(`{{ route('admin.laporan.izin') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateIzinStats(data.stats);
            updateIzinTable(data.izin);
        })
        .catch(error => {
            console.error('Error loading izin data:', error);
            document.getElementById('izin-table-body').innerHTML = `
                <tr><td colspan="7" class="text-center py-8 text-red-600">Gagal memuat data</td></tr>
            `;
        });
}

function loadKinerjaData() {
    const periode = document.getElementById('kinerja-periode').value;
    
    // Show loading
    document.getElementById('kinerja-cards').innerHTML = `
        <div class="col-span-full text-center py-8">Memuat data...</div>
    `;
    
    const params = new URLSearchParams({
        bulan: periode
    });
    
    fetch(`{{ route('admin.laporan.kinerja') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateKinerjaStats(data.overallStats);
            updateKinerjaCards(data.karyawan);
        })
        .catch(error => {
            console.error('Error loading kinerja data:', error);
            document.getElementById('kinerja-cards').innerHTML = `
                <div class="col-span-full text-center py-8 text-red-600">Gagal memuat data</div>
            `;
        });
}

// Update functions
function updateAbsensiStats(stats) {
    document.getElementById('total-hari-kerja').textContent = stats.total_hari_kerja;
    document.getElementById('total-hadir').textContent = stats.total_hadir;
    document.getElementById('total-terlambat').textContent = stats.total_terlambat;
    document.getElementById('total-tidak-hadir').textContent = stats.total_tidak_hadir;
    document.getElementById('total-izin').textContent = stats.total_izin;
}

function updateAbsensiTable(data) {
    const tbody = document.getElementById('absensi-table-body');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="7" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map(emp => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                        <span class="text-xs font-medium text-gray-600">${emp.karyawan.substring(0, 2)}</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">${emp.karyawan}</div>
                        <div class="text-sm text-gray-500">${emp.id_karyawan}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${emp.shift}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">${emp.total_hadir}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">${emp.total_terlambat}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">${emp.total_tidak_hadir}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">${emp.total_izin}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: ${emp.tingkat_kehadiran}%"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-900">${emp.tingkat_kehadiran}%</span>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateIzinStats(stats) {
    document.getElementById('total-pengajuan').textContent = stats.total_pengajuan;
    document.getElementById('izin-disetujui').textContent = stats.disetujui;
    document.getElementById('izin-menunggu').textContent = stats.menunggu;
    document.getElementById('izin-ditolak').textContent = stats.ditolak;
    document.getElementById('total-hari-izin').textContent = stats.total_hari_izin;
}

function updateIzinTable(data) {
    const tbody = document.getElementById('izin-table-body');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="7" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.tanggal_pengajuan}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                        <span class="text-xs font-medium text-gray-600">${item.karyawan.substring(0, 2)}</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">${item.karyawan}</div>
                        <div class="text-sm text-gray-500">${item.id_karyawan}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.jenis_izin}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.tanggal_mulai} - ${item.tanggal_selesai}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.total_hari} hari</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.status_badge_class}">
                    ${item.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button onclick="viewLeaveDetail(${item.id})" class="text-blue-600 hover:text-blue-900">Detail</button>
            </td>
        </tr>
    `).join('');
}

function updateKinerjaStats(stats) {
    document.getElementById('rata-rata-kehadiran').textContent = stats.rata_rata_kehadiran + '%';
    document.getElementById('karyawan-excellent').textContent = stats.karyawan_excellent;
    document.getElementById('karyawan-good').textContent = stats.karyawan_good;
    document.getElementById('karyawan-average').textContent = stats.karyawan_average;
    document.getElementById('karyawan-poor').textContent = stats.karyawan_poor;
}

function updateKinerjaCards(data) {
    const container = document.getElementById('kinerja-cards');
    
    if (!data || data.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-500">Tidak ada data</div>
        `;
        return;
    }
    
    container.innerHTML = data.map(employee => {
        const ratingClass = {
            'Excellent': 'bg-green-100 text-green-800',
            'Good': 'bg-blue-100 text-blue-800',
            'Average': 'bg-yellow-100 text-yellow-800',
            'Poor': 'bg-red-100 text-red-800'
        }[employee.rating] || 'bg-gray-100 text-gray-800';
        
        return `
            <div class="bg-white border rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                            <span class="text-sm font-medium text-gray-600">${employee.name.substring(0, 2)}</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">${employee.name}</h4>
                            <p class="text-xs text-gray-500">${employee.id_karyawan}</p>
                        </div>
                    </div>
                    <div class="performance-meter" style="--percentage: ${employee.tingkat_kehadiran}">
                        <div class="performance-text">${employee.tingkat_kehadiran}%</div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Hadir:</span>
                        <span class="font-medium">${employee.total_hadir} hari</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Terlambat:</span>
                        <span class="font-medium">${employee.total_terlambat} hari</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tidak Hadir:</span>
                        <span class="font-medium">${employee.total_tidak_hadir} hari</span>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${ratingClass}">
                            ${employee.rating}
                        </span>
                        <button onclick="viewEmployeeDetail(${employee.id})" class="text-sm text-blue-600 hover:text-blue-800">
                            Detail
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Export Modal Functions
function toggleUserSelect() {
    const jenisLaporan = document.getElementById('export-jenis').value;
    const userSelection = document.getElementById('user-selection');
    const userSelect = document.getElementById('export-user');
    
    if (jenisLaporan === 'individual') {
        userSelection.style.display = 'block';
        userSelect.required = true;
    } else {
        userSelection.style.display = 'none';
        userSelect.required = false;
        userSelect.value = '';
    }
}

function exportCurrentReport() {
    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

// Detail view functions - placeholder
function viewLeaveDetail(id) {
    console.log('View leave detail:', id);
    // Implement detail modal if needed
}

function viewEmployeeDetail(id) {
    console.log('View employee detail:', id);
    // Implement detail modal if needed
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});

// Close modals when clicking outside
window.onclick = function(event) {
    const exportModal = document.getElementById('exportModal');
    if (exportModal && event.target === exportModal) {
        exportModal.classList.add('hidden');
    }
}
</script>
@endpush