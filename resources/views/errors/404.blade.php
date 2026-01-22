<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan - 404</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 antialiased h-screen flex items-center justify-center p-6 text-slate-900">
    <div class="max-w-xl w-full text-center">
        <div class="relative mb-8">
            <h1
                class="text-[120px] font-extrabold leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-indigo-600 to-indigo-400 opacity-20 select-none">
                404
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <div
                    class="bg-white p-5 rounded-3xl shadow-2xl shadow-indigo-100 border border-indigo-50 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                    <svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <h2 class="text-3xl font-extrabold mb-4 tracking-tight">Halaman Tidak Ditemukan</h2>

        <p class="text-slate-500 mb-10 leading-relaxed max-w-sm mx-auto font-medium">
            Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan ke alamat lain.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="{{ url('/') }}"
                class="w-full sm:w-auto px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-xl shadow-indigo-200 transition active:scale-95 text-sm uppercase tracking-widest flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Kembali
            </a>

        </div>

        <p class="mt-16 text-slate-300 text-[10px] font-bold uppercase tracking-[0.4em]">
            System Status: Page Missing (Code: 404)
        </p>
    </div>
</body>

</html>