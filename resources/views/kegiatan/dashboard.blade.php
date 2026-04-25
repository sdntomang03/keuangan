<x-manual-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white uppercase tracking-tight">Dashboard Perencanaan
            </h1>
            <p class="text-slate-500 text-sm mt-1">Ringkasan data perencanaan keuangan SDN Tomang 03 Pagi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 bg-emerald-50 dark:bg-emerald-500/10 p-8 rounded-full">
                    <svg class="w-12 h-12 text-emerald-500 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Plafon Anggaran</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">Rp {{ number_format($totalAnggaran,
                        0, ',', '.') }}</h3>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 bg-indigo-50 dark:bg-indigo-500/10 p-8 rounded-full">
                    <svg class="w-12 h-12 text-indigo-500 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah Kegiatan</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $totalKegiatan }} <span
                            class="text-sm font-medium text-slate-400">Draft</span></h3>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -top-6 bg-amber-50 dark:bg-amber-500/10 p-8 rounded-full">
                    <svg class="w-12 h-12 text-amber-500 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sumber Dana Tersedia</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $totalSumberDana }} <span
                            class="text-sm font-medium text-slate-400">Tahun</span></h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div
                class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div
                    class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase">Rekapitulasi per Sumber Dana
                    </h3>
                    <a href="{{ route('laporan.index') }}"
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Lihat Laporan &rarr;</a>
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase">Sumber Dana &
                                    Tahun</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase">Total Plafon
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($rekapPerSumberDana as $rekap)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $rekap->nama }}
                                    </div>
                                    <div class="text-xs text-slate-500">Tahun Anggaran {{ $rekap->tahun }}</div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="text-sm font-bold text-emerald-600">Rp {{ number_format($rekap->total,
                                        0, ',', '.') }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-sm text-slate-500 italic">Belum ada
                                    serapan anggaran tersimpan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div
                    class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase">Aktivitas Perencanaan
                        Terakhir</h3>
                    <a href="{{ route('kegiatan.index') }}"
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Semua Kegiatan &rarr;</a>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($kegiatanTerbaru as $kgt)
                        <li class="p-5 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0 pr-4">
                                    <p class="text-sm font-bold text-indigo-700 truncate">
                                        {{ $kgt->id_kegiatan }}
                                    </p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white mt-1 line-clamp-1">
                                        {{ $kgt->program->nama_program ?? 'Program tidak diketahui' }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $kgt->sumberDana->nama ?? '-' }} ({{ $kgt->sumberDana->tahun ?? '-' }})
                                    </p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-[11px] font-medium text-slate-400">{{
                                        $kgt->updated_at->diffForHumans() }}</p>
                                    <a href="{{ route('kegiatan.tambah_komponen', $kgt->id) }}"
                                        class="inline-block mt-2 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold rounded-md transition-colors">
                                        Isi Rincian
                                    </a>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="p-8 text-center text-sm text-slate-500 italic">Belum ada kegiatan yang dibuat.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-manual-layout>