<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Dashboard Pembina Ekstrakurikuler') }}
        </h2>
    </x-slot>

    @push('styles')
    <style>
        .animate-enter {
            opacity: 0;
            transform: translateY(15px);
            animation: enter-frames 0.5s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        @keyframes enter-frames {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @endpush

    <div class="py-10 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. HERO WELCOME BANNER --}}
            <div
                class="animate-enter relative bg-gradient-to-r from-indigo-600 to-blue-600 rounded-3xl p-8 sm:p-10 text-white shadow-xl overflow-hidden">
                <div
                    class="absolute top-0 right-0 -mt-12 -mr-12 w-48 h-48 bg-white/10 rounded-full blur-2xl animate-pulse">
                </div>
                <div class="relative z-10">
                    <span
                        class="bg-white/20 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest backdrop-blur-sm">
                        Panel Pembina & Pelatih
                    </span>
                    <h1 class="mt-4 text-3xl font-black tracking-tight">Selamat Datang, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="mt-2 text-indigo-100 text-sm max-w-xl leading-relaxed font-medium">
                        Akses kilat untuk memantau grup binaan, mengunggah jurnal mingguan, dan melengkapi berkas
                        dokumentasi ekskul Anda.
                    </p>
                </div>
            </div>

            {{-- 2. KARTU STATISTIK RINGKASAN --}}
            <div class="animate-enter delay-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 hover:shadow-md transition duration-300">
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ekskul Yang Diampu</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalEkskul }} <span
                                class="text-xs font-bold text-gray-400">Cabang</span></h3>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 hover:shadow-md transition duration-300">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jurnal Pertemuan</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalPertemuan }} <span
                                class="text-xs font-bold text-gray-400">Laporan</span></h3>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 hover:shadow-md transition duration-300">
                    <div
                        class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dokumentasi Kegiatan
                        </p>
                        <h3 class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalFoto }} <span
                                class="text-xs font-bold text-gray-400">Foto Terunggah</span></h3>
                    </div>
                </div>
            </div>

            {{-- 3. KONTEN UTAMA GRIDS --}}
            <div class="animate-enter delay-200 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                {{-- KIRI: DAFTAR EKSKUL YANG DIAMPU & AKSYON --}}
                <div class="space-y-6 lg:col-span-1">
                    <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-sm">
                        <h3
                            class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Cabang Kelompok Anda
                        </h3>

                        <div class="space-y-3">
                            @forelse($myEkskuls ?? [] as $ekskulItem)
                            <div
                                class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100 hover:bg-indigo-50/30 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                                    <span class="text-xs font-black text-gray-800 uppercase tracking-wide">
                                        {{ $ekskulItem->nama_ekskul ?? $ekskulItem->nama }}
                                    </span>
                                </div>
                                <span
                                    class="text-[9px] bg-indigo-100 text-indigo-700 font-extrabold px-2 py-0.5 rounded-full uppercase tracking-tighter">
                                    Aktif
                                </span>
                            </div>
                            @empty
                            <div class="text-center py-4 text-xs font-medium text-gray-400 italic">
                                Belum ada kelompok ekskul yang ditugaskan.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-sm">
                        <h3
                            class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span>⚡</span> Aksi Cepat Pembina
                        </h3>
                        <div class="space-y-3">
                            <a href="{{ route('ekskul.laporan.index') }}"
                                class="group flex items-center justify-between p-4 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm">
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider">Isi Jurnal Baru</h4>
                                    <p class="text-[10px] text-indigo-200 font-medium mt-0.5">Unggah materi & foto hari
                                        ini</p>
                                </div>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200 font-bold">→</span>
                            </a>

                            <a href="{{ route('ekskul.laporan.index') }}"
                                class="group flex items-center justify-between p-4 rounded-xl bg-gray-800 text-white hover:bg-black transition shadow-sm">
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider">Riwayat Arsip</h4>
                                    <p class="text-[10px] text-gray-400 font-medium mt-0.5">Lihat seluruh rekam laporan
                                    </p>
                                </div>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200 font-bold">→</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- KANAN: JURNAL PERTEMUAN TERAKHIR (Timeline Style) --}}
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <span>🕒</span> Garis Waktu Jurnal Laporan Terbaru
                        </h3>
                        <a href="{{ route('ekskul.laporan.index') }}"
                            class="text-[10px] font-black text-indigo-600 uppercase tracking-wider bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="flow-root">
                        <ul class="-mb-8">
                            @forelse($laporanTerbaru ?? [] as $index => $laporan)
                            <li>
                                <div class="relative pb-8">
                                    @if($index !== count($laporanTerbaru) - 1)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"></span>
                                    @endif

                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                class="h-8 w-8 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600 font-black text-xs">
                                                {{ $index + 1 }}
                                            </span>
                                        </div>

                                        <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-xs font-black text-indigo-600 uppercase tracking-wide">
                                                        {{ $laporan->ekskul->nama_ekskul ?? $laporan->ekskul->nama ??
                                                        '-' }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 font-mono">
                                                        • {{ \Carbon\Carbon::parse($laporan->tanggal_kegiatan ??
                                                        $laporan->tanggal)->translatedFormat('d M Y') }}
                                                    </span>
                                                </div>
                                                <p class="text-xs font-bold text-gray-800 leading-relaxed">
                                                    Materi: <span class="font-medium text-gray-600">{{ $laporan->materi
                                                        }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-100 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1 text-purple-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    {{ $laporan->fotos_count ?? $laporan->fotos->count() ?? 0 }} Foto
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <div
                                class="text-center py-12 text-xs font-bold text-gray-400 italic bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                Belum ada data jurnal pertemuan yang diunggah untuk minggu-minggu ini.
                            </div>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>