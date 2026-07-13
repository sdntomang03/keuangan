<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200">
                Laporan SPJ & Pajak Dinamis
            </h2>
            <a href="{{ route('realisasi.spj.pdf') }}"
                class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-bold shadow hover:bg-rose-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Download PDF
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700">

                <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Rincian Belanja - {{
                        strtoupper($anggaran->singkatan) }} Tahun {{ $anggaran->tahun }} (Triwulan {{
                        $sekolah->triwulan_aktif }})</h3>
                </div>

                <div class="overflow-x-auto p-0">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        <thead
                            class="text-xs text-slate-700 uppercase bg-slate-100 dark:bg-slate-900/50 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-center">No</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700">Tanggal</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700">No Bukti</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700">Rekanan</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700">Uraian</th>
                                <th class="px-4 py-3 border-b dark:border-slate-700 text-right">Nilai SPJ (Bruto)</th>

                                {{-- Kolom Pajak Dinamis --}}
                                @foreach($pajakUnik as $pajak)
                                <th
                                    class="px-4 py-3 border-b dark:border-slate-700 text-right text-rose-600 dark:text-rose-400">
                                    Potongan {{ $pajak }}</th>
                                @endforeach

                                <th
                                    class="px-4 py-3 border-b dark:border-slate-700 text-right text-emerald-600 dark:text-emerald-400">
                                    Nilai Bersih (Netto)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($mappedData as $index => $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $row['no_bukti'] ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $row['rekanan'] }}</td>
                                <td class="px-4 py-3 whitespace-normal min-w-[200px]">{{ $row['uraian'] }}</td>
                                <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($row['bruto'], 0, ',',
                                    '.') }}</td>

                                @foreach($pajakUnik as $pajak)
                                <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400">
                                    {{ $row['pajak'][$pajak] > 0 ? 'Rp ' . number_format($row['pajak'][$pajak], 0, ',',
                                    '.') : '-' }}
                                </td>
                                @endforeach

                                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{
                                    number_format($row['netto'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 6 + count($pajakUnik) }}"
                                    class="px-4 py-8 text-center text-slate-500 italic">Tidak ada data transaksi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot
                            class="bg-slate-100 dark:bg-slate-900/80 font-bold text-slate-800 dark:text-white border-t-2 border-slate-300 dark:border-slate-600">
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-right uppercase tracking-wider">Grand Total</td>
                                <td class="px-4 py-4 text-right">Rp {{ number_format($totals['bruto'], 0, ',', '.') }}
                                </td>

                                @foreach($pajakUnik as $pajak)
                                <td class="px-4 py-4 text-right text-rose-600 dark:text-rose-400">Rp {{
                                    number_format($totals['pajak'][$pajak], 0, ',', '.') }}</td>
                                @endforeach

                                <td class="px-4 py-4 text-right text-emerald-600 dark:text-emerald-400">Rp {{
                                    number_format($totals['netto'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>