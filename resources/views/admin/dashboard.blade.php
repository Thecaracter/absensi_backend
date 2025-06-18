@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan aktivitas dan statistik sistem absensi')

@push('styles')
<style>
    .stats-card {
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        transition: stroke-dashoffset 0.35s;
        transform-origin: 50% 50%;
    }
    
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .gradient-bg-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .gradient-bg-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .gradient-bg-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .gradient-bg-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Karyawan -->
        <div class="stats-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden fade-in-up">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_karyawan'] }}</p>
                        <p class="text-sm text-green-600 mt-2">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ $stats['total_karyawan'] }} Aktif
                            </span>
                        </p>
                    </div>
                    <div class="gradient-bg-1 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('admin.karyawan.index') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    Kelola Karyawan →
                </a>
            </div>
        </div>

        <!-- Absensi Hari Ini -->
        <div class="stats-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden fade-in-up" style="animation-delay: 0.1s">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Absensi Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['absensi_hari_ini'] }}</p>
                        <p class="text-sm text-blue-600 mt-2">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ $stats['karyawan_hadir_hari_ini'] }} Hadir
                            </span>
                        </p>
                    </div>
                    <div class="gradient-bg-2 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('admin.absensi.index') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    Lihat Detail →
                </a>
            </div>
        </div>

        <!-- Izin Menunggu -->
        <div class="stats-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden fade-in-up" style="animation-delay: 0.2s">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Izin Menunggu</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['izin_menunggu'] }}</p>
                        <p class="text-sm text-yellow-600 mt-2">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Perlu Review
                            </span>
                        </p>
                    </div>
                    <div class="gradient-bg-3 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('admin.izin.index') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    Review Izin →
                </a>
            </div>
        </div>

        <!-- Karyawan Terlambat -->
        <div class="stats-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden fade-in-up" style="animation-delay: 0.3s">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Terlambat Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['karyawan_terlambat_hari_ini'] }}</p>
                        <p class="text-sm text-red-600 mt-2">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Dari {{ $stats['total_karyawan'] }} Total
                            </span>
                        </p>
                    </div>
                    <div class="gradient-bg-4 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('admin.absensi.index') }}?status_absen=terlambat" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    Lihat Detail →
                </a>
            </div>
        </div>
    </div>

    <!-- Charts & Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Attendance Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 fade-in-up" style="animation-delay: 0.4s">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Tren Absensi 7 Hari Terakhir</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Hadir</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Terlambat</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Tidak Hadir</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Shift Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 fade-in-up" style="animation-delay: 0.5s">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Distribusi per Shift</h3>
            </div>
            <div class="p-6">
                @foreach($absensiPerShift as $shiftName => $data)
                    <div class="mb-6 last:mb-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ $shiftName }}</span>
                            <span class="text-sm text-gray-500">{{ $data['total'] }} total</span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                            @php
                                $hadirPercent = $data['total'] > 0 ? ($data['hadir'] / $data['total']) * 100 : 0;
                                $terlambatPercent = $data['total'] > 0 ? ($data['terlambat'] / $data['total']) * 100 : 0;
                            @endphp
                            <div class="bg-green-500 h-2.5 rounded-l-full" style="width: {{ $hadirPercent }}%"></div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>✓ {{ $data['hadir'] }} Hadir</span>
                            <span>⚠ {{ $data['terlambat'] }} Terlambat</span>
                            <span>✗ {{ $data['tidak_hadir'] }} Tidak Hadir</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activities & Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Attendance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 fade-in-up" style="animation-delay: 0.6s">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Absensi Terbaru</h3>
                    <a href="{{ route('admin.absensi.index') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($absensiHariIni as $absen)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <img class="h-10 w-10 rounded-full object-cover" 
                                 src="{{ $absen->user->foto_url }}" 
                                 alt="{{ $absen->user->name }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $absen->user->name }}
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $absen->getStatusBadgeClass() }}">
                                        {{ $absen->getStatusAbsenText() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-sm text-gray-500">
                                        {{ $absen->shift->nama }} • 
                                        @if($absen->jam_masuk)
                                            Masuk: {{ \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') }}
                                        @endif
                                        @if($absen->jam_keluar)
                                            • Keluar: {{ \Carbon\Carbon::parse($absen->jam_keluar)->format('H:i') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $absen->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p>Belum ada absensi hari ini</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Leave Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 fade-in-up" style="animation-delay: 0.7s">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Izin Menunggu Approval</h3>
                    <a href="{{ route('admin.izin.index') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($izinMenunggu as $izin)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <img class="h-10 w-10 rounded-full object-cover" 
                                 src="{{ $izin->user->foto_url }}" 
                                 alt="{{ $izin->user->name }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $izin->user->name }}
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $izin->getJenisIzinText() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-sm text-gray-500">
                                        {{ $izin->tanggal_mulai->format('d M') }} - {{ $izin->tanggal_selesai->format('d M') }}
                                        ({{ $izin->total_hari }} hari)
                                    </p>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.izin.show', $izin) }}" 
                                           class="text-xs text-blue-600 hover:text-blue-500 font-medium">
                                            Review
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>Tidak ada izin yang menunggu approval</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 fade-in-up" style="animation-delay: 0.8s">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Karyawan Terbaik Bulan Ini</h3>
            <p class="text-sm text-gray-500 mt-1">Berdasarkan tingkat kehadiran</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($topKaryawan as $index => $karyawan)
                    <div class="text-center p-4 rounded-lg {{ $index === 0 ? 'bg-yellow-50 border-2 border-yellow-200' : 'bg-gray-50' }}">
                        @if($index === 0)
                            <div class="w-6 h-6 bg-yellow-500 text-white rounded-full flex items-center justify-center text-xs font-bold mx-auto mb-2">
                                👑
                            </div>
                        @endif
                        <img class="h-12 w-12 rounded-full object-cover mx-auto mb-3 ring-2 {{ $index === 0 ? 'ring-yellow-300' : 'ring-gray-200' }}" 
                             src="{{ $karyawan->foto_url }}" 
                             alt="{{ $karyawan->name }}">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $karyawan->name }}</h4>
                        <p class="text-xs text-gray-500 mb-2">{{ $karyawan->shift->nama ?? 'No Shift' }}</p>
                        <div class="flex items-center justify-center space-x-1">
                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-900">{{ $karyawan->attendances_count }} hari</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                @foreach($absensi7Hari as $data)
                    '{{ $data["hari"] }} {{ \Carbon\Carbon::parse($data["tanggal"])->format("d/m") }}',
                @endforeach
            ],
            datasets: [
                {
                    label: 'Hadir',
                    data: [
                        @foreach($absensi7Hari as $data)
                            {{ $data['hadir'] }},
                        @endforeach
                    ],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Terlambat',
                    data: [
                        @foreach($absensi7Hari as $data)
                            {{ $data['terlambat'] }},
                        @endforeach
                    ],
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Tidak Hadir',
                    data: [
                        @foreach($absensi7Hari as $data)
                            {{ $data['tidak_hadir'] }},
                        @endforeach
                    ],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(156, 163, 175, 0.2)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });

    // Auto refresh setiap 5 menit
    setInterval(function() {
        window.location.reload();
    }, 300000); // 5 minutes

    // Animasi counter
    function animateCounter(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerText = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Jalankan animasi saat halaman load
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.text-3xl');
        counters.forEach(counter => {
            const target = parseInt(counter.innerText);
            counter.innerText = '0';
            animateCounter(counter, 0, target, 1000);
        });
    });
</script>
@endpush