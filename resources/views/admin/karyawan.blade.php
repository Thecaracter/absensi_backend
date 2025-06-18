@extends('layouts.app')

@section('title', 'Manajemen Karyawan')

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
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Manajemen Karyawan</h1>
            <p class="text-sm md:text-base text-gray-600 mt-1">Kelola data karyawan perusahaan</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <button onclick="openModal('addModal')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="hidden sm:inline">Tambah Karyawan</span>
                <span class="sm:hidden">Tambah</span>
            </button>
            <a href="{{ route('admin.karyawan.export') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="hidden sm:inline">Export</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Total Karyawan</p>
                    <p class="text-lg md:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-gray-600">Karyawan Aktif</p>
                    <p class="text-lg md:text-2xl font-bold text-green-600">{{ $stats['aktif'] }}</p>
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
                    <p class="text-xs md:text-sm font-medium text-gray-600">Karyawan Nonaktif</p>
                    <p class="text-lg md:text-2xl font-bold text-red-600">{{ $stats['nonaktif'] }}</p>
                </div>
                <div class="bg-red-100 p-2 md:p-3 rounded-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <form method="GET" action="{{ route('admin.karyawan.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Nama, ID, Email, No HP..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- HAPUS FILTER SHIFT -->
                <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.karyawan.index') }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg transition-colors text-sm">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Bulk Actions -->
        @if($karyawan->count() > 0)
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
                        <button onclick="bulkAction('activate')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                            Aktifkan
                        </button>
                        <button onclick="bulkAction('deactivate')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm transition-colors">
                            Nonaktifkan
                        </button>
                        <button onclick="bulkAction('delete')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Employee List - Mobile Cards -->
    <div class="block lg:hidden space-y-4">
        @forelse($karyawan as $employee)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                               class="employee-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mr-2">
                        <img class="h-12 w-12 rounded-full object-cover flex-shrink-0" 
                             src="{{ $employee->foto_url ?? asset('images/default-avatar.png') }}" 
                             alt="{{ $employee->name }}"
                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $employee->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $employee->id_karyawan }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $employee->email }}</p>
                                <p class="text-xs text-gray-600">{{ $employee->no_hp }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="showModal('{{ $employee->id }}')" 
                                        class="text-blue-600 hover:text-blue-900 p-1" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button onclick="editModal('{{ $employee->id }}')" 
                                        class="text-indigo-600 hover:text-indigo-900 p-1" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="deleteEmployee('{{ $employee->id }}', '{{ $employee->name }}')" 
                                        class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded" 
                                        title="Hapus Karyawan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <form id="deleteForm{{ $employee->id }}" action="{{ route('admin.karyawan.destroy', $employee->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <!-- HAPUS SHIFT BADGE -->
                            @if($employee->status == 'aktif')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Bergabung: {{ $employee->tanggal_masuk->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada karyawan</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status']))
                        Tidak ada karyawan yang sesuai dengan filter.
                    @else
                        Mulai dengan menambahkan karyawan baru.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.karyawan.index') }}" 
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
                            Tambah Karyawan
                        </button>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Employee List - Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Karyawan</h3>
            <div class="text-sm text-gray-500">
                Total: {{ $karyawan->total() }} karyawan
            </div>
        </div>
        
        @if($karyawan->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="selectAllDesktop" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No HP</th>
                            <!-- HAPUS KOLOM SHIFT -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($karyawan as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                           class="employee-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $employee->foto_url ?? asset('images/default-avatar.png') }}" 
                                             alt="{{ $employee->name }}"
                                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $employee->tanggal_masuk->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">{{ $employee->id_karyawan }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $employee->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $employee->no_hp }}</div>
                                </td>
                                <!-- HAPUS KOLOM SHIFT DATA -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($employee->status == 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="showModal('{{ $employee->id }}')" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors p-1 hover:bg-blue-50 rounded" 
                                                title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button onclick="editModal('{{ $employee->id }}')" 
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors p-1 hover:bg-indigo-50 rounded" 
                                                title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button onclick="deleteEmployee('{{ $employee->id }}', '{{ $employee->name }}')" 
                                                class="text-red-600 hover:text-red-900 transition-colors p-1 hover:bg-red-50 rounded" 
                                                title="Hapus Karyawan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                        <form id="deleteFormDesktop{{ $employee->id }}" action="{{ route('admin.karyawan.destroy', $employee->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada karyawan</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status']))
                        Tidak ada karyawan yang sesuai dengan filter yang dipilih.
                    @else
                        Mulai dengan menambahkan karyawan baru ke sistem.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.karyawan.index') }}" 
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
                            Tambah Karyawan
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($karyawan->hasPages())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-700">
                    <span>Menampilkan</span>
                    <span class="font-medium mx-1">{{ $karyawan->firstItem() }}</span>
                    <span>sampai</span>
                    <span class="font-medium mx-1">{{ $karyawan->lastItem() }}</span>
                    <span>dari</span>
                    <span class="font-medium mx-1">{{ $karyawan->total() }}</span>
                    <span>karyawan</span>
                </div>
                <div>
                    {{ $karyawan->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Karyawan Baru</h3>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.karyawan.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto px-1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID Karyawan *</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               name="id_karyawan" 
                               id="auto_id_karyawan"
                               required
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('id_karyawan', $nextEmployeeId ?? '') }}"
                               placeholder="EMP001">
                        <button type="button" 
                                onclick="generateNewId()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-1"
                                title="Generate ID Baru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Klik tombol refresh untuk generate ID baru</p>
                    @error('id_karyawan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('name') }}" placeholder="Masukkan nama lengkap">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('email') }}" placeholder="contoh@email.com">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No HP *</label>
                    <input type="text" name="no_hp" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                    @error('no_hp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- HAPUS FIELD SHIFT -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}">
                    @error('tanggal_masuk')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
                    <textarea name="alamat" required rows="3"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Minimal 6 karakter">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ulangi password">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <input type="file" name="foto" accept="image/*"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimal 2MB.</p>
                    @error('foto')
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

<!-- Show Modal for Employee Details -->
@foreach($karyawan as $employee)
<div id="showModal{{ $employee->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Detail Karyawan</h3>
            <button onclick="closeModal('showModal{{ $employee->id }}')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="mt-4 max-h-96 overflow-y-auto">
            <!-- Employee Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pb-6 border-b border-gray-200">
                <img class="w-20 h-20 rounded-full object-cover mx-auto sm:mx-0" 
                     src="{{ $employee->foto_url ?? asset('images/default-avatar.png') }}" 
                     alt="{{ $employee->name }}"
                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                <div class="text-center sm:text-left flex-1">
                    <h2 class="text-xl font-bold text-gray-900">{{ $employee->name }}</h2>
                    <p class="text-gray-600 font-mono">{{ $employee->id_karyawan }}</p>
                    <div class="flex items-center justify-center sm:justify-start gap-2 mt-2 flex-wrap">
                        @if($employee->status == 'aktif')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                Nonaktif
                            </span>
                        @endif
                        <!-- HAPUS SHIFT BADGE -->
                    </div>
                </div>
            </div>

            <!-- Employee Details -->
            <div class="py-6 space-y-6">
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Personal</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-sm text-gray-900">{{ $employee->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">No HP</label>
                            <p class="text-sm text-gray-900">{{ $employee->no_hp }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tanggal Masuk</label>
                            <p class="text-sm text-gray-900">{{ $employee->tanggal_masuk->format('d F Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Masa Kerja</label>
                            <p class="text-sm text-gray-900">{{ $employee->tanggal_masuk->diffForHumans() }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Alamat</label>
                            <p class="text-sm text-gray-900">{{ $employee->alamat }}</p>
                        </div>
                    </div>
                </div>

                <!-- HAPUS SHIFT INFORMATION SECTION -->

                <!-- Statistics Bulan Ini -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Bulan Ini</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600">0</div>
                            <div class="text-sm text-blue-800">Hari Kerja</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-600">0</div>
                            <div class="text-sm text-yellow-800">Terlambat</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600">0</div>
                            <div class="text-sm text-green-800">Izin Disetujui</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex justify-between items-center mt-6 pt-4 border-t">
            <button onclick="deleteEmployee('{{ $employee->id }}', '{{ $employee->name }}')" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Karyawan
            </button>
            <button onclick="closeModal('showModal{{ $employee->id }}'); editModal('{{ $employee->id }}')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                Edit Karyawan
            </button>
        </div>
    </div>
</div>
@endforeach

<!-- Edit Modal for Each Employee -->
@foreach($karyawan as $employee)
<div id="editModal{{ $employee->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-lg bg-white my-8">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Edit Karyawan: {{ $employee->name }}</h3>
            <button onclick="closeModal('editModal{{ $employee->id }}')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.karyawan.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto px-1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID Karyawan *</label>
                    <input type="text" name="id_karyawan" value="{{ $employee->id_karyawan }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ $employee->name }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ $employee->email }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No HP *</label>
                    <input type="text" name="no_hp" value="{{ $employee->no_hp }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <!-- HAPUS FIELD SHIFT -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="aktif" {{ $employee->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ $employee->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk *</label>
                    <input type="date" name="tanggal_masuk" value="{{ $employee->tanggal_masuk->format('Y-m-d') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
                    <textarea name="alamat" required rows="3"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $employee->alamat }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                    <input type="password" name="password"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Kosongkan jika tidak ingin mengubah">
                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ulangi password baru">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <div class="flex items-center gap-4">
                        @if($employee->foto_url)
                            <img src="{{ $employee->foto_url }}" alt="Current photo" class="w-16 h-16 rounded-full object-cover">
                        @endif
                        <div class="flex-1">
                            <input type="file" name="foto" accept="image/*"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal('editModal{{ $employee->id }}')" 
                        class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Hapus Karyawan</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="deleteMessage">
                    Apakah Anda yakin ingin menghapus karyawan ini?
                </p>
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Data yang akan dihapus:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Data personal karyawan</li>
                                    <li>Semua riwayat absensi</li>
                                    <li>Semua data izin/cuti</li>
                                    <li>File foto profil</li>
                                </ul>
                            </div>
                            <p class="mt-2 text-sm font-medium text-red-800">
                                Tindakan ini tidak dapat dibatalkan!
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
<form id="bulkActionForm" action="{{ route('admin.karyawan.bulk-action') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionType">
    <div id="bulkActionEmployees"></div>
</form>

@endsection

@push('scripts')
<script>
    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Generate new employee ID via AJAX
    function generateNewId() {
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
        button.disabled = true;
        
        fetch('{{ route("admin.karyawan.generate-id") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('auto_id_karyawan').value = data.id_karyawan;
                // Show success feedback
                const input = document.getElementById('auto_id_karyawan');
                input.classList.add('ring-2', 'ring-green-500');
                setTimeout(() => {
                    input.classList.remove('ring-2', 'ring-green-500');
                }, 1000);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal generate ID baru. Silakan coba lagi.');
            })
            .finally(() => {
                // Restore button
                button.innerHTML = originalContent;
                button.disabled = false;
            });
    }

    // Delete functionality
    let employeeToDelete = null;
    
    function deleteEmployee(employeeId, employeeName) {
        employeeToDelete = employeeId;
        document.getElementById('deleteMessage').innerHTML = 
            `Apakah Anda yakin ingin menghapus karyawan <strong>"${employeeName}"</strong>?`;
        openModal('deleteModal');
    }
    
    function confirmDelete() {
        if (employeeToDelete) {
            // Check if we're on mobile or desktop and submit the appropriate form
            const mobileForm = document.getElementById('deleteForm' + employeeToDelete);
            const desktopForm = document.getElementById('deleteFormDesktop' + employeeToDelete);
            
            if (mobileForm) {
                mobileForm.submit();
            } else if (desktopForm) {
                desktopForm.submit();
            }
        }
        closeModal('deleteModal');
    }

    // Show employee details modal
    function showModal(employeeId) {
        openModal('showModal' + employeeId);
    }

    // Show edit employee modal
    function editModal(employeeId) {
        openModal('editModal' + employeeId);
    }

    // Bulk actions functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllDesktopCheckbox = document.getElementById('selectAllDesktop');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
        const selectedCountElement = document.getElementById('selectedCount');
        const bulkActionsElement = document.getElementById('bulkActions');

        // Function to update selected count and show/hide bulk actions
        function updateBulkActions() {
            const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
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
            employeeCheckboxes.forEach(checkbox => {
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
        employeeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                
                // Update select all checkboxes state
                const totalCheckboxes = employeeCheckboxes.length;
                const checkedCheckboxes = document.querySelectorAll('.employee-checkbox:checked').length;
                
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

        // Set initial ID from backend
        const autoIdField = document.getElementById('auto_id_karyawan');
        if (autoIdField && !autoIdField.value) {
            autoIdField.value = '{{ $nextEmployeeId }}';
        }
    });

    // Bulk action function
    function bulkAction(action) {
        const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Pilih setidaknya satu karyawan!');
            return;
        }
        
        let message = '';
        let confirmAction = false;
        
        switch(action) {
            case 'activate':
                message = `Aktifkan ${selectedCheckboxes.length} karyawan terpilih?`;
                confirmAction = true;
                break;
            case 'deactivate':
                message = `Nonaktifkan ${selectedCheckboxes.length} karyawan terpilih?`;
                confirmAction = true;
                break;
            case 'delete':
                message = `Hapus ${selectedCheckboxes.length} karyawan terpilih?\n\nPerhatian: Tindakan ini akan menghapus:\n- Data karyawan\n- Semua data absensi\n- Semua data izin\n- File foto profil\n\nTindakan ini tidak dapat dibatalkan!`;
                confirmAction = confirm(message);
                break;
        }
        
        if (action !== 'delete') {
            confirmAction = confirm(message);
        }
        
        if (!confirmAction) return;
        
        // Prepare form
        const form = document.getElementById('bulkActionForm');
        const actionInput = document.getElementById('bulkActionType');
        const employeesContainer = document.getElementById('bulkActionEmployees');
        
        actionInput.value = action;
        employeesContainer.innerHTML = '';
        
        // Add selected employee IDs to form
        selectedCheckboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = checkbox.value;
            employeesContainer.appendChild(input);
        });
        
        // Submit form
        form.submit();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
            event.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Handle validation errors - auto-show modals
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            @if(!request()->has('employee_id'))
                // Show add modal if no specific employee ID
                openModal('addModal');
            @else
                // Show edit modal for specific employee
                openModal('editModal{{ request()->get("employee_id") }}');
            @endif
        });
    @endif

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
        // Add real-time validation for email
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('border-red-500', 'ring-red-500');
                } else {
                    this.classList.remove('border-red-500', 'ring-red-500');
                }
            });
        });

        // Add real-time validation for phone numbers
        const phoneInputs = document.querySelectorAll('input[name="no_hp"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9+]/g, '');
                
                // Basic Indonesian phone number validation
                if (this.value && this.value.length > 0) {
                    if (!this.value.match(/^(\+62|62|0)/)) {
                        this.classList.add('border-yellow-500', 'ring-yellow-500');
                    } else {
                        this.classList.remove('border-yellow-500', 'ring-yellow-500');
                    }
                }
            });
        });

        // Password confirmation validation
        const passwordInputs = document.querySelectorAll('input[name="password"]');
        const confirmPasswordInputs = document.querySelectorAll('input[name="password_confirmation"]');
        
        function validatePasswordMatch(passwordField, confirmField) {
            if (passwordField.value && confirmField.value) {
                if (passwordField.value !== confirmField.value) {
                    confirmField.classList.add('border-red-500', 'ring-red-500');
                    confirmField.setCustomValidity('Password tidak cocok');
                } else {
                    confirmField.classList.remove('border-red-500', 'ring-red-500');
                    confirmField.setCustomValidity('');
                }
            }
        }

        passwordInputs.forEach((passwordInput, index) => {
            const confirmInput = confirmPasswordInputs[index];
            if (confirmInput) {
                passwordInput.addEventListener('input', () => validatePasswordMatch(passwordInput, confirmInput));
                confirmInput.addEventListener('input', () => validatePasswordMatch(passwordInput, confirmInput));
            }
        });
    });

    // Enhanced file upload preview
    document.addEventListener('DOMContentLoaded', function() {
        const fileInputs = document.querySelectorAll('input[type="file"][name="foto"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File terlalu besar! Maksimal 2MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    if (!file.type.match(/^image\/(jpeg|jpg|png)$/)) {
                        alert('Format file tidak didukung! Gunakan JPEG, JPG, atau PNG.');
                        this.value = '';
                        return;
                    }
                    
                    // Show preview if in edit modal
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = input.closest('div').querySelector('img');
                        if (preview) {
                            preview.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    });
</script>
@endpush