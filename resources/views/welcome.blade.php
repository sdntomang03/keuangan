<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
        @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-4">
            @auth
            <a href="{{ url('/dashboard') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition">
                Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal transition">
                Log in
            </a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}"
                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition">
                Register
            </a>
            @endif
            @endauth
        </nav>
        @endif
    </header>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow">
        <main
            class="flex max-w-[335px] w-full flex-col lg:max-w-4xl lg:flex-row shadow-xl rounded-lg overflow-hidden border border-gray-100 dark:border-[#3E3E3A]">

            <div class="text-[13px] leading-[20px] flex-1 p-8 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC]">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold mb-2">Sistem Informasi Instansi</h1>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-base">Selamat datang di platform pengelolaan
                        administrasi dan anggaran sekolah terpadu.</p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-sm">Manajemen Sekolah</h3>
                            <p class="text-[#706f6c] dark:text-[#A1A09A]">Kelola data profil, NPSN, hingga legalitas
                                instansi secara digital.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-sm">Kontrol Anggaran</h3>
                            <p class="text-[#706f6c] dark:text-[#A1A09A]">Pantau penggunaan anggaran aktif per triwulan
                                secara real-time.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <a href="{{ route('login') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition duration-200">
                        Mulai Sekarang
                    </a>
                    <a href="#"
                        class="border border-gray-200 dark:border-gray-700 px-6 py-2 rounded-md font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition duration-200">
                        Pelajari Fitur
                    </a>
                </div>
            </div>

            <div
                class="lg:w-[350px] bg-gray-50 dark:bg-[#1b1b18] p-8 lg:p-12 flex flex-col justify-center border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-[#3E3E3A]">
                <div class="relative group">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-lg blur opacity-25 group-hover:opacity-50 transition duration-1000">
                    </div>
                    <div class="relative bg-white dark:bg-[#161615] p-6 rounded-lg shadow-sm">
                        <div class="text-xs font-bold text-indigo-600 uppercase mb-2">Status Sistem</div>
                        <div class="text-2xl font-bold mb-1">2026</div>
                        <div class="text-[#706f6c] dark:text-[#A1A09A] leading-tight">Tahun Anggaran Berjalan
                            Terintegrasi.</div>
                    </div>
                </div>

                <div class="mt-8">
                    <p
                        class="text-[11px] text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider font-bold mb-4 text-center lg:text-left">
                        Didukung Oleh</p>
                    <div
                        class="flex justify-center lg:justify-start gap-4 grayscale opacity-50 hover:grayscale-0 transition duration-500">
                        <span class="font-bold text-lg tracking-tighter italic">E-INSTANSI</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="mt-8 text-xs text-[#706f6c] dark:text-[#A1A09A]">
        &copy; 2026 {{ config('app.name') }}. Semua Hak Dilindungi.
    </footer>
</body>

</html>