<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'E-Keuangan') }} | Sistem Informasi Keuangan Instansi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-[#050505] text-slate-900 dark:text-slate-100 antialiased">

    <div class="overflow-x-hidden w-full flex flex-col min-h-screen">

        <nav
            class="fixed top-0 w-full z-50 border-b border-slate-200/60 dark:border-slate-800/60 bg-white/80 dark:bg-[#050505]/80 backdrop-blur-md relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">

                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-indigo-600 dark:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-sm font-extrabold tracking-tight leading-none uppercase text-slate-900 dark:text-white">SI-KEUANGAN</span>
                            <span
                                class="text-[10px] text-slate-500 dark:text-slate-400 font-medium tracking-widest uppercase">Portal
                                Terintegrasi</span>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center gap-6">
                        @auth
                        <a href="{{ url('/dashboard') }}"
                            class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition">
                            Dashboard &rarr;
                        </a>
                        @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                            Log in
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 px-5 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition">
                            Daftar Instansi
                        </a>
                        @endif
                        @endauth
                    </div>

                    <button id="mobile-menu-btn"
                        class="md:hidden p-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition focus:outline-none"
                        aria-label="Toggle menu">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                </div>
            </div>

            <div id="mobile-menu"
                class="hidden absolute top-16 left-0 w-full bg-white/95 dark:bg-[#050505]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-xl flex-col py-4 px-6 gap-2 z-40 md:hidden">
                @auth
                <a href="{{ url('/dashboard') }}"
                    class="text-base font-semibold text-indigo-600 dark:text-indigo-400 py-3 border-b border-slate-100 dark:border-slate-800 block">
                    Dashboard &rarr;
                </a>
                @else
                <a href="{{ route('login') }}"
                    class="text-base font-medium text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 py-3 border-b border-slate-100 dark:border-slate-800 block">
                    Log in
                </a>
                @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="mt-4 bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white px-5 py-3 rounded-xl text-center font-bold block w-full hover:bg-slate-800 dark:hover:bg-slate-200 transition">
                    Daftar Instansi
                </a>
                @endif
                @endauth
            </div>
        </nav>

        <main class="pt-32 pb-16 px-4 flex-grow">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-12 gap-12 items-center">

                    <div class="lg:col-span-7 space-y-8">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                            <span
                                class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">Update
                                Anggaran TA 2026</span>
                        </div>

                        <h1
                            class="text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1] text-slate-900 dark:text-white">
                            Transformasi Digital <br>
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-600">Keuangan
                                Instansi.</span>
                        </h1>

                        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                            Sistem pengelolaan administrasi sekolah yang mengedepankan transparansi, akurasi data, dan
                            kemudahan pelaporan anggaran triwulan dalam satu ekosistem digital.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('login') }}"
                                class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold shadow-xl shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                Akses Portal Utama
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="border-2 border-slate-200 dark:border-slate-800 px-8 py-4 rounded-xl font-bold hover:bg-white dark:hover:bg-slate-900 transition-all text-center">
                                Dokumentasi Sistem
                            </a>
                        </div>

                        <div
                            class="pt-8 flex flex-wrap items-center gap-8 border-t border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">Keamanan
                                    Data</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">SSL Encrypted</p>
                            </div>
                            <div class="hidden sm:block w-px h-8 bg-slate-200 dark:bg-slate-800"></div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2">
                                    Kepatuhan</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Standar ARKAS 2026</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 relative">
                        <div class="absolute -top-20 -right-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl">
                        </div>

                        <div
                            class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden p-2">
                            <div
                                class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-700">
                                <div class="flex justify-between items-center mb-6">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">Ringkasan
                                        Realisasi</span>
                                    <span
                                        class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded">Live</span>
                                </div>
                                <div class="space-y-4">
                                    <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full w-full">
                                        <div class="h-2 bg-indigo-600 rounded-full w-3/4"></div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-2">
                                        <div>
                                            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Dana BOS</p>
                                            <p class="text-xl font-black text-slate-900 dark:text-white">Rp 450.000.000
                                            </p>
                                        </div>
                                        <div class="sm:text-right">
                                            <p class="text-[10px] text-emerald-500 font-bold">75% Terserap</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div
                                    class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 hover:border-indigo-200 transition">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center mb-3 text-indigo-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd"
                                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold mb-1">E-RKAS</p>
                                    <p class="text-[10px] text-slate-500 leading-tight">Digitalisasi rencana kegiatan.
                                    </p>
                                </div>
                                <div
                                    class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800 hover:border-emerald-200 transition">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center mb-3 text-emerald-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold mb-1">Rekanan</p>
                                    <p class="text-[10px] text-slate-500 leading-tight">Database pihak ketiga terpusat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <footer class="mt-auto py-12 border-t border-slate-200 dark:border-slate-800 w-full bg-white dark:bg-[#050505]">
            <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2 grayscale opacity-60">
                    <span class="font-black tracking-tighter text-xl italic">E-INSTANSI</span>
                    <span class="text-[10px] font-bold bg-slate-200 px-2 py-1 rounded">2026</span>
                </div>
                <div
                    class="flex flex-wrap justify-center gap-4 sm:gap-8 text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                    <a href="#" class="hover:text-indigo-600">Panduan</a>
                    <a href="#" class="hover:text-indigo-600">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-indigo-600">Kontak</a>
                </div>
                <p class="text-xs text-slate-400 font-medium text-center md:text-left">
                    &copy; 2026 Sistem Manajemen Keuangan Sekolah.
                </p>
            </div>
        </footer>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');

            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
                menu.classList.toggle('flex');
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    menu.classList.add('hidden');
                    menu.classList.remove('flex');
                }
            });
        });
    </script>
</body>

</html>