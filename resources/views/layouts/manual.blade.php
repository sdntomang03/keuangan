<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SI-KEUANGAN') }} - Perencanaan Manual</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">

    <div class="relative min-h-screen md:flex">

        @include('layouts.navigation-manual')

        <div class="flex-1 min-h-screen flex flex-col md:ml-64 transition-all duration-300 ease-in-out">

            <header
                class="md:hidden sticky top-0 z-30 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 p-4 flex justify-between items-center shadow-sm">
                <div class="flex items-center">
                    <span
                        class="font-bold text-lg text-indigo-600 uppercase tracking-widest leading-none">SI-KEUANGAN</span>
                </div>
                <button id="mobile-menu-btn"
                    class="p-2 rounded-md text-slate-500 hover:bg-slate-100 focus:outline-none dark:hover:bg-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>

        </div>

        <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden md:hidden" aria-hidden="true"></div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar-manual');
            const overlay = document.getElementById('sidebar-overlay');

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }

            btn?.addEventListener('click', toggleSidebar);
            overlay?.addEventListener('click', toggleSidebar);
        });
    </script>
</body>

</html>