@extends('layouts.app')

@section('title', 'Jadwal Karyawan')

@push('styles')
<style>
    .calendar-cell {
        min-height: 120px;
        border: 1px solid #e5e7eb;
    }
    .attendance-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .tab-active {
        background-color: #3b82f6;
        color: white;
    }
    .modal {
        backdrop-filter: blur(4px);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal Karyawan</h1>
            <p class="mt-1 text-sm text-gray-600">
                Kelola jadwal kerja, shift, dan monitoring kehadiran karyawan
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button type="button" 
                    onclick="openExportModal()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Jadwal
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('admin.jadwal.index', ['view' => 'monthly']) }}" 
               class="tab-link py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'monthly' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Jadwal Bulanan
            </a>
            <a href="{{ route('admin.jadwal.index', ['view' => 'shift']) }}" 
               class="tab-link py-2 px-1 border-b-2 font-medium text-sm {{ $view === 'shift' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Manajemen Shift
            </a>
        </nav>
    </div>

    @if($view === 'monthly')
        <!-- Monthly View with Shift Summary -->
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- Month Navigation -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Manajemen Jadwal Otomatis - {{ $monthDate->format('F Y') }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.jadwal.index', ['view' => 'monthly', 'month' => $monthDate->copy()->subMonth()->format('Y-m')]) }}" 
                               class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.jadwal.index', ['view' => 'monthly', 'month' => $monthDate->copy()->addMonth()->format('Y-m')]) }}" 
                               class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button" 
                                onclick="openAutoGenerateModal()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            AUTO GENERATE
                        </button>
                        <button type="button" 
                                onclick="fixPendingStatus()"
                                class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            FIX STATUS
                        </button>
                        <button type="button" 
                                onclick="openClearScheduleModal()"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            HAPUS JADWAL
                        </button>
                    </div>
                </div>
                
                <!-- Monthly Stats -->
                <div class="mt-4 grid grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $karyawan->count() }}</div>
                        <div class="text-sm text-blue-600">Total Karyawan</div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $shifts_aktif->count() }}</div>
                        <div class="text-sm text-green-600">Total Shift</div>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        @php
                            $workingDays = 0;
                            $current = $monthDate->copy()->startOfMonth();
                            while ($current <= $monthDate->copy()->endOfMonth()) {
                                if (!$current->isWeekend()) $workingDays++;
                                $current->addDay();
                            }
                        @endphp
                        <div class="text-2xl font-bold text-yellow-600">{{ $workingDays }}</div>
                        <div class="text-sm text-yellow-600">Hari Kerja</div>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg">
                        @php
                            $existingSchedules = 0;
                            foreach($daily_shift_summary as $summary) {
                                $existingSchedules += $summary['total'];
                            }
                        @endphp
                        <div class="text-2xl font-bold text-red-600">{{ $existingSchedules }}</div>
                        <div class="text-sm text-red-600">Jadwal Existing</div>
                    </div>
                </div>
                
                {{-- DEBUG INFO - Remove this after fixing --}}
                @if(env('APP_DEBUG'))
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-medium text-yellow-800">Debug Info:</h4>
                    <div class="text-sm text-yellow-700 mt-2">
                        <p>Total attendances loaded: {{ isset($attendances) ? $attendances->count() : 'N/A' }}</p>
                        <p>Date range: {{ $monthDate->copy()->startOfMonth()->format('Y-m-d') }} to {{ $monthDate->copy()->endOfMonth()->format('Y-m-d') }}</p>
                        <p>Daily summary count: {{ count($daily_shift_summary) }}</p>
                        @if(isset($attendances) && $attendances->count() > 0)
                            <p>Sample attendance date: {{ $attendances->first()->tanggal_absen }}</p>
                            <p>Sample shift: {{ $attendances->first()->shift->nama ?? 'No shift' }}</p>
                        @endif
                        @php
                            $sampleDay = '2025-06-18';
                            $sampleSummary = $daily_shift_summary[$sampleDay] ?? null;
                        @endphp
                        @if($sampleSummary)
                            <p>Sample day ({{ $sampleDay }}) summary: {{ $sampleSummary['total'] }} attendances, {{ count($sampleSummary['shifts']) }} shifts</p>
                        @else
                            <p>No summary found for sample day {{ $sampleDay }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Calendar Grid -->
            <div class="p-6">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 mb-4">
                    @foreach(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                        <div class="p-3 text-center text-sm font-medium text-gray-700 bg-gray-50 rounded">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-1">
                    @foreach($weeks as $week)
                        @foreach($week as $day)
                            @php
                                $dayKey = $day['date']->format('Y-m-d');
                                // Use the new structure from controller
                                $summary = $daily_shift_summary[$dayKey] ?? ['total' => 0, 'shifts' => []];
                            @endphp
                            <div class="min-h-[120px] border border-gray-200 rounded-lg p-2 cursor-pointer hover:bg-gray-50 transition-colors
                                        {{ !$day['is_current_month'] ? 'bg-gray-50 text-gray-400' : '' }}
                                        {{ $day['is_today'] ? 'bg-blue-50 border-blue-300' : '' }}
                                        {{ $day['is_weekend'] ? 'bg-red-50' : '' }}"
                                 onclick="openDateDetailModal('{{ $dayKey }}')">
                                
                                <!-- Date Number -->
                                <div class="text-sm font-medium mb-2 
                                            {{ $day['is_today'] ? 'text-blue-600' : '' }}
                                            {{ $day['is_weekend'] ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $day['date']->format('d') }}
                                    @if(env('APP_DEBUG') && $summary['total'] > 0)
                                        <span class="text-xs bg-green-200 px-1 rounded">{{ $summary['total'] }}</span>
                                    @endif
                                </div>

                                <!-- Shift Summary -->
                                @if($summary['total'] > 0)
                                    <div class="space-y-1">
                                        @foreach($summary['shifts'] as $shift)
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-medium text-gray-700 truncate">{{ $shift['nama'] }}</span>
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">
                                                    {{ $shift['count'] }}
                                                </span>
                                            </div>
                                            @if($shift['jam_masuk'])
                                                <div class="text-xs text-gray-500 ml-1">
                                                    {{ Carbon\Carbon::parse($shift['jam_masuk'])->format('H:i') }} - 
                                                    {{ Carbon\Carbon::parse($shift['jam_keluar'])->format('H:i') }}
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        <!-- Total Summary -->
                                        <div class="border-t border-gray-200 pt-1 mt-2">
                                            <div class="flex items-center justify-between text-xs font-semibold">
                                                <span class="text-gray-600">Total</span>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                    {{ $summary['total'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 text-center mt-4">
                                        Belum ada jadwal
                                        @if(env('APP_DEBUG'))
                                            <br><span class="text-red-500">Debug: {{ $dayKey }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
                
                <!-- Legend -->
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-50 border border-blue-300 rounded mr-2"></div>
                        <span class="text-gray-600">Hari Ini</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-50 rounded mr-2"></div>
                        <span class="text-gray-600">Weekend</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-gray-50 rounded mr-2"></div>
                        <span class="text-gray-600">Bulan Lain</span>
                    </div>
                    <div class="ml-auto text-gray-500">
                        💡 Klik tanggal untuk melihat detail karyawan per shift
                    </div>
                </div>
            </div>
        </div>

    @elseif($view === 'shift')
        <!-- Shift Management View -->
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Manajemen Shift</h3>
                    <button type="button" 
                            onclick="openAddShiftModal()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Shift
                    </button>
                </div>
            </div>

            <!-- Shift List -->
            <div class="p-6">
                <div class="grid gap-4">
                    @forelse($shifts_all as $shift)
                        <div class="shift-card border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $shift->nama }}</h4>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            <span>⏰ {{ $shift->jam_masuk }} - {{ $shift->jam_keluar }}</span>
                                            <span>⏱️ Toleransi: {{ $shift->toleransi_menit }} menit</span>
                                            @if($shift->aktif)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                        @if($shift->keterangan)
                                            <p class="text-sm text-gray-500 mt-1">{{ $shift->keterangan }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <button type="button" 
                                            onclick="editShift({{ $shift->id }})"
                                            class="text-blue-600 hover:text-blue-800 p-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    
                                    <form action="{{ route('admin.jadwal.shift.toggle-status', $shift) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-yellow-600 hover:text-yellow-800 p-2"
                                                title="{{ $shift->aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            @if($shift->aktif)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.jadwal.shift.destroy', $shift) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus shift ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 p-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada shift</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan shift pertama.</p>
                            <div class="mt-6">
                                <button type="button" 
                                        onclick="openAddShiftModal()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Shift
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Date Detail Modal -->
<div id="dateDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 modal">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalDateTitle">
                    Detail Jadwal - Loading...
                </h3>
                <button type="button" 
                        onclick="closeDateDetailModal()"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div id="modalContent">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Generate Modal -->
<div id="autoGenerateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Auto Generate Jadwal</h3>
            <form id="autoGenerateForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <input type="month" name="month" 
                               value="{{ $monthDate->format('Y-m') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="exclude_weekends" checked
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Exclude Weekends</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="overwrite"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Timpa Jadwal Lama</label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Shift Ratio</label>
                        <input type="number" name="min_shift_ratio" 
                               value="0.8" step="0.1" min="0.1" max="1.0"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeAutoGenerateModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg">
                        Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear Schedule Modal -->
<div id="clearScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Hapus Jadwal</h3>
            <form id="clearScheduleForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <input type="month" name="month" 
                               value="{{ $monthDate->format('Y-m') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="clear_attended"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-700">Hapus juga jadwal yang sudah ada absensi</label>
                    </div>
                    
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                        <p class="text-sm text-yellow-800">⚠️ Perhatian: Tindakan ini tidak dapat dibatalkan!</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeClearScheduleModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                        Hapus Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Export Jadwal</h3>
            <form action="{{ route('admin.jadwal.export') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <select name="periode" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="bulan">Bulanan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" name="tanggal" required value="{{ now()->format('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                        <select name="format" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Shift</label>
                        <select name="shift_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Semua Shift</option>
                            @foreach($shifts_aktif as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExportModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Shift Modal -->
<div id="shiftModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="shiftModalTitle">Tambah Shift</h3>
            <form id="shiftForm" action="{{ route('admin.jadwal.shift.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Shift *</label>
                        <input type="text" name="nama" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2"
                               placeholder="Contoh: Shift Pagi">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Masuk *</label>
                            <input type="time" name="jam_masuk" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Keluar *</label>
                            <input type="time" name="jam_keluar" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Toleransi (menit) *</label>
                        <input type="number" name="toleransi_menit" required min="0" max="60" value="15"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                  placeholder="Deskripsi shift (opsional)"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeShiftModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Date Detail Modal Functions
function openDateDetailModal(date) {
    console.log('Opening modal for date:', date); 
    document.getElementById('dateDetailModal').classList.remove('hidden');
    loadDateDetail(date);
}

function closeDateDetailModal() {
    document.getElementById('dateDetailModal').classList.add('hidden');
}

function loadDateDetail(date) {
    console.log('Loading detail for date:', date); 
    
    // Reset content
    document.getElementById('modalDateTitle').innerText = 'Detail Jadwal - Loading...';
    document.getElementById('modalContent').innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading...</span>
        </div>
    `;

    // Fetch data dengan URL yang benar
    const url = `/admin/jadwal/date-detail?date=${date}`;
    console.log('Fetching from URL:', url); 
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status); 
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); 
        
        if (data.success) {
            document.getElementById('modalDateTitle').innerText = `Detail Jadwal - ${data.date_formatted}`;
            
            let content = '';
            
            // Check if there are any shifts AND total employees > 0
            if (!data.shifts || data.shifts.length === 0 || data.total_employees === 0) {
                content = `
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada jadwal</h3>
                        <p class="mt-1 text-sm text-gray-500">Tidak ada karyawan yang dijadwalkan untuk tanggal ini.</p>
                    </div>
                `;
            } else {
                content = `
                    <div class="mb-4 bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-800">Total Karyawan Terjadwal</span>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-bold">${data.total_employees}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                `;
                
                data.shifts.forEach(shift => {
                    content += `
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">${shift.nama}</h4>
                                    ${shift.jam_masuk ? `<p class="text-sm text-gray-600">${shift.jam_masuk} - ${shift.jam_keluar}</p>` : ''}
                                </div>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">
                                    ${shift.count} orang
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    `;
                    
                    if (shift.employees && shift.employees.length > 0) {
                        shift.employees.forEach(employee => {
                            let statusBadge = '';
                            switch(employee.status_absen) {
                                case 'hadir':
                                    statusBadge = '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Hadir</span>';
                                    break;
                                case 'terlambat':
                                    statusBadge = '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Terlambat</span>';
                                    break;
                                case 'tidak_hadir':
                                    statusBadge = '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Tidak Hadir</span>';
                                    break;
                                case 'izin':
                                    statusBadge = '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Izin</span>';
                                    break;
                                case 'belum_absen':
                                    statusBadge = '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Belum Absen</span>';
                                    break;
                                case 'menunggu':
                                    statusBadge = '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs">Menunggu</span>';
                                    break;
                                default:
                                    statusBadge = '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">Belum Absen</span>';
                            }
                            
                            content += `
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">${employee.name}</span>
                                        ${statusBadge}
                                    </div>
                                    <div class="text-xs text-gray-600">ID: ${employee.id_karyawan}</div>
                                    ${employee.jam_masuk ? `<div class="text-xs text-gray-600 mt-1">Masuk: ${employee.jam_masuk}</div>` : ''}
                                    ${employee.jam_keluar ? `<div class="text-xs text-gray-600">Keluar: ${employee.jam_keluar}</div>` : ''}
                                </div>
                            `;
                        });
                    } else {
                        content += `
                            <div class="col-span-3 text-center py-4 text-gray-500">
                                Tidak ada karyawan di shift ini
                            </div>
                        `;
                    }
                    
                    content += `
                            </div>
                        </div>
                    `;
                });
                
                content += '</div>';
            }
            
            document.getElementById('modalContent').innerHTML = content;
        } else {
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="text-red-600 mb-2">❌</div>
                    <p class="text-gray-600">Gagal memuat data: ${data.message || 'Unknown error'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('modalContent').innerHTML = `
            <div class="text-center py-8">
                <div class="text-red-600 mb-2">❌</div>
                <p class="text-gray-600">Terjadi kesalahan saat memuat data.</p>
                <p class="text-xs text-gray-500 mt-2">Error: ${error.message}</p>
            </div>
        `;
    });
}

// Auto Generate Modal Functions
function openAutoGenerateModal() {
    document.getElementById('autoGenerateModal').classList.remove('hidden');
}

function closeAutoGenerateModal() {
    document.getElementById('autoGenerateModal').classList.add('hidden');
}

// Clear Schedule Modal Functions
function openClearScheduleModal() {
    document.getElementById('clearScheduleModal').classList.remove('hidden');
}

function closeClearScheduleModal() {
    document.getElementById('clearScheduleModal').classList.add('hidden');
}

// Export Modal Functions
function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

// Handle Auto Generate Form
document.getElementById('autoGenerateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    fetch('{{ route("admin.jadwal.auto-generate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat generate jadwal');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeAutoGenerateModal();
    });
});

// Handle Clear Schedule Form
document.getElementById('clearScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('Yakin ingin menghapus jadwal? Tindakan ini tidak dapat dibatalkan!')) {
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    fetch('{{ route("admin.jadwal.clear-schedule") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat menghapus jadwal');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        closeClearScheduleModal();
    });
});

// Shift Modal Functions
function openAddShiftModal() {
    document.getElementById('shiftModalTitle').textContent = 'Tambah Shift';
    document.getElementById('shiftForm').action = '{{ route("admin.jadwal.shift.store") }}';
    document.getElementById('shiftForm').reset();
    // Remove method spoofing if exists
    const methodInput = document.getElementById('shiftForm').querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }
    document.getElementById('shiftModal').classList.remove('hidden');
}

function closeShiftModal() {
    document.getElementById('shiftModal').classList.add('hidden');
}

function editShift(shiftId) {
    fetch(`{{ url('admin/jadwal/shift') }}/${shiftId}/json`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('shiftModalTitle').textContent = 'Edit Shift';
            document.getElementById('shiftForm').action = `{{ url('admin/jadwal/shift') }}/${shiftId}`;
            
            // Add method spoofing for PUT
            let methodInput = document.getElementById('shiftForm').querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                document.getElementById('shiftForm').appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Fill form
            document.querySelector('input[name="nama"]').value = data.nama;
            document.querySelector('input[name="jam_masuk"]').value = data.jam_masuk;
            document.querySelector('input[name="jam_keluar"]').value = data.jam_keluar;
            document.querySelector('input[name="toleransi_menit"]').value = data.toleransi_menit;
            document.querySelector('textarea[name="keterangan"]').value = data.keterangan || '';
            
            document.getElementById('shiftModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data shift');
        });
}

function fixPendingStatus() {
    if (confirm('Yakin ingin mengubah semua status "menunggu" yang sudah lewat tanggal menjadi "tidak_hadir"?')) {
        fetch('{{ route("admin.jadwal.fix-status") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                window.location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan saat fix status');
        });
    }
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const modals = ['dateDetailModal', 'autoGenerateModal', 'shiftModal', 'clearScheduleModal', 'exportModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Debug function to test if data exists
function debugScheduleData() {
    const today = new Date().toISOString().split('T')[0];
    console.log('Testing with today\'s date:', today);
    loadDateDetail(today);
}
</script>
@endpush
@endsection