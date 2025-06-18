@extends('layouts.app')

@section('title', 'Manajemen Izin & Cuti')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Manajemen Izin & Cuti</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1">Kelola permohonan izin dan cuti karyawan dengan jadwal otomatis</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <button onclick="openModal('addModal')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="hidden sm:inline">Tambah Izin</span>
                <span class="sm:hidden">Tambah</span>
            </button>
            <button onclick="openExportModal()" 
               class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Total Permohonan</p>
                    <p class="text-lg md:text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Menunggu Approval</p>
                    <p class="text-lg md:text-2xl font-bold text-yellow-600">{{ $stats['menunggu'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Disetujui + Jadwal</p>
                    <p class="text-lg md:text-2xl font-bold text-green-600">{{ $stats['disetujui'] ?? 0 }}</p>
                </div>
                <div class="bg-green-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Ditolak</p>
                    <p class="text-lg md:text-2xl font-bold text-red-600">{{ $stats['ditolak'] ?? 0 }}</p>
                </div>
                <div class="bg-red-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <a href="{{ route('admin.izin.index', ['tab' => 'pending'] + request()->except('tab')) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $tab === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu Approval
                        @if($stats['menunggu'] > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $stats['menunggu'] }}
                            </span>
                        @endif
                    </div>
                </a>
                <a href="{{ route('admin.izin.index', ['tab' => 'processed'] + request()->except('tab')) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $tab === 'processed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0-18h2m0 0h2m-2 0h2a2 2 0 012 2v11a2 2 0 01-2 2m-2 0h-2m2 0v-4"/>
                        </svg>
                        Sudah Diproses
                        @if(($stats['disetujui'] + $stats['ditolak']) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $stats['disetujui'] + $stats['ditolak'] }}
                            </span>
                        @endif
                    </div>
                </a>
            </nav>
        </div>

        <!-- Filters & Bulk Actions -->
        <div class="p-4 md:p-6">
            <form method="GET" action="{{ route('admin.izin.index') }}" class="space-y-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nama karyawan, alasan..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Jenis Izin</label>
                        <select name="jenis_izin" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Jenis</option>
                            <option value="sakit" {{ request('jenis_izin') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="cuti_tahunan" {{ request('jenis_izin') == 'cuti_tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="keperluan_pribadi" {{ request('jenis_izin') == 'keperluan_pribadi' ? 'selected' : '' }}>Keperluan Pribadi</option>
                            <option value="darurat" {{ request('jenis_izin') == 'darurat' ? 'selected' : '' }}>Darurat</option>
                            <option value="lainnya" {{ request('jenis_izin') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    @if($tab === 'processed')
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    @endif
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                        @if(request()->hasAny(['search', 'jenis_izin', 'status']))
                            <a href="{{ route('admin.izin.index', ['tab' => $tab]) }}" 
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg transition-colors text-sm">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Bulk Actions untuk Pending Tab -->
            @if($tab === 'pending' && isset($izin) && $izin->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Pilih Semua</span>
                            </label>
                            <span id="selectedCount" class="text-sm text-gray-600">0 terpilih</span>
                        </div>
                        <div id="bulkActions" class="hidden flex flex-wrap gap-2">
                            <button onclick="bulkAction('approve')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                Setujui + Buat Jadwal
                            </button>
                            <button onclick="bulkAction('reject')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Leave Requests List - Mobile Cards -->
    <div class="block lg:hidden space-y-4">
        @forelse($izin ?? [] as $leave)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex items-center">
                        @if($tab === 'pending')
                            <input type="checkbox" name="izin_ids[]" value="{{ $leave->id }}" 
                                   class="izin-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mr-3">
                        @endif
                        <img class="h-12 w-12 rounded-full object-cover flex-shrink-0" 
                             src="{{ $leave->user->foto_url ?? asset('images/default-avatar.png') }}" 
                             alt="{{ $leave->user->name }}"
                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $leave->user->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $leave->user->id_karyawan }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ ucfirst(str_replace('_', ' ', $leave->jenis_izin)) }}</p>
                                <p class="text-xs text-gray-600">{{ $leave->tanggal_mulai->format('d M Y') }} - {{ $leave->tanggal_selesai->format('d M Y') }}</p>
                                <p class="text-xs text-gray-600">{{ $leave->total_hari }} hari</p>
                                @if($leave->status === 'disetujui')
                                    <p class="text-xs text-green-600 font-medium mt-1">✓ Jadwal otomatis dibuat</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="showModal('{{ $leave->id }}')" 
                                        class="text-blue-600 hover:text-blue-900 p-1" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                @if($leave->status == 'menunggu')
                                    <button onclick="editModal('{{ $leave->id }}')" 
                                            class="text-indigo-600 hover:text-indigo-900 p-1" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($leave->status === 'disetujui')
                                    <button onclick="cancelApproval('{{ $leave->id }}')" 
                                            class="text-orange-600 hover:text-orange-900 p-1" title="Batalkan Approval">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11H6m0 0l3-3m-3 3l3 3m5-6v6a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h3"/>
                                        </svg>
                                    </button>
                                @endif
                                <button onclick="deleteIzin('{{ $leave->id }}')" 
                                        class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            @if($leave->status == 'menunggu')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></div>
                                    Menunggu
                                </span>
                            @elseif($leave->status == 'disetujui')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                    Disetujui
                                </span>
                            @elseif($leave->status == 'ditolak')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                    Ditolak
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Diajukan: {{ $leave->created_at->format('d M Y H:i') }}
                        </div>
                        @if($leave->alasan)
                            <div class="text-xs text-gray-600 mt-1 line-clamp-2">
                                {{ Str::limit($leave->alasan, 100) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    @if($tab === 'pending')
                        Tidak ada izin menunggu approval
                    @else
                        Tidak ada izin yang sudah diproses
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'jenis_izin', 'status']))
                        Tidak ada izin yang sesuai dengan filter.
                    @else
                        @if($tab === 'pending')
                            Belum ada permohonan izin yang menunggu approval.
                        @else
                            Belum ada izin yang sudah diproses.
                        @endif
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'jenis_izin', 'status']))
                        <a href="{{ route('admin.izin.index', ['tab' => $tab]) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filter
                        </a>
                    @else
                        <button onclick="openModal('addModal')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Izin
                        </button>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Leave Requests List - Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                @if($tab === 'pending')
                    Izin Menunggu Approval
                @else
                    Izin Sudah Diproses
                @endif
            </h3>
            <div class="text-sm text-gray-500">
                Total: {{ isset($izin) ? $izin->total() : 0 }} permohonan
            </div>
        </div>
        
        @if(isset($izin) && $izin->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            @if($tab === 'pending')
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="selectAllDesktop" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            @if($tab === 'processed')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($izin as $leave)
                            <tr class="hover:bg-gray-50">
                                @if($tab === 'pending')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="izin_ids[]" value="{{ $leave->id }}" 
                                           class="izin-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $leave->user->foto_url ?? asset('images/default-avatar.png') }}" 
                                             alt="{{ $leave->user->name }}"
                                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $leave->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $leave->user->id_karyawan }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $leave->jenis_izin)) }}</div>
                                    @if($leave->alasan)
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $leave->alasan }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $leave->tanggal_mulai->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">sampai {{ $leave->tanggal_selesai->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $leave->total_hari }} hari</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($leave->status == 'menunggu')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></div>
                                            Menunggu
                                        </span>
                                    @elseif($leave->status == 'disetujui')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                            Disetujui
                                        </span>
                                    @elseif($leave->status == 'ditolak')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                            Ditolak
                                        </span>
                                    @endif
                                    @if($leave->approver)
                                        <div class="text-xs text-gray-500 mt-1">oleh {{ $leave->approver->name }}</div>
                                    @endif
                                </td>
                                @if($tab === 'processed')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($leave->status === 'disetujui')
                                        @php
                                            $attendanceCount = \App\Models\Attendance::where('user_id', $leave->user_id)
                                                ->whereBetween('tanggal_absen', [$leave->tanggal_mulai, $leave->tanggal_selesai])
                                                ->where('status_absen', 'izin')
                                                ->where('catatan_admin', 'like', 'Auto: Izin%')
                                                ->count();
                                        @endphp
                                        @if($attendanceCount > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $attendanceCount }} hari
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                Belum dibuat
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="showModal('{{ $leave->id }}')" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors p-1 hover:bg-blue-50 rounded" 
                                                title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        @if($leave->status == 'menunggu')
                                            <button onclick="approveIzin('{{ $leave->id }}')" 
                                                    class="text-green-600 hover:text-green-900 transition-colors p-1 hover:bg-green-50 rounded" 
                                                    title="Setujui + Buat Jadwal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button onclick="rejectIzin('{{ $leave->id }}')" 
                                                    class="text-red-600 hover:text-red-900 transition-colors p-1 hover:bg-red-50 rounded" 
                                                    title="Tolak">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            <button onclick="editModal('{{ $leave->id }}')" 
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors p-1 hover:bg-indigo-50 rounded" 
                                                    title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if($leave->status === 'disetujui')
                                            <button onclick="cancelApproval('{{ $leave->id }}')" 
                                                    class="text-orange-600 hover:text-orange-900 transition-colors p-1 hover:bg-orange-50 rounded" 
                                                    title="Batalkan Approval + Hapus Jadwal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11H6m0 0l3-3m-3 3l3 3m5-6v6a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h3"/>
                                                </svg>
                                            </button>
                                        @endif
                                        <button onclick="deleteIzin('{{ $leave->id }}')" 
                                                class="text-red-600 hover:text-red-900 transition-colors p-1 hover:bg-red-50 rounded" 
                                                title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    @if($tab === 'pending')
                        Tidak ada izin menunggu approval
                    @else
                        Tidak ada izin yang sudah diproses
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'jenis_izin', 'status']))
                        Tidak ada izin yang sesuai dengan filter yang dipilih.
                    @else
                        @if($tab === 'pending')
                            Belum ada permohonan izin yang menunggu approval.
                        @else
                            Belum ada izin yang sudah diproses.
                        @endif
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'jenis_izin', 'status']))
                        <a href="{{ route('admin.izin.index', ['tab' => $tab]) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filter
                        </a>
                    @else
                        <button onclick="openModal('addModal')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Izin
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if(isset($izin) && $izin->hasPages())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-700">
                    <span>Menampilkan</span>
                    <span class="font-medium mx-1">{{ $izin->firstItem() }}</span>
                    <span>sampai</span>
                    <span class="font-medium mx-1">{{ $izin->lastItem() }}</span>
                    <span>dari</span>
                    <span class="font-medium mx-1">{{ $izin->total() }}</span>
                    <span>permohonan</span>
                </div>
                <div>
                    {{ $izin->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-2xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Permohonan Izin</h3>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.izin.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto px-1">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan *</label>
                    <select name="user_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Karyawan</option>
                        @foreach($karyawan ?? [] as $emp)
                            <option value="{{ $emp->id }}" {{ old('user_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} - {{ $emp->id_karyawan }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin *</label>
                    <select name="jenis_izin" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Jenis Izin</option>
                        <option value="sakit" {{ old('jenis_izin') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="cuti_tahunan" {{ old('jenis_izin') == 'cuti_tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                        <option value="keperluan_pribadi" {{ old('jenis_izin') == 'keperluan_pribadi' ? 'selected' : '' }}>Keperluan Pribadi</option>
                        <option value="darurat" {{ old('jenis_izin') == 'darurat' ? 'selected' : '' }}>Darurat</option>
                        <option value="lainnya" {{ old('jenis_izin') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('jenis_izin')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="menunggu" {{ old('status') == 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="disetujui" {{ old('status') == 'disetujui' ? 'selected' : '' }}>Disetujui (+ Buat Jadwal)</option>
                        <option value="ditolak" {{ old('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('tanggal_mulai') }}">
                    @error('tanggal_mulai')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('tanggal_selesai') }}">
                    @error('tanggal_selesai')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                    <textarea name="alasan" required rows="4"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Jelaskan alasan permohonan izin...">{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Opsional)</label>
                    <input type="file" name="lampiran" accept="image/*,application/pdf"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, PDF. Maksimal 2MB.</p>
                    @error('lampiran')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal('addModal')" 
                        class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-semibold text-gray-900">Export Data Izin</h3>
                <button onclick="closeModal('exportModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.izin.export') }}" method="POST" class="mt-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin</label>
                        <select name="jenis_izin" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Jenis</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti_tahunan">Cuti Tahunan</option>
                            <option value="keperluan_pribadi">Keperluan Pribadi</option>
                            <option value="darurat">Darurat</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                    <button type="button" onclick="closeModal('exportModal')" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Modal - FIXED: menggunakan PATCH method -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Setujui Permohonan Izin</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="approveMessage">
                    Apakah Anda yakin ingin menyetujui permohonan izin ini? Jadwal otomatis akan dibuat.
                </p>
                <form id="approveForm" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin (Opsional)</label>
                        <textarea name="catatan_admin" rows="3" 
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Tambahkan catatan untuk karyawan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex gap-3 justify-center">
                    <button onclick="closeModal('approveModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button onclick="confirmApprove()" 
                            class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-24 hover:bg-green-700 transition-colors">
                        Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal - FIXED: menggunakan PATCH method -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Tolak Permohonan Izin</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="rejectMessage">
                    Apakah Anda yakin ingin menolak permohonan izin ini?
                </p>
                <form id="rejectForm" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan *</label>
                        <textarea name="catatan_admin" rows="3" required
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex gap-3 justify-center">
                    <button onclick="closeModal('rejectModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button onclick="confirmReject()" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 transition-colors">
                        Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Approval Modal - FIXED: menggunakan PATCH method -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11H6m0 0l3-3m-3 3l3 3m5-6v6a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h3"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Batalkan Persetujuan Izin</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Apakah Anda yakin ingin membatalkan persetujuan izin ini? Jadwal otomatis akan dihapus.
                </p>
                <form id="cancelForm" method="POST" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan *</label>
                        <textarea name="alasan_pembatalan" rows="3" required
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Jelaskan alasan pembatalan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex gap-3 justify-center">
                    <button onclick="closeModal('cancelModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button onclick="confirmCancel()" 
                            class="px-4 py-2 bg-orange-600 text-white text-base font-medium rounded-md w-24 hover:bg-orange-700 transition-colors">
                        Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show/Detail Modal -->
<div id="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Detail Permohonan Izin</h3>
            <button onclick="closeModal('showModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="showModalContent" class="mt-4 max-h-96 overflow-y-auto">
            <!-- Content will be loaded dynamically -->
        </div>
        
        <div id="showModalActions" class="flex justify-between items-center mt-6 pt-4 border-t">
            <!-- Actions will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-2xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Edit Permohonan Izin</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div id="editModalContent" class="mt-4">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Hapus Permohonan Izin</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="deleteMessage">
                    Apakah Anda yakin ingin menghapus permohonan izin ini?
                </p>
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                Tindakan ini tidak dapat dibatalkan!
                            </p>
                            <p class="mt-1 text-sm text-red-700">
                                Data permohonan izin dan jadwal otomatis akan dihapus permanen.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex gap-3 justify-center">
                    <button onclick="closeModal('deleteModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button onclick="confirmDelete()" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Form (Hidden) -->
<form id="bulkActionForm" action="{{ route('admin.izin.bulk-action') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionType">
    <input type="hidden" name="catatan_admin" id="bulkCatatanAdmin">
    <div id="bulkActionIzin"></div>
</form>

<!-- Hidden Forms for Actions -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // Global variables
    let currentIzinId = null;
    let currentAction = null;

    // Define route templates untuk digunakan di JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        window.izinRoutes = {
            show: "{{ route('admin.izin.json', ':id') }}",
            approve: "{{ route('admin.izin.approve', ':id') }}",  
            reject: "{{ route('admin.izin.reject', ':id') }}",
            cancel: "{{ route('admin.izin.cancel', ':id') }}",
            update: "{{ route('admin.izin.update', ':id') }}",
            destroy: "{{ route('admin.izin.destroy', ':id') }}"
        };
    });

    // Helper function untuk membuat URL dengan ID
    function getIzinRoute(routeName, izinId) {
        return window.izinRoutes[routeName].replace(':id', izinId);
    }

    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openExportModal() {
        openModal('exportModal');
    }

    // Show izin details
    function showModal(izinId) {
        // Fetch dari route JSON dengan URL yang benar
        fetch(getIzinRoute('show', izinId))
            .then(response => response.json())
            .then(data => {
                const content = `
                    <div class="space-y-6">
                        <!-- Employee Header -->
                        <div class="flex items-start gap-4 pb-6 border-b border-gray-200">
                            <img class="w-16 h-16 rounded-full object-cover" 
                                 src="${data.user.foto_url}" 
                                 alt="${data.user.name}"
                                 onerror="this.src='{{ asset("images/default-avatar.png") }}'">
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900">${data.user.name}</h2>
                                <p class="text-gray-600 font-mono">${data.user.id_karyawan}</p>
                                <div class="mt-2">
                                    ${getStatusBadge(data.status)}
                                </div>
                            </div>
                        </div>

                        <!-- Leave Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Izin</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Jenis Izin</label>
                                        <p class="text-sm text-gray-900">${data.jenis_izin_text}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Tanggal Mulai</label>
                                        <p class="text-sm text-gray-900">${data.tanggal_mulai}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Tanggal Selesai</label>
                                        <p class="text-sm text-gray-900">${data.tanggal_selesai}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Durasi</label>
                                        <p class="text-sm text-gray-900">${data.durasi_text}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Permohonan</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Tanggal Pengajuan</label>
                                        <p class="text-sm text-gray-900">${data.created_at}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Alasan</label>
                                        <p class="text-sm text-gray-900">${data.alasan}</p>
                                    </div>
                                    ${data.lampiran_url ? `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Lampiran</label>
                                        <a href="${data.lampiran_url}" target="_blank" 
                                           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            Lihat Lampiran
                                        </a>
                                    </div>
                                    ` : ''}
                                    ${data.catatan_admin ? `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Catatan Admin</label>
                                        <p class="text-sm text-gray-900">${data.catatan_admin}</p>
                                    </div>
                                    ` : ''}
                                    ${data.approver ? `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Diproses Oleh</label>
                                        <p class="text-sm text-gray-900">${data.approver.name}</p>
                                    </div>
                                    ` : ''}
                                    ${data.tanggal_persetujuan ? `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Tanggal Persetujuan</label>
                                        <p class="text-sm text-gray-900">${data.tanggal_persetujuan}</p>
                                    </div>
                                    ` : ''}
                                    ${data.status === 'disetujui' ? `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Jadwal Otomatis</label>
                                        <p class="text-sm text-green-600 font-medium">✓ Sudah dibuat untuk ${data.total_hari} hari</p>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                let actions = '';
                if (data.is_menunggu) {
                    actions = `
                        <div class="flex gap-2">
                            <button onclick="deleteIzin('${izinId}')" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="rejectIzin('${izinId}')" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                Tolak
                            </button>
                            <button onclick="approveIzin('${izinId}')" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                Setujui + Buat Jadwal
                            </button>
                            <button onclick="closeModal('showModal'); editModal('${izinId}')" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                Edit
                            </button>
                        </div>
                    `;
                } else {
                    actions = `
                        <button onclick="deleteIzin('${izinId}')" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                        <div class="flex gap-2">
                            ${data.status === 'disetujui' ? `
                                <button onclick="cancelApproval('${izinId}')" 
                                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    Batalkan Approval
                                </button>
                            ` : ''}
                            <div class="text-sm text-gray-500">
                                Permohonan sudah diproses
                            </div>
                        </div>
                    `;
                }

                document.getElementById('showModalContent').innerHTML = content;
                document.getElementById('showModalActions').innerHTML = actions;
                openModal('showModal');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat detail izin');
            });
    }

    // Edit izin modal - FIXED: menggunakan helper function dan route yang benar
    function editModal(izinId) {
        // Fetch data untuk edit form dengan URL yang benar
        fetch(getIzinRoute('show', izinId))
            .then(response => response.json())
            .then(data => {
                const content = `
                    <form action="${getIzinRoute('update', izinId)}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto px-1">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin *</label>
                                <select name="jenis_izin" required
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="sakit" ${data.jenis_izin === 'sakit' ? 'selected' : ''}>Sakit</option>
                                    <option value="cuti_tahunan" ${data.jenis_izin === 'cuti_tahunan' ? 'selected' : ''}>Cuti Tahunan</option>
                                    <option value="keperluan_pribadi" ${data.jenis_izin === 'keperluan_pribadi' ? 'selected' : ''}>Keperluan Pribadi</option>
                                    <option value="darurat" ${data.jenis_izin === 'darurat' ? 'selected' : ''}>Darurat</option>
                                    <option value="lainnya" ${data.jenis_izin === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="menunggu" ${data.status === 'menunggu' ? 'selected' : ''}>Menunggu Persetujuan</option>
                                    <option value="disetujui" ${data.status === 'disetujui' ? 'selected' : ''}>Disetujui (+ Buat Jadwal)</option>
                                    <option value="ditolak" ${data.status === 'ditolak' ? 'selected' : ''}>Ditolak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                                <input type="date" name="tanggal_mulai" value="${data.tanggal_mulai.split('/').reverse().join('-')}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                                <input type="date" name="tanggal_selesai" value="${data.tanggal_selesai.split('/').reverse().join('-')}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                                <textarea name="alasan" required rows="4"
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Jelaskan alasan permohonan izin...">${data.alasan}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran Baru (Opsional)</label>
                                <input type="file" name="lampiran" accept="image/*,application/pdf"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, PDF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</p>
                                ${data.lampiran_url ? `<p class="text-xs text-blue-600 mt-1">File saat ini: <a href="${data.lampiran_url}" target="_blank" class="underline">Lihat lampiran</a></p>` : ''}
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-6 pt-4 border-t">
                            <button type="button" onclick="closeModal('editModal')" 
                                    class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                Update
                            </button>
                        </div>
                    </form>
                `;

                document.getElementById('editModalContent').innerHTML = content;
                openModal('editModal');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat data untuk edit');
            });
    }

    // Approve izin - FIXED: menggunakan helper function
    function approveIzin(izinId) {
        currentIzinId = izinId;
        currentAction = 'approve';
        document.getElementById('approveForm').action = getIzinRoute('approve', izinId);
        openModal('approveModal');
    }

    function confirmApprove() {
        document.getElementById('approveForm').submit();
    }

    // Reject izin - FIXED: menggunakan helper function
    function rejectIzin(izinId) {
        currentIzinId = izinId;
        currentAction = 'reject';
        document.getElementById('rejectForm').action = getIzinRoute('reject', izinId);
        openModal('rejectModal');
    }

    function confirmReject() {
        const form = document.getElementById('rejectForm');
        const textarea = form.querySelector('textarea[name="catatan_admin"]');
        
        if (!textarea.value.trim()) {
            alert('Alasan penolakan harus diisi!');
            textarea.focus();
            return;
        }
        
        form.submit();
    }

    // Cancel approval - FIXED: menggunakan helper function
    function cancelApproval(izinId) {
        currentIzinId = izinId;
        document.getElementById('cancelForm').action = getIzinRoute('cancel', izinId);
        openModal('cancelModal');
    }

    function confirmCancel() {
        const form = document.getElementById('cancelForm');
        const textarea = form.querySelector('textarea[name="alasan_pembatalan"]');
        
        if (!textarea.value.trim()) {
            alert('Alasan pembatalan harus diisi!');
            textarea.focus();
            return;
        }
        
        form.submit();
    }

    // Delete izin - FIXED: menggunakan helper function
    function deleteIzin(izinId) {
        currentIzinId = izinId;
        openModal('deleteModal');
    }

    function confirmDelete() {
        const form = document.getElementById('deleteForm');
        form.action = getIzinRoute('destroy', currentIzinId);
        form.submit();
    }

    // Bulk actions functionality - hanya untuk tab pending
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllDesktopCheckbox = document.getElementById('selectAllDesktop');
        const izinCheckboxes = document.querySelectorAll('.izin-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const bulkActionsElement = document.getElementById('bulkActions');

        // Function to update selected count and show/hide bulk actions
        function updateBulkActions() {
            if (!izinCheckboxes.length) return;
            
            const selectedCheckboxes = document.querySelectorAll('.izin-checkbox:checked');
            const count = selectedCheckboxes.length;
            
            if (selectedCountElement) {
                selectedCountElement.textContent = `${count} terpilih`;
            }
            
            if (bulkActionsElement) {
                if (count > 0) {
                    bulkActionsElement.classList.remove('hidden');
                } else {
                    bulkActionsElement.classList.add('hidden');
                }
            }
        }

        // Handle select all functionality
        function handleSelectAll(checked) {
            izinCheckboxes.forEach(checkbox => {
                checkbox.checked = checked;
            });
            updateBulkActions();
        }

        // Event listeners for select all checkboxes
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                handleSelectAll(this.checked);
                if (selectAllDesktopCheckbox) {
                    selectAllDesktopCheckbox.checked = this.checked;
                }
            });
        }

        if (selectAllDesktopCheckbox) {
            selectAllDesktopCheckbox.addEventListener('change', function() {
                handleSelectAll(this.checked);
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = this.checked;
                }
            });
        }

        // Event listeners for individual checkboxes
        izinCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                
                // Update select all checkboxes state
                const totalCheckboxes = izinCheckboxes.length;
                const checkedCheckboxes = document.querySelectorAll('.izin-checkbox:checked').length;
                
                const allSelected = checkedCheckboxes === totalCheckboxes;
                const someSelected = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allSelected;
                    selectAllCheckbox.indeterminate = someSelected;
                }
                
                if (selectAllDesktopCheckbox) {
                    selectAllDesktopCheckbox.checked = allSelected;
                    selectAllDesktopCheckbox.indeterminate = someSelected;
                }
            });
        });
    });

    // Bulk action function
    function bulkAction(action) {
        const selectedCheckboxes = document.querySelectorAll('.izin-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Pilih setidaknya satu permohonan izin!');
            return;
        }
        
        let message = '';
        let needsNote = false;
        
        switch(action) {
            case 'approve':
                message = `Setujui ${selectedCheckboxes.length} permohonan izin terpilih? Jadwal otomatis akan dibuat untuk semua izin yang disetujui.`;
                break;
            case 'reject':
                message = `Tolak ${selectedCheckboxes.length} permohonan izin terpilih?`;
                needsNote = true;
                break;
        }
        
        if (needsNote) {
            const note = prompt(message + '\n\nMasukkan alasan:');
            if (!note || !note.trim()) {
                alert('Alasan harus diisi untuk penolakan!');
                return;
            }
            document.getElementById('bulkCatatanAdmin').value = note.trim();
        } else {
            if (!confirm(message)) return;
        }
        
        // Prepare form
        const form = document.getElementById('bulkActionForm');
        const actionInput = document.getElementById('bulkActionType');
        const izinContainer = document.getElementById('bulkActionIzin');
        
        actionInput.value = action;
        izinContainer.innerHTML = '';
        
        // Add selected izin IDs to form
        selectedCheckboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'leave_request_ids[]';
            input.value = checkbox.value;
            izinContainer.appendChild(input);
        });
        
        // Submit form
        form.submit();
    }

    // Utility function to get status badge
    function getStatusBadge(status) {
        const badges = {
            'menunggu': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><div class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></div>Menunggu</span>',
            'disetujui': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>Disetujui</span>',
            'ditolak': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>Ditolak</span>'
        };
        
        return badges[status] || badges['menunggu'];
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
            event.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('[role="alert"]');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });
    });

    // Form validation enhancement
    document.addEventListener('DOMContentLoaded', function() {
        // Date validation
        const tanggalMulaiInputs = document.querySelectorAll('input[name="tanggal_mulai"]');
        const tanggalSelesaiInputs = document.querySelectorAll('input[name="tanggal_selesai"]');
        
        function validateDates(mulaiInput, selesaiInput) {
            if (mulaiInput.value && selesaiInput.value) {
                const mulai = new Date(mulaiInput.value);
                const selesai = new Date(selesaiInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Validate start date is not in the past
                if (mulai < today) {
                    mulaiInput.classList.add('border-red-500', 'ring-red-500');
                    mulaiInput.setCustomValidity('Tanggal mulai tidak boleh di masa lalu');
                } else {
                    mulaiInput.classList.remove('border-red-500', 'ring-red-500');
                    mulaiInput.setCustomValidity('');
                }
                
                // Validate end date is not before start date
                if (selesai < mulai) {
                    selesaiInput.classList.add('border-red-500', 'ring-red-500');
                    selesaiInput.setCustomValidity('Tanggal selesai harus setelah tanggal mulai');
                } else {
                    selesaiInput.classList.remove('border-red-500', 'ring-red-500');
                    selesaiInput.setCustomValidity('');
                }
            }
        }

        tanggalMulaiInputs.forEach((mulaiInput, index) => {
            const selesaiInput = tanggalSelesaiInputs[index];
            if (selesaiInput) {
                mulaiInput.addEventListener('change', () => validateDates(mulaiInput, selesaiInput));
                selesaiInput.addEventListener('change', () => validateDates(mulaiInput, selesaiInput));
            }
        });

        // File upload validation
        const fileInputs = document.querySelectorAll('input[type="file"][name="lampiran"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file size (2MB for izin)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File terlalu besar! Maksimal 2MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Format file tidak didukung! Gunakan JPEG, PNG, atau PDF.');
                        this.value = '';
                        return;
                    }
                }
            });
        });

        // Status change warning
        const statusSelects = document.querySelectorAll('select[name="status"]');
        statusSelects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.value === 'disetujui') {
                    if (!confirm('Mengubah status menjadi "Disetujui" akan secara otomatis membuat jadwal absensi untuk periode izin. Lanjutkan?')) {
                        this.value = 'menunggu'; // Reset ke menunggu
                        return;
                    }
                }
            });
        });
    });

    // Auto-show add modal if there are validation errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openModal('addModal');
        });
    @endif

    // Tab functionality - maintain state
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight active tab based on current selection
        const currentTab = '{{ $tab }}';
        console.log('Current tab:', currentTab);
        
        // Auto-refresh notification count every 30 seconds for pending tab
        @if($tab === 'pending')
            setInterval(function() {
                fetch('{{ route("admin.izin.index", ["tab" => "pending"]) }}')
                    .then(response => response.text())
                    .then(html => {
                        // Update pending count in tab if changed
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newCount = doc.querySelector('[data-pending-count]');
                        const currentCount = document.querySelector('[data-pending-count]');
                        
                        if (newCount && currentCount && newCount.textContent !== currentCount.textContent) {
                            // Show notification of new pending requests
                            const notification = document.createElement('div');
                            notification.className = 'fixed top-4 right-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg shadow-lg z-50';
                            notification.innerHTML = `
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Ada permohonan izin baru yang perlu disetujui!</span>
                                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            `;
                            document.body.appendChild(notification);
                            
                            // Auto remove after 5 seconds
                            setTimeout(() => notification.remove(), 5000);
                        }
                    })
                    .catch(error => console.log('Auto-refresh error:', error));
            }, 30000); // Check every 30 seconds
        @endif
    });
</script>
@endpush