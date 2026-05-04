<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} | TemuLKPP</title>
        <link rel="icon" type="image/png" href="{{ asset('img/icon/logo/logoIcon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-screen overflow-hidden bg-[linear-gradient(145deg,#f4f1eb_0%,#fffdf8_48%,#efe5d3_100%)] text-stone-900">
        <script>
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        </script>
        <div class="flex h-screen">
            <div class="relative flex shrink-0">
                @include('partials.sidebar')
                
                <!-- Floating Toggle Button -->
                <button 
                    onclick="toggleSidebar()"
                    class="sidebar-toggle-btn absolute -right-4 top-10 flex h-8 w-8 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-600 shadow-xl transition-all hover:bg-stone-50 hover:text-red-600 active:scale-90 z-[60]"
                >
                    <svg class="sidebar-rotate-icon h-5 w-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </button>
            </div>

            <main class="min-w-0 flex-1 overflow-y-auto">
                @yield('container')
            </main>
        </div>

        @stack('scripts')
        <script>
            function toggleSidebar() {
                const body = document.body;
                const isCollapsed = body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
                window.dispatchEvent(new Event('resize'));
            }
        </script>
    </body>
</html>
