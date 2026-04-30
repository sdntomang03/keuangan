<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistem Keuangan Sekolah</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Kustomisasi Scrollbar untuk Sidebar & Konten */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }

        .dark .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #475569;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100 dark:bg-gray-900 dark:text-gray-100 overflow-hidden">

    <!-- WRAPPER UTAMA: Alpine.js untuk kontrol Sidebar (Default terbuka jika layar lebar) -->
    <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="flex h-screen w-full bg-gray-100 dark:bg-gray-900">

        <!-- ==========================================
             1. PANGGIL SIDEBAR KIRI
             ========================================== -->
        @include('layouts.navigation')

        <!-- ==========================================
             2. KOLOM KANAN (TOPBAR + KONTEN UTAMA)
             ========================================== -->
        <div class="flex flex-col flex-1 w-full min-w-0 transition-all duration-300 ease-in-out">

            <!-- TOPBAR (NAVBAR ATAS) -->
            <header
                class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 z-30 h-16 shrink-0 flex items-center justify-between px-4 sm:px-6 lg:px-8">

                <!-- Kiri: Tombol Toggle Sidebar -->
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-indigo-600 focus:outline-none p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <!-- Judul Mobile Opsional -->
                    <span class="ml-4 font-bold text-gray-800 dark:text-gray-200 lg:hidden tracking-tight">SIAKAD</span>
                </div>

                <!-- Kanan: Pilihan Anggaran & Profil User -->
                <div class="flex items-center space-x-3 sm:space-x-5">

                    <!-- Switcher Anggaran (Sembunyi di HP, Pindah ke Sidebar kalau di HP) -->
                    @if(isset($anggaranAktif) && $anggaranAktif)
                    <div
                        class="hidden sm:flex items-center bg-gray-50 dark:bg-gray-900 rounded-lg p-1 shadow-inner border border-gray-200 dark:border-gray-700">
                        <span class="text-[10px] font-bold uppercase px-3 text-gray-400 dark:text-gray-500">
                            {{ $anggaranAktif->tahun }}
                        </span>
                        <div class="flex space-x-1">
                            @foreach(['bos', 'bop'] as $item)
                            <form method="POST" action="{{ route('anggaran.switch') }}">
                                @csrf
                                <input type="hidden" name="singkatan" value="{{ $item }}">
                                <input type="hidden" name="tahun" value="{{ $anggaranAktif->tahun }}">
                                <button type="submit" @disabled($anggaranAktif->singkatan == $item)
                                    class="px-3 py-1.5 text-[11px] font-extrabold rounded-md transition-all duration-200
                                    {{
                                    $anggaranAktif->singkatan == $item
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                                    {{ strtoupper($item) }}
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Dropdown Akun Profil -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center p-1 border border-transparent rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition">
                                <div
                                    class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-600 border border-indigo-200 flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span
                                    class="hidden md:block ml-2 text-sm font-bold text-gray-700 dark:text-gray-300 mr-1">{{
                                    Auth::user()->name }}</span>
                                <svg class="hidden md:block h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                <div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg> {{ __('Profile') }}</div>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('sekolah.index')">
                                <div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg> {{ __('Pengaturan Anggaran') }}</div>
                            </x-dropdown-link>
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50 font-bold">
                                    <div class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg> {{ __('Log Out') }}</div>
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <!-- KONTEN HALAMAN -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden custom-scroll p-4 sm:p-6 lg:p-8">

                <!-- Page Header Opsional -->
                @isset($header)
                <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    {{ $header }}
                </div>
                @endisset

                {{ $slot }}

            </main>
        </div>
    </div>

    <!-- Alert Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Oops...', text: "{{ session('error') }}" });
        @endif
    </script>
</body>

</html>