<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Dibatalkan - 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 antialiased h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="mb-8 relative inline-block">
            <div class="absolute inset-0 bg-indigo-200 rounded-full blur-2xl opacity-50 scale-150"></div>
            <div class="relative bg-white p-6 rounded-3xl shadow-xl shadow-indigo-100 border border-indigo-50">
                <svg class="w-20 h-20 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </div>
        </div>

        <h1 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Akses Terbatas</h1>

        <p class="text-slate-500 mb-8 leading-relaxed">
            Maaf, akun Anda tidak memiliki izin (Role) yang diperlukan untuk mengakses halaman ini. Silakan hubungi
            Admin Pusat jika ini adalah kesalahan.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <a href="{{ url('/dashboard') }}"
                class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition active:scale-95 text-sm uppercase tracking-widest">
                Kembali ke Beranda
            </a>
        </div>

        <p class="mt-12 text-slate-400 text-xs font-medium uppercase tracking-[0.2em]">
            Error Code: 403 Forbidden
        </p>
    </div>
</body>

</html>