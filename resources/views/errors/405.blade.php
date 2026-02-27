<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>405 Metode Tidak Diizinkan | SI-KEUANGAN</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="bg-slate-50 dark:bg-[#050505] text-slate-900 dark:text-slate-100 antialiased min-h-screen flex items-center justify-center overflow-hidden relative">

    <div
        class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-rose-500/20 dark:bg-rose-500/10 rounded-full blur-3xl pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-indigo-500/20 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
    </div>

    <div class="relative z-10 w-full max-w-3xl mx-auto px-6 text-center">
        <div
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 dark:bg-rose-900/30 border border-rose-100 dark:border-rose-800 mb-8 shadow-sm">
            <span class="relative flex h-2 w-2">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
            </span>
            <span class="text-[11px] font-bold text-rose-700 dark:text-rose-300 uppercase tracking-widest">HTTP Error
                405</span>
        </div>

        <h1
            class="text-9xl md:text-[12rem] font-black tracking-tighter leading-none mb-4 text-transparent bg-clip-text bg-gradient-to-br from-slate-800 to-slate-400 dark:from-white dark:to-slate-600 drop-shadow-sm">
            405
        </h1>

        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4 text-slate-900 dark:text-white">
            Metode Tidak <span
                class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 to-indigo-600">Diizinkan.</span>
        </h2>

        <p class="text-base md:text-lg text-slate-600 dark:text-slate-400 mb-10 max-w-xl mx-auto leading-relaxed">
            Maaf, akses ke halaman tersebut ditolak karena metode pengiriman data (GET/POST) tidak sesuai dengan standar
            keamanan sistem SI-KEUANGAN.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="window.history.back()"
                class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl shadow-indigo-200 dark:shadow-none transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </button>

            <a href="{{ url('/') }}"
                class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold border-2 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center justify-center">
                Ke Beranda Utama
            </a>
        </div>

        <p class="mt-12 text-xs font-medium text-slate-400 dark:text-slate-500">
            Jika Anda terus melihat pesan ini, silakan hubungi <a href="#"
                class="text-indigo-600 dark:text-indigo-400 hover:underline">Administrator Sistem</a>.
        </p>
    </div>

</body>

</html>