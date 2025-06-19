@extends('layouts.app')

@section('title', 'Data Absensi')
@section('page-title', 'Data Absensi')
@section('page-subtitle', 'Kelola data absensi karyawan untuk tanggal ' . \Carbon\Carbon::parse($tanggal)->format('d F Y'))

@section('content')
<div class="space-y-6">
    <!-- Header Info -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-sm text-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Data Absensi</h2>
                <p class="text-blue-100 mt-1">{{ \Carbon\Carbon::parse($tanggal)->format('l, d F Y') }}</p>
                @if($tanggal === today()->format('Y-m-d'))
                    <span class="inline-flex items-center px-2 py-1 text-xs bg-green-500 bg-opacity-20 text-green-100 rounded-full mt-2">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Hari Ini
                    </span>
                @elseif(\Carbon\Carbon::parse($tanggal)->isFuture())
                    <span class="inline-flex items-center px-2 py-1 text-xs bg-yellow-500 bg-opacity-20 text-yellow-100 rounded-full mt-2">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Masa Depan
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-500 bg-opacity-20 text-blue-100 rounded-full mt-2">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-8a1 1 0 112 0v4a1 1 0 11-2 0v-4zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                        </svg>
                        Riwayat
                    </span>
                @endif
            </div>
            <div class="text-right space-x-3">
                <button onclick="jumpToToday()" class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                    </svg>
                    Hari Ini
                </button>
                <button onclick="recalculateLateStatus()" 
                        class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Recalculate Late Status
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Absensi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statsToday['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $statsToday['hadir'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $statsToday['terlambat'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tidak Hadir</p>
                    <p class="text-2xl font-bold text-red-600">{{ $statsToday['tidak_hadir'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Izin</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $statsToday['izin'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4M8 7h8M8 7V21m8-14v14"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Menunggu Approval</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $statsToday['menunggu_approval'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Filter Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                        </svg>
                        Tanggal Absensi
                    </label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           onchange="this.form.submit()">
                </div>

                <!-- Filter Shift -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shift</label>
                    <select name="shift_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Shift</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->nama }} ({{ \Carbon\Carbon::parse($shift->jam_masuk)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->jam_keluar)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Karyawan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                    <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawan as $k)
                            <option value="{{ $k->id }}" {{ request('user_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->name }} ({{ $k->id_karyawan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Absen</label>
                    <select name="status_absen" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ request('status_absen') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ request('status_absen') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="tidak_hadir" {{ request('status_absen') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                        <option value="izin" {{ request('status_absen') == 'izin' ? 'selected' : '' }}>Izin</option>
                    </select>
                </div>

                <!-- Filter Approval -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Approval</label>
                    <select name="status_approval" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="menunggu_masuk" {{ request('status_approval') == 'menunggu_masuk' ? 'selected' : '' }}>Menunggu Masuk</option>
                        <option value="menunggu_keluar" {{ request('status_approval') == 'menunggu_keluar' ? 'selected' : '' }}>Menunggu Keluar</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.absensi.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                <button type="button" onclick="openExportModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
                <button type="button" onclick="openBulkActionModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Bulk Action
                </button>
            </div>
        </form>
    </div>

    <!-- Alert Info tentang keterlambatan -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="text-sm font-medium text-amber-800">Informasi Keterlambatan</h4>
                <p class="text-sm text-amber-700 mt-1">
                    Status keterlambatan otomatis dihitung berdasarkan toleransi shift masing-masing karyawan. 
                    Sistem akan mengupdate status menjadi "Terlambat" jika absen masuk melebihi toleransi yang ditentukan.
                    Klik "Recalculate Late Status" untuk memperbarui perhitungan keterlambatan untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabel Absensi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift & Toleransi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status & Keterlambatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($absensi as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="attendance_ids[]" value="{{ $attendance->id }}" 
                                       class="attendance-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $attendance->user->foto_url }}" alt="">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $attendance->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $attendance->user->id_karyawan }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                        {{ $attendance->shift->nama }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($attendance->shift->jam_masuk)->format('H:i') }} - {{ \Carbon\Carbon::parse($attendance->shift->jam_keluar)->format('H:i') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Toleransi: {{ $attendance->shift->toleransi_menit }} menit
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($attendance->jam_masuk)
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') }}</span>
                                        @if($attendance->menit_terlambat > 0)
                                            <span class="text-red-500 text-xs font-medium">
                                                Terlambat {{ $attendance->menit_terlambat }} menit
                                            </span>
                                        @elseif($attendance->status_absen === 'hadir')
                                            <span class="text-green-500 text-xs">Tepat waktu</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($attendance->jam_keluar)
                                    {{ Carbon\Carbon::parse($attendance->jam_keluar)->format('H:i') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($attendance->status_absen == 'hadir') bg-green-100 text-green-800
                                    @elseif($attendance->status_absen == 'terlambat') bg-yellow-100 text-yellow-800
                                    @elseif($attendance->status_absen == 'tidak_hadir') bg-red-100 text-red-800
                                    @elseif($attendance->status_absen == 'izin') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $attendance->getStatusAbsenText() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-1">
                                    @if($attendance->status_masuk == 'menunggu')
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Masuk Pending</span>
                                    @elseif($attendance->status_masuk == 'disetujui')
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Masuk OK</span>
                                    @elseif($attendance->status_masuk == 'ditolak')
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Masuk Ditolak</span>
                                    @endif

                                    @if($attendance->jam_keluar)
                                        @if($attendance->status_keluar == 'menunggu')
                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Keluar Pending</span>
                                        @elseif($attendance->status_keluar == 'disetujui')
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Keluar OK</span>
                                        @elseif($attendance->status_keluar == 'ditolak')
                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Keluar Ditolak</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="showAttendanceDetail({{ $attendance->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors" 
                                            title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    @if($attendance->status_masuk == 'menunggu' || $attendance->status_keluar == 'menunggu')
                                        <button onclick="openApprovalModal({{ $attendance->id }})" 
                                                class="text-green-600 hover:text-green-900 transition-colors"
                                                title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <button onclick="openEditStatusModal({{ $attendance->id }})" 
                                            class="text-purple-600 hover:text-purple-900 transition-colors"
                                            title="Edit Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data absensi</p>
                                    <p class="text-sm">Belum ada absensi untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($absensi->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $absensi->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Absensi -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Detail Absensi</h3>
                <button onclick="closeModal('detailModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="detailContent" class="p-6">
                <!-- Content akan diisi via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Approval -->
<div id="approvalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Approval Absensi</h3>
            </div>
            <form id="approvalForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div id="approvalContent">
                        <!-- Content akan diisi via JavaScript -->
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin</label>
                        <textarea name="catatan_admin" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('approvalModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="approveBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Setujui
                    </button>
                    <button type="button" id="rejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Status -->
<div id="editStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Edit Status Absensi</h3>
            </div>
            <form id="editStatusForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Absensi</label>
                        <select name="status_absen" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Menit Terlambat</label>
                        <input type="number" name="menit_terlambat" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Menit Lembur</label>
                        <input type="number" name="menit_lembur" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin</label>
                        <textarea name="catatan_admin" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editStatusModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export -->
<div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Export Data Absensi</h3>
            </div>
            <form action="{{ route('admin.absensi.export') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                        <select name="format" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('exportModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Action -->
<div id="bulkActionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Action</h3>
            </div>
            <form action="{{ route('admin.absensi.bulk-action') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Aksi</label>
                        <select name="action" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Aksi</option>
                            <option value="approve_masuk">Setujui Absen Masuk (Auto Calculate Late)</option>
                            <option value="reject_masuk">Tolak Absen Masuk</option>
                            <option value="approve_keluar">Setujui Absen Keluar</option>
                            <option value="reject_keluar">Tolak Absen Keluar</option>
                            <option value="recalculate_late">Recalculate Status Keterlambatan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin</label>
                        <textarea name="catatan_admin" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Catatan untuk semua item yang dipilih"></textarea>
                    </div>
                    <div id="selectedItemsInfo" class="text-sm text-gray-600">
                        <!-- Info akan diisi via JavaScript -->
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('bulkActionModal')" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Proses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal Functions
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

// Jump to today function
function jumpToToday() {
    const today = new Date().toISOString().split('T')[0];
    const url = new URL(window.location.href);
    url.searchParams.set('tanggal', today);
    window.location.href = url.toString();
}

// Recalculate late status function - PAKAI FORM REDIRECT (bukan AJAX)
function recalculateLateStatus() {
    const tanggal = '{{ $tanggal }}';
    const tanggalFormatted = new Date(tanggal).toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    if (confirm(`Yakin ingin menghitung ulang status keterlambatan untuk ${tanggalFormatted}?`)) {
        // Create form and submit untuk redirect back
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/absensi/recalculate-late';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const tanggalInput = document.createElement('input');
        tanggalInput.type = 'hidden';
        tanggalInput.name = 'tanggal';
        tanggalInput.value = tanggal;
        
        form.appendChild(csrfToken);
        form.appendChild(tanggalInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Show attendance detail
function showAttendanceDetail(attendanceId) {
    document.getElementById('detailContent').innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading...</span>
        </div>
    `;
    
    openModal('detailModal');
    
    fetch(`/admin/absensi/${attendanceId}/json`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('detailContent').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Informasi Karyawan</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-medium ml-2">${data.user.name}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">ID:</span>
                                    <span class="font-medium ml-2">${data.user.id_karyawan}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Shift:</span>
                                    <span class="font-medium ml-2">${data.shift.nama}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Tanggal:</span>
                                    <span class="font-medium ml-2">${data.tanggal_absen}</span>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="font-semibold text-gray-900">Informasi Shift</h4>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jam Masuk Scheduled:</span>
                                    <span class="font-medium">${data.shift.jam_masuk}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jam Keluar Scheduled:</span>
                                    <span class="font-medium">${data.shift.jam_keluar}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Toleransi:</span>
                                    <span class="font-medium">${data.shift.toleransi_menit} menit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Status Absensi</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium">${data.status_absen_text}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jam Masuk Actual:</span>
                                    <span class="font-medium">${data.jam_masuk || '-'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jam Keluar Actual:</span>
                                    <span class="font-medium">${data.jam_keluar || '-'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Menit Terlambat:</span>
                                    <span class="font-medium ${data.menit_terlambat > 0 ? 'text-red-600' : 'text-green-600'}">${data.menit_terlambat} menit</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Menit Lembur:</span>
                                    <span class="font-medium">${data.menit_lembur || 0} menit</span>
                                </div>
                            </div>
                        </div>
                        
                        ${data.catatan_admin ? `
                        <h4 class="font-semibold text-gray-900">Catatan Admin</h4>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-700">${data.catatan_admin}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Foto Masuk</h4>
                        <div class="bg-gray-100 rounded-lg p-4 text-center">
                            ${data.foto_masuk_url ? 
                                `<img src="${data.foto_masuk_url}" class="max-w-full h-auto rounded-lg" alt="Foto Masuk">` :
                                '<span class="text-gray-500 text-sm">Tidak ada foto</span>'
                            }
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Foto Keluar</h4>
                        <div class="bg-gray-100 rounded-lg p-4 text-center">
                            ${data.foto_keluar_url ? 
                                `<img src="${data.foto_keluar_url}" class="max-w-full h-auto rounded-lg" alt="Foto Keluar">` :
                                '<span class="text-gray-500 text-sm">Tidak ada foto</span>'
                            }
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600">Gagal memuat detail absensi</p>
                </div>
            `;
        });
}

// Open approval modal
function openApprovalModal(attendanceId) {
    fetch(`/admin/absensi/${attendanceId}/json`)
        .then(response => response.json())
        .then(data => {
            let content = `<div class="space-y-3">`;
            content += `<p class="text-sm text-gray-600">Karyawan: <strong>${data.user.name}</strong></p>`;
            content += `<p class="text-sm text-gray-600">Shift: <strong>${data.shift.nama}</strong> (${data.shift.jam_masuk} - ${data.shift.jam_keluar})</p>`;
            content += `<p class="text-sm text-gray-600">Toleransi: <strong>${data.shift.toleransi_menit} menit</strong></p>`;
            
            if (data.status_masuk === 'menunggu') {
                const lateInfo = data.menit_terlambat > 0 ? ` (Akan dikategorikan TERLAMBAT ${data.menit_terlambat} menit)` : ' (Tepat waktu)';
                content += `<div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">Absen masuk menunggu persetujuan</p>
                    <p class="text-xs text-yellow-600">Jam: ${data.jam_masuk || '-'}${lateInfo}</p>
                </div>`;
            }
            
            if (data.status_keluar === 'menunggu') {
                content += `<div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">Absen keluar menunggu persetujuan</p>
                    <p class="text-xs text-yellow-600">Jam: ${data.jam_keluar || '-'}</p>
                </div>`;
            }
            
            content += `</div>`;
            
            document.getElementById('approvalContent').innerHTML = content;
            
            // Set form actions
            const form = document.getElementById('approvalForm');
            const approveBtn = document.getElementById('approveBtn');
            const rejectBtn = document.getElementById('rejectBtn');
            
            // Default to approve masuk if pending
            if (data.status_masuk === 'menunggu') {
                form.action = `/admin/absensi/${attendanceId}/approve-masuk`;
                rejectBtn.onclick = function() {
                    form.action = `/admin/absensi/${attendanceId}/reject-masuk`;
                    form.submit();
                };
            } else if (data.status_keluar === 'menunggu') {
                form.action = `/admin/absensi/${attendanceId}/approve-keluar`;
                rejectBtn.onclick = function() {
                    form.action = `/admin/absensi/${attendanceId}/reject-keluar`;
                    form.submit();
                };
            }
            
            openModal('approvalModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data approval');
        });
}

// Open edit status modal
function openEditStatusModal(attendanceId) {
    fetch(`/admin/absensi/${attendanceId}/json`)
        .then(response => response.json())
        .then(data => {
            const form = document.getElementById('editStatusForm');
            form.action = `/admin/absensi/${attendanceId}/update-status`;
            
            // Populate form
            form.querySelector('[name="status_absen"]').value = data.status_absen;
            form.querySelector('[name="menit_terlambat"]').value = data.menit_terlambat || 0;
            form.querySelector('[name="menit_lembur"]').value = data.menit_lembur || 0;
            form.querySelector('[name="catatan_admin"]').value = data.catatan_admin || '';
            
            openModal('editStatusModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data absensi');
        });
}

// Export modal
function openExportModal() {
    openModal('exportModal');
}

// Bulk action modal
function openBulkActionModal() {
    const checked = document.querySelectorAll('.attendance-checkbox:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu item untuk bulk action');
        return;
    }
    
    // Add hidden inputs for selected IDs
    const form = document.querySelector('#bulkActionModal form');
    
    // Remove existing hidden inputs
    form.querySelectorAll('input[name="attendance_ids[]"]').forEach(input => input.remove());
    
    // Add new hidden inputs
    checked.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'attendance_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    
    document.getElementById('selectedItemsInfo').innerHTML = `<p>Item terpilih: <strong>${checked.length}</strong></p>`;
    
    openModal('bulkActionModal');
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.attendance-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update select all when individual checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('attendance-checkbox')) {
        const checkboxes = document.querySelectorAll('.attendance-checkbox');
        const checkedBoxes = document.querySelectorAll('.attendance-checkbox:checked');
        const selectAll = document.getElementById('selectAll');
        
        if (checkedBoxes.length === checkboxes.length) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else if (checkedBoxes.length > 0) {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }
});
</script>

@endsection