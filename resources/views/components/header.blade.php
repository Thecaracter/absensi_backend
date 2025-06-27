<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
    <div class="px-4 lg:px-6">
        <div class="flex justify-between items-center h-16">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <!-- Mobile menu button -->
                <button type="button" 
                        onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Page Title -->
                <div class="flex items-center space-x-3">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        @hasSection('page-subtitle')
                            <p class="text-sm text-gray-500 mt-0.5">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                
                <!-- Current Time -->
                <div class="hidden md:flex items-center text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded-lg">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="current-time">{{ now()->format('H:i') }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ now()->format('d M Y') }}</span>
                </div>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        </svg>
                        <!-- Notification badge -->
                        @if(auth()->user()->isAdmin())
                            @php
                                $pendingCount = \App\Models\LeaveRequest::menunggu()->count() + 
                                               \App\Models\Attendance::menungguApproval()->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                    {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                                </span>
                            @endif
                        @endif
                    </button>

                    <!-- Notifications dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-1 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-1 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                        </div>
                        
                        <div class="max-h-64 overflow-y-auto">
                            @if(auth()->user()->isAdmin())
                                @php
                                    $pendingLeaves = \App\Models\LeaveRequest::menunggu()->with('user')->take(3)->get();
                                    $pendingAttendances = \App\Models\Attendance::menungguApproval()->with('user')->take(3)->get();
                                @endphp
                                
                                @forelse($pendingLeaves as $leave)
                                    <a href="{{ route('admin.izin.index', $leave) }}" 
                                       class="block p-3 hover:bg-gray-50 border-b border-gray-100">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">Pengajuan Izin Baru</p>
                                                <p class="text-sm text-gray-500 truncate">{{ $leave->user->name }} - {{ $leave->getJenisIzinText() }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $leave->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    @forelse($pendingAttendances as $attendance)
                                        <a href="{{ route('admin.absensi.index', $attendance) }}" 
                                           class="block p-3 hover:bg-gray-50 border-b border-gray-100">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">Absensi Menunggu Approval</p>
                                                    <p class="text-sm text-gray-500 truncate">{{ $attendance->user->name }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ $attendance->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-4 text-center text-gray-500">
                                            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm">Tidak ada notifikasi</p>
                                        </div>
                                    @endforelse
                                @endforelse
                            @else
                                <div class="p-4 text-center text-gray-500">
                                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm">Tidak ada notifikasi</p>
                                </div>
                            @endif
                        </div>
                        
                        @if(auth()->user()->isAdmin() && ($pendingLeaves->count() > 0 || $pendingAttendances->count() > 0))
                            <div class="p-3 border-t border-gray-200 text-center">
                                <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <img class="h-8 w-8 rounded-full object-cover ring-2 ring-gray-200" 
                             src="{{ auth()->user()->foto_url }}" 
                             alt="{{ auth()->user()->name }}">
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Profile dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-1 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-1 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <img class="h-10 w-10 rounded-full object-cover" 
                                     src="{{ auth()->user()->foto_url }}" 
                                     alt="{{ auth()->user()->name }}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                    @if(auth()->user()->isKaryawan())
                                        <p class="text-xs text-blue-600 font-medium">{{ auth()->user()->id_karyawan }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        

                        <!-- Logout -->
                        <div class="border-t border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Update current time every minute
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    }

    // Update time every minute
    setInterval(updateTime, 60000);
    
    // Update immediately on load
    document.addEventListener('DOMContentLoaded', updateTime);
</script>