<!-- Footer -->
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="px-4 lg:px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
            <!-- Left side - Copyright -->
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span>© {{ date('Y') }} Sistem Absensi Karyawan.</span>
                <span class="hidden md:inline">Semua hak dilindungi.</span>
            </div>

            <!-- Center - Quick Stats (for admin) -->
            @if(auth()->user()->isAdmin())
                <div class="flex items-center space-x-6 text-xs text-gray-500">
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ \App\Models\User::karyawan()->aktif()->count() }} Karyawan Aktif</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ \App\Models\Attendance::hariIni()->count() }} Absensi Hari Ini</span>
                    </div>
                    @php
                        $pendingCount = \App\Models\LeaveRequest::menunggu()->count();
                    @endphp
                    @if($pendingCount > 0)
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $pendingCount }} Izin Menunggu</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Right side - Links & Version -->
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center space-x-3 text-gray-500">
                    <a href="#" class="hover:text-gray-700 transition-colors">Bantuan</a>
                    <span class="text-gray-300">|</span>
                    <a href="#" class="hover:text-gray-700 transition-colors">Kontak</a>
                    <span class="text-gray-300">|</span>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">v1.0.0</span>
                </div>
            </div>
        </div>

        <!-- Bottom row for mobile -->
        <div class="md:hidden mt-3 pt-3 border-t border-gray-200">
            <div class="flex justify-center items-center space-x-4 text-xs text-gray-400">
                <span>Powered by Laravel</span>
                <span class="text-gray-300">•</span>
                <span>Built with ❤️</span>
            </div>
        </div>
    </div>
</footer>

<!-- System Status Indicator (optional) -->
<div class="fixed bottom-4 right-4 z-40">
    <div class="flex items-center space-x-2 bg-white border border-gray-200 rounded-lg shadow-lg px-3 py-2">
        <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs text-gray-600 font-medium">Sistem Online</span>
        </div>
    </div>
</div>