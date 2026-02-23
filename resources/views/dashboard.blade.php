<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Keuangan') }}
        </h2>
    </x-slot>

    {{-- Tambahkan sedikit CSS kustom untuk animasi masuk --}}
    @push('styles')
    <style>
        .animate-enter {
            opacity: 0;
            transform: translateY(20px);
            animation: enter-frames 0.6s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        @keyframes enter-frames {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @endpush

    <div class="py-10 bg-gray-50 dark:bg-gray-900 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. HERO BANNER - (Animasi Masuk: animate-enter) --}}
            <div
                class="animate-enter relative bg-gradient-to-r from-indigo-600 to-blue-500 rounded-3xl shadow-xl overflow-hidden text-white">
                <div
                    class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl animate-pulse">
                </div>
                <div
                    class="absolute bottom-0 left-0 -mb-16 -ml-16 w-48 h-48 bg-blue-300 opacity-20 rounded-full blur-2xl animate-pulse delay-300">
                </div>

                <div
                    class="relative p-8 md:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-2">
                        <p class="text-blue-100 text-sm font-medium tracking-wide uppercase">Tahun Anggaran Aktif: <span
                                class="font-bold text-white">{{ $anggaran->tahun ?? '-' }} {{
                                isset($anggaran->singkatan) ? '(' . strtoupper($anggaran->singkatan) . ')' : ''
                                }}</span></p>
                        <h3 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                            {{ $setting->nama_sekolah ?? 'Nama Sekolah Belum Diatur' }}
                        </h3>
                        <p class="text-indigo-100 flex items-center text-sm mt-2">
                            Kelola anggaran dan pantau realisasi dana BOS & BOP dengan mudah.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('sekolah.index') }}"
                            class="inline-flex items-center px-5 py-2.5 bg-white/20 hover:bg-white/30 border border-white/30 rounded-xl font-semibold text-sm text-white transition-all duration-300 backdrop-blur-sm hover:scale-105 active:scale-95">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Pengaturan Profil
                        </a>
                    </div>
                </div>
                <div
                    class="relative bg-black/10 border-t border-white/10 p-6 md:px-10 transition-all hover:bg-black/20">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center space-x-4 group">
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-200 uppercase tracking-wider font-semibold">Kepala Sekolah
                                </p>
                                <p class="text-sm font-bold text-white">{{ $setting->nama_kepala_sekolah ?? 'Belum
                                    Diisi' }} <span class="font-normal text-indigo-200 ml-1">| NIP. {{
                                        $setting->nip_kepala_sekolah ?? '-' }}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 group">
                            <div
                                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-200 uppercase tracking-wider font-semibold">Bendahara</p>
                                <p class="text-sm font-bold text-white">{{ $setting->nama_bendahara ?? 'Belum Diisi' }}
                                    <span class="font-normal text-indigo-200 ml-1">| NIP. {{ $setting->nip_bendahara ??
                                        '-' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. KARTU STATISTIK (Animasi Masuk dengan Delay + Counter Up) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    class="animate-enter delay-100 bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 group-hover:scale-110 transform origin-top-right">
                        <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex justify-between items-start mb-6 relative z-10">
                        <div>
                            <p class="text-sm font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                Anggaran BOS</p>
                            {{-- Alpine.js Counter Animation untuk Jumlah Komponen --}}
                            <h3 class="text-4xl font-black text-gray-800 dark:text-white mt-2"
                                x-data="{ current: 0, target: {{ $stats['total_bos'] ?? 0 }} }"
                                x-init="setTimeout(() => { const duration = 1000; const step = target / (duration / 16); let handle = setInterval(() => { current += step; if (current >= target) { current = target; clearInterval(handle); } }, 16); }, 200)">
                                <span x-text="Math.round(current)">0</span>
                                <span class="text-base font-medium text-gray-400">Komponen</span>
                            </h3>
                        </div>
                        <div
                            class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-bold uppercase tracking-widest shadow-sm border border-blue-100 dark:border-blue-800">
                            BOS</div>
                    </div>
                    <div class="space-y-3 bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4 relative z-10">
                        {{-- Counter Animation untuk Rupiah (Menggunakan fungsi formatCurrency JS di bawah) --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Harga</span>
                            <span class="text-base font-bold text-gray-800 dark:text-gray-200 counter-currency"
                                data-target="{{ $stats['harga_bos'] ?? 0 }}">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Pajak</span>
                            <span class="text-base font-bold text-rose-500 dark:text-rose-400 counter-currency"
                                data-target="{{ $stats['pajak_bos'] ?? 0 }}">Rp 0</span>
                        </div>
                    </div>
                    <div class="mt-5 relative z-10">
                        <a href="{{ route('akb.indexrincian', ['jenis_anggaran' => 'bos']) }}"
                            class="inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 group-hover:translate-x-2 transition-transform duration-300">
                            Lihat Rincian <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div
                    class="animate-enter delay-200 bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity duration-500 group-hover:scale-110 transform origin-top-right">
                        <svg class="w-24 h-24 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex justify-between items-start mb-6 relative z-10">
                        <div>
                            <p class="text-sm font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                Anggaran BOP</p>
                            {{-- Alpine.js Counter Animation untuk Jumlah Komponen --}}
                            <h3 class="text-4xl font-black text-gray-800 dark:text-white mt-2"
                                x-data="{ current: 0, target: {{ $stats['total_bop'] ?? 0 }} }"
                                x-init="setTimeout(() => { const duration = 1000; const step = target / (duration / 16); let handle = setInterval(() => { current += step; if (current >= target) { current = target; clearInterval(handle); } }, 16); }, 300)">
                                <span x-text="Math.round(current)">0</span>
                                <span class="text-base font-medium text-gray-400">Komponen</span>
                            </h3>
                        </div>
                        <div
                            class="px-3 py-1.5 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg text-xs font-bold uppercase tracking-widest shadow-sm border border-orange-100 dark:border-orange-800">
                            BOP</div>
                    </div>
                    <div class="space-y-3 bg-gray-50 dark:bg-gray-700/30 rounded-2xl p-4 relative z-10">
                        {{-- Counter Animation untuk Rupiah --}}
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Harga</span>
                            <span class="text-base font-bold text-gray-800 dark:text-gray-200 counter-currency"
                                data-target="{{ $stats['harga_bop'] ?? 0 }}">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Pajak</span>
                            <span class="text-base font-bold text-rose-500 dark:text-rose-400 counter-currency"
                                data-target="{{ $stats['pajak_bop'] ?? 0 }}">Rp 0</span>
                        </div>
                    </div>
                    <div class="mt-5 relative z-10">
                        <a href="{{ route('akb.indexrincian', ['jenis_anggaran' => 'bop']) }}"
                            class="inline-flex items-center text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 group-hover:translate-x-2 transition-transform duration-300">
                            Lihat Rincian <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- SIAPKAN DATA PHP --}}
            @php
            $rekapLabels = []; $rekapAnggaran = []; $rekapRealisasi = []; $rekapPersentase = [];
            $totalSemuaAnggaran = 0; $totalSemuaRealisasi = 0;

            foreach($dataRkas as $idbl => $items) {
            $anggaranNominal = $items->sum('total_anggaran');
            $realisasiNominal = $items->sum('total_realisasi');
            $rekapLabels[] = $items->first()->kegiatan->namagiat ?? 'Kegiatan ' . $idbl;
            $rekapAnggaran[] = $anggaranNominal;
            $rekapRealisasi[] = $realisasiNominal;
            $persen = $anggaranNominal > 0 ? round(($realisasiNominal / $anggaranNominal) * 100, 1) : 0;
            $rekapPersentase[] = $persen;
            $totalSemuaAnggaran += $anggaranNominal;
            $totalSemuaRealisasi += $realisasiNominal;
            }
            $persentaseTotal = $totalSemuaAnggaran > 0 ? round(($totalSemuaRealisasi / $totalSemuaAnggaran) * 100, 1) :
            0;
            @endphp

            {{-- 3. GRAFIK REKAPITULASI (Animasi Masuk dengan Delay Lebih Lama) --}}
            <div
                class="animate-enter delay-300 bg-white dark:bg-gray-800 shadow-sm sm:rounded-3xl border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow duration-300">
                <div
                    class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="w-full md:w-3/5">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Statistik Realisasi Kegiatan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">Perbandingan anggaran dan
                            penyerapan dana per aktivitas.</p>

                        {{-- PROGRESS BAR TOTAL PENYERAPAN (Animasi Pengisian Halus) --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-200 dark:border-gray-700"
                            x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 300)">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Penyerapan
                                    Keseluruhan</span>
                                {{-- Counter Up untuk Persentase Total --}}
                                <span
                                    class="text-xl font-black {{ $persentaseTotal >= 50 ? 'text-emerald-500' : 'text-orange-500' }}"
                                    x-data="{ current: 0, target: {{ $persentaseTotal }} }"
                                    x-init="setTimeout(() => { const duration = 1500; const step = target / (duration / 16); let handle = setInterval(() => { current += step; if (current >= target) { current = target; clearInterval(handle); } }, 16); }, 500)">
                                    <span x-text="current.toFixed(1)">0.0</span>%
                                </span>
                            </div>
                            <div
                                class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden relative">
                                <div class="h-2.5 rounded-full transition-all duration-[1500ms] ease-out {{ $persentaseTotal >= 50 ? 'bg-emerald-500' : 'bg-orange-500' }}"
                                    :style="shown ? 'width: {{ $persentaseTotal }}%' : 'width: 0%'"></div>
                            </div>
                            <div
                                class="flex justify-between mt-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                <span class="counter-currency" data-target="{{ $totalSemuaRealisasi }}">Realisasi: Rp
                                    0</span>
                                <span class="counter-currency" data-target="{{ $totalSemuaAnggaran }}">Anggaran: Rp
                                    0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Form Filter Triwulan --}}
                    <form method="GET" action="{{ route('dashboard') }}"
                        class="flex items-center w-full md:w-auto group">
                        <div class="relative w-full md:w-56">
                            <select name="tw" onchange="this.form.submit()"
                                class="block w-full pl-4 pr-10 py-3 text-sm font-semibold border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm appearance-none cursor-pointer transition-all group-hover:border-indigo-300">
                                <option value="tahun" {{ ($tw ?? 'tahun' )=='tahun' ? 'selected' : '' }}>Tahunan (Semua)
                                </option>
                                <option value="1" {{ ($tw ?? '' )=='1' ? 'selected' : '' }}>Triwulan 1</option>
                                <option value="2" {{ ($tw ?? '' )=='2' ? 'selected' : '' }}>Triwulan 2</option>
                                <option value="3" {{ ($tw ?? '' )=='3' ? 'selected' : '' }}>Triwulan 3</option>
                                <option value="4" {{ ($tw ?? '' )=='4' ? 'selected' : '' }}>Triwulan 4</option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 group-hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="p-6 md:p-8">
                    <div class="relative w-full" style="height: 400px;">
                        <canvas id="rekapChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT JS UNTUK CHART DAN ANIMASI COUNTER CURRENCY --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. FUNGSI UNTUK ANIMASI COUNTER RUPIAH ---
            const formatRupiah = (number) => {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(number);
            };

            const animateCurrencyCounters = () => {
                const counters = document.querySelectorAll('.counter-currency');
                const duration = 1500; // Durasi animasi dalam ms

                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const startTime = performance.now();

                    const updateCount = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);

                        // Easing function (easeOutQuart) agar animasi melambat di akhir
                        const easeOut = 1 - Math.pow(1 - progress, 4);
                        const current = target * easeOut;

                        counter.innerText = formatRupiah(current);

                        if (progress < 1) {
                            requestAnimationFrame(updateCount);
                        } else {
                             counter.innerText = formatRupiah(target); // Pastikan nilai akhir tepat
                        }
                    };
                    requestAnimationFrame(updateCount);
                });
            };

            // Jalankan animasi counter setelah sedikit delay agar halaman stabil
            setTimeout(animateCurrencyCounters, 300);


            // --- 2. KONFIGURASI CHART.JS DENGAN ANIMASI LEBIH HALUS ---
            const ctxRekap = document.getElementById('rekapChart').getContext('2d');
            const rekapLabels = {!! json_encode($rekapLabels) !!};
            const rekapAnggaran = {!! json_encode($rekapAnggaran) !!};
            const rekapRealisasi = {!! json_encode($rekapRealisasi) !!};
            const rekapPersentase = {!! json_encode($rekapPersentase) !!};

            new Chart(ctxRekap, {
                type: 'bar',
                data: {
                    labels: rekapLabels,
                    datasets: [
                        {
                            label: 'Anggaran',
                            data: rekapAnggaran,
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: 'transparent',
                            borderRadius: 6,
                            barPercentage: 0.6, categoryPercentage: 0.8
                        },
                        {
                            label: 'Realisasi',
                            data: rekapRealisasi,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: 'transparent',
                            borderRadius: 6,
                            barPercentage: 0.6, categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000, // Memperlambat animasi chart awal (2 detik)
                        easing: 'easeOutQuart' // Efek easing yang halus
                    },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: "'Inter', 'sans-serif'", size: 12 }, color: '#6B7280',
                                callback: function(value) { let label = this.getLabelForValue(value); return label.length > 20 ? label.substring(0, 20) + '...' : label; }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            border: { dash: [4, 4] },
                            grid: { color: 'rgba(156, 163, 175, 0.2)' },
                            ticks: {
                                font: { family: "'Inter', 'sans-serif'", size: 12 }, color: '#6B7280',
                                callback: function(value) { if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt'; return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top', align: 'end',
                            labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, font: { family: "'Inter', 'sans-serif'", size: 13, weight: '500' } }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Inter', 'sans-serif'", size: 14, weight: 'bold' },
                            bodyFont: { family: "'Inter', 'sans-serif'", size: 13 },
                            padding: 12, cornerRadius: 8, displayColors: true,
                            callbacks: {
                                title: function(context) { return rekapLabels[context[0].dataIndex]; },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    if (context.datasetIndex === 1) { let persen = rekapPersentase[context.dataIndex]; label += ' (' + persen + '% Terserap)'; }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
