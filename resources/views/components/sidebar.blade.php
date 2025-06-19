<!-- Sidebar -->
<div id="sidebar" 
     class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-2xl border-r border-gray-100 sidebar-transition lg:translate-x-0 flex flex-col">
    
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
        
        <div class="flex items-center space-x-3 relative z-10">
            <div class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-white text-xl font-bold tracking-wide">AbsensiApp</h1>
                <p class="text-white/80 text-xs font-medium">Management System</p>
            </div>
        </div>
        
        <!-- Close button for mobile -->
        <button onclick="toggleSidebar()" 
                class="lg:hidden p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200 relative z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- User Profile Section -->
    <div class="p-6 bg-gradient-to-b from-gray-50 to-white border-b border-gray-100">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <img class="w-12 h-12 rounded-xl object-cover ring-3 ring-indigo-100 shadow-lg" 
                     src="{{ auth()->user()->foto_url }}" 
                     alt="{{ auth()->user()->name }}">
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full shadow-sm"></div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</h3>
                <p class="text-xs text-gray-500 capitalize font-medium">{{ auth()->user()->role }}</p>
                @if(auth()->user()->isKaryawan())
                    <div class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md bg-indigo-50 text-xs font-medium text-indigo-700">
                        {{ auth()->user()->id_karyawan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin min-h-0">
        
        @if(auth()->user()->isAdmin())
            <!-- Admin Menu -->
            
            <!-- Section: Overview -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Overview</h4>
                
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/20' : 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Dashboard</span>
                    @if(request()->routeIs('admin.dashboard'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>
            </div>

            <!-- Section: Management -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Management</h4>
                
                <!-- Karyawan Management -->
                <a href="{{ route('admin.karyawan.index') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.karyawan.*') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.karyawan.*') ? 'bg-white/20' : 'bg-blue-100 text-blue-600 group-hover:bg-blue-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Kelola Karyawan</span>
                    @if(request()->routeIs('admin.karyawan.*'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>

                <!-- Absensi Management -->
                <a href="{{ route('admin.absensi.index') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.absensi.*') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.absensi.*') ? 'bg-white/20' : 'bg-green-100 text-green-600 group-hover:bg-green-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Data Absensi</span>
                    @if(request()->routeIs('admin.absensi.*'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>

                <!-- Izin/Cuti Management -->
                <a href="{{ route('admin.izin.index') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.izin.*') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.izin.*') ? 'bg-white/20' : 'bg-yellow-100 text-yellow-600 group-hover:bg-yellow-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Izin & Cuti</span>
                    @php $pendingLeaves = \App\Models\LeaveRequest::menunggu()->count(); @endphp
                    @if($pendingLeaves > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                            {{ $pendingLeaves > 9 ? '9+' : $pendingLeaves }}
                        </span>
                    @elseif(request()->routeIs('admin.izin.*'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>

                <!-- Jadwal & Shift -->
                <a href="{{ route('admin.jadwal.index') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.jadwal.*') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.jadwal.*') ? 'bg-white/20' : 'bg-purple-100 text-purple-600 group-hover:bg-purple-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Jadwal & Shift</span>
                    @if(request()->routeIs('admin.jadwal.*'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>
            </div>

            <!-- Section: Reports -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Reports</h4>
                
                <!-- Laporan -->
                <a href="{{ route('admin.laporan.index') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('admin.laporan.*') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('admin.laporan.*') ? 'bg-white/20' : 'bg-red-100 text-red-600 group-hover:bg-red-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Laporan</span>
                    @if(request()->routeIs('admin.laporan.*'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>
            </div>

        @else
            <!-- Karyawan Menu -->
            
            <!-- Section: Overview -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Overview</h4>
                
                <!-- Dashboard Karyawan -->
                <a href="{{ route('karyawan.dashboard') }}" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 {{ request()->routeIs('karyawan.dashboard') ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg transform scale-105' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg {{ request()->routeIs('karyawan.dashboard') ? 'bg-white/20' : 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200' }} transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Dashboard</span>
                    @if(request()->routeIs('karyawan.dashboard'))
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    @endif
                </a>
            </div>

            <!-- Section: Attendance -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Attendance</h4>
                
                <!-- Absensi Saya -->
                <a href="#" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg bg-green-100 text-green-600 group-hover:bg-green-200 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Absensi Saya</span>
                </a>

                <!-- Riwayat Absensi -->
                <a href="#" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Riwayat Absensi</span>
                </a>
            </div>

            <!-- Section: Leave -->
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-2">Leave</h4>
                
                <!-- Pengajuan Izin -->
                <a href="#" 
                   class="nav-link group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-300 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-lg bg-yellow-100 text-yellow-600 group-hover:bg-yellow-200 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="flex-1">Pengajuan Izin</span>
                </a>
            </div>

        @endif
    </nav>

    <!-- Sidebar Footer -->
    <div class="mt-auto px-6 py-4 border-t border-gray-200 bg-gradient-to-t from-gray-50 to-white shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-gray-600">Online</span>
            </div>
            <span class="text-xs text-gray-400 font-medium">v1.0.0</span>
        </div>
    </div>
</div>

<style>
.nav-link {
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.nav-link:hover::before {
    left: 100%;
}

.bg-grid-pattern {
    background-image: 
        linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>