<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Sistem Absensi</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional Styles -->
    @stack('styles')
    
    <style>
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #e2e8f0 #f8fafc;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
        
        .sidebar-transition {
            transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }
        
        .content-shift {
            transition: margin-left 0.3s ease-in-out;
        }
        
        @media (max-width: 1023px) {
            .sidebar-hidden {
                transform: translateX(-100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar Component -->
        @include('components.sidebar')
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:ml-64 content-shift">
            <!-- Header Component -->
            @include('components.header')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-hidden">
                <div class="h-full overflow-y-auto scrollbar-thin">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mx-4 mt-4 lg:mx-6 lg:mt-6">
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mx-4 mt-4 lg:mx-6 lg:mt-6">
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mx-4 mt-4 lg:mx-6 lg:mt-6">
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        @foreach($errors->all() as $error)
                                            <p class="text-red-700 font-medium text-sm">{{ $error }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page Content -->
                    <div class="p-4 lg:p-6">
                        @yield('content')
                    </div>
                </div>
            </main>
            
            <!-- Footer Component -->
            @include('components.footer')
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"
         onclick="toggleSidebar()"></div>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const body = document.body;
            
            if (sidebar.classList.contains('sidebar-hidden')) {
                // Show sidebar
                sidebar.classList.remove('sidebar-hidden');
                overlay.classList.remove('hidden');
                body.classList.add('overflow-hidden');
            } else {
                // Hide sidebar
                sidebar.classList.add('sidebar-hidden');
                overlay.classList.add('hidden');
                body.classList.remove('overflow-hidden');
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            
            // Initialize sidebar as hidden on mobile
            if (window.innerWidth < 1024) {
                sidebar.classList.add('sidebar-hidden');
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const body = document.body;
                
                if (window.innerWidth >= 1024) {
                    // Desktop: show sidebar, hide overlay
                    sidebar.classList.remove('sidebar-hidden');
                    overlay.classList.add('hidden');
                    body.classList.remove('overflow-hidden');
                } else {
                    // Mobile: hide sidebar if not already hidden
                    if (!sidebar.classList.contains('sidebar-hidden')) {
                        sidebar.classList.add('sidebar-hidden');
                        overlay.classList.add('hidden');
                        body.classList.remove('overflow-hidden');
                    }
                }
            });
        });

        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>