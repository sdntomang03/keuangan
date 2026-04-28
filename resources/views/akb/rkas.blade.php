<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight print:hidden">
            {{ __('Matriks Anggaran Kas (AKB) - ') . ($anggaran->tahun ?? '') }}
        </h2>
    </x-slot>

    {{-- Tambahan CSS khusus Print --}}
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white !important;
            }

            @page {
                size: landscape;
                margin: 10mm;
            }

            header,
            nav,
            aside {
                display: none !important;
            }

            .max-w-7xl {
                max-width: 100% !important;
            }

            .py-4 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
        }
    </style>

    {{-- Filter Form (Disembunyikan saat print) --}}
    <div
        class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200 mt-6 mx-auto max-w-7xl sm:px-6 lg:px-8 print:hidden">
        <form action="{{ route('akb.rincian') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih Triwulan</label>
                <select name="filter_tw"
                    class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">-- Setahun Penuh --</option>
                    <option value="1" {{ request('filter_tw')=='1' ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ request('filter_tw')=='2' ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ request('filter_tw')=='3' ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ request('filter_tw')=='4' ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 font-bold text-indigo-600">Mode Tampilan</label>
                <select name="tampilan"
                    class="mt-1 block w-40 rounded-md border-indigo-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-indigo-50">
                    <option value="bulanan" {{ request('tampilan')=='bulanan' ? 'selected' : '' }}>Rincian Bulan
                    </option>
                    <option value="triwulan" {{ request('tampilan')=='triwulan' ? 'selected' : '' }}>Rekap Triwulan
                    </option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm transition">Filter</button>
                <a href="{{ route('akb.rincian') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm transition">Reset</a>

                {{-- TOMBOL CETAK --}}
                <button type="button" onclick="window.print()"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak
                </button>
            </div>
        </form>
    </div>

    @php
    $mapTriwulan = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];
    $filterTw = request('filter_tw');
    $tampilan = request('tampilan', 'bulanan');

    $targetMonths = $filterTw ? $mapTriwulan[$filterTw] : range(1, 12);
    $targetQuarters = $filterTw ? [$filterTw] : [1, 2, 3, 4];

    // FILTER: Buang baris yang tidak ada uangnya
    $filteredRkas = $dataRkas->filter(function ($item) use ($targetMonths) {
    $sumTarget = 0;
    foreach ($targetMonths as $m) {
    $prop = "bln_$m";
    $sumTarget += $item->$prop;
    }
    return $sumTarget > 0;
    });

    // Pengelompokan Tingkat 1: Berdasarkan Kegiatan
    $groupedRkas = $filteredRkas->groupBy('idbl');

    // Total Kolom untuk perhitungan colspan dinamis
    // 4 Kolom Statis (Komponen, Satuan, Harga, Total) + Jumlah Kolom Dinamis (TW/Bulan)
    $totalKolom = 4 + ($tampilan == 'triwulan' ? count($targetQuarters) : count($targetMonths));
    @endphp

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:px-0">
            <div
                class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto print:shadow-none print:p-0 print:overflow-visible">

                {{-- KOP LAPORAN --}}
                <div class="hidden print:block text-center mb-6 border-b pb-4">
                    <h2 class="text-xl font-bold uppercase tracking-wider">
                        Matriks Anggaran Kas (AKB) - {{ $anggaran->tahun ?? date('Y') }}
                    </h2>
                    <p class="text-sm text-gray-700 font-bold mt-1">
                        Unit Sekolah: {{ auth()->user()->sekolah->nama_sekolah ?? auth()->user()->name ?? 'Nama Sekolah
                        Tidak Tersedia' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Mode: {{ $tampilan == 'triwulan' ? 'Rekap Triwulan' : 'Rincian Bulanan' }}
                        {{ $filterTw ? '(Triwulan '.$filterTw.')' : '(Setahun Penuh)' }}
                    </p>
                </div>

                <table class="w-full border-collapse border border-gray-300 text-[11px] print:text-[10px]">
                    <thead class="bg-gray-100 font-bold print:bg-gray-200">
                        <tr>
                            <th rowspan="2" class="border border-gray-300 px-3 py-2 align-middle text-left w-64">
                                Komponen & Spesifikasi
                            </th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 align-middle text-center w-16">
                                Satuan
                            </th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-2 align-middle text-right w-24">
                                Harga Satuan
                            </th>
                            <th rowspan="2"
                                class="border border-gray-300 px-2 py-2 align-middle text-right w-28 bg-gray-50 print:bg-gray-100">
                                Total RKAS<br><span class="text-[9px] font-normal">(Setahun)</span>
                            </th>

                            @if($tampilan == 'triwulan')
                            <th colspan="{{ count($targetQuarters) }}"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50 print:bg-blue-100">
                                {{ $filterTw ? 'Rincian Triwulan ' . $filterTw : 'Rincian Per Triwulan' }}
                            </th>
                            @else
                            <th colspan="{{ count($targetMonths) }}"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50 print:bg-blue-100">
                                {{ $filterTw ? 'Rincian Bulan (TW ' . $filterTw . ')' : 'Rincian Per Bulan (Jan - Des)'
                                }}
                            </th>
                            @endif
                        </tr>
                        <tr>
                            @if($tampilan == 'triwulan')
                            @foreach($targetQuarters as $tw)
                            <th class="border border-gray-300 px-1 py-1 text-center w-28">TW {{ $tw }}</th>
                            @endforeach
                            @else
                            @foreach ($targetMonths as $m)
                            <th class="border border-gray-300 px-1 py-1 text-center w-16">
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                            </th>
                            @endforeach
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($groupedRkas as $idbl => $itemsByKegiatan)
                        @php
                        $namaKegiatan = $itemsByKegiatan->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi';

                        // Pengelompokan Tingkat 2: Berdasarkan Kode Rekening di dalam Kegiatan tersebut
                        $groupedByKorek = $itemsByKegiatan->groupBy(function($item) {
                        return $item->korek->kode ?? '-';
                        });
                        @endphp

                        {{-- 1. HEADER BARIS KEGIATAN --}}
                        <tr class="bg-indigo-100 print:bg-gray-300">
                            <td colspan="{{ $totalKolom }}" class="border border-gray-300 px-3 py-2">
                                <span
                                    class="font-black text-indigo-900 print:text-black uppercase text-[11px] tracking-wide">
                                    KEGIATAN: {{ $namaKegiatan }}
                                </span>
                            </td>
                        </tr>

                        @foreach ($groupedByKorek as $kodeKorek => $itemsByKorek)
                        @php
                        $namaKorek = $itemsByKorek->first()->korek->ket ?? 'Rekening Tidak Terdefinisi';
                        @endphp

                        {{-- 2. HEADER BARIS KODE REKENING --}}
                        <tr class="bg-indigo-50/70 print:bg-gray-100">
                            <td colspan="{{ $totalKolom }}" class="border border-gray-300 px-3 py-1.5 pl-6">
                                <span class="font-bold text-indigo-800 print:text-gray-800 text-[10px] uppercase">
                                    REKENING: {{ $kodeKorek }} - {{ $namaKorek }}
                                </span>
                            </td>
                        </tr>

                        {{-- 3. BARIS RINCIAN KOMPONEN --}}
                        @foreach ($itemsByKorek as $rkas)
                        <tr class="hover:bg-gray-50 transition print:break-inside-avoid">
                            <td class="border border-gray-300 px-3 py-1.5 pl-10">
                                <div class="font-bold text-gray-800">{{ $rkas->namakomponen }}</div>
                                <div class="text-[9px] text-gray-500 italic mt-0.5">Spesifikasi: {{ $rkas->spek ?? '-'
                                    }}</div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1.5 text-center text-gray-600">
                                {{ $rkas->satuan ?? '-' }}
                            </td>
                            <td
                                class="border border-gray-300 px-2 py-1.5 text-right font-medium text-gray-600 print:text-black">
                                {{ number_format($rkas->hargasatuan, 0, ',', '.') }}
                            </td>
                            <td
                                class="border border-gray-300 px-2 py-1.5 text-right font-bold bg-yellow-50 text-indigo-700 print:bg-yellow-100 print:text-black">
                                {{ number_format($rkas->total_akb_setahun, 0, ',', '.') }}
                            </td>

                            {{-- Kolom Dinamis (TW / Bulan) --}}
                            @if($tampilan == 'triwulan')
                            @foreach($targetQuarters as $tw)
                            <td class="border border-gray-300 px-2 py-1.5 text-right font-medium">
                                @php $propTw = "tw_$tw"; $valTw = $rkas->$propTw; @endphp
                                {{ $valTw > 0 ? number_format($valTw, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @else
                            @foreach ($targetMonths as $m)
                            <td class="border border-gray-300 px-1 py-1.5 text-right">
                                @php $propBln = "bln_$m"; $valBln = $rkas->$propBln; @endphp
                                {{ $valBln > 0 ? number_format($valBln, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @endif
                        </tr>
                        @endforeach
                        @endforeach

                        {{-- 4. BARIS SUB TOTAL KEGIATAN --}}
                        <tr class="bg-blue-50 border-y-2 border-blue-200 print:bg-blue-100 print:border-black">
                            <td colspan="3"
                                class="border border-gray-300 px-3 py-2 text-right text-[10px] font-black text-blue-900 print:text-black uppercase tracking-widest">
                                Sub Total Kegiatan
                            </td>
                            <td
                                class="border border-gray-300 px-2 py-2 text-right font-bold text-blue-900 bg-blue-100/50 print:text-black print:bg-blue-200">
                                {{ number_format($itemsByKegiatan->sum('total_akb_setahun'), 0, ',', '.') }}
                            </td>

                            @if($tampilan == 'triwulan')
                            @foreach($targetQuarters as $tw)
                            <td
                                class="border border-gray-300 px-2 py-2 text-right font-bold text-blue-900 print:text-black">
                                @php $propTw = "tw_$tw"; $subTotalTw = $itemsByKegiatan->sum($propTw); @endphp
                                {{ $subTotalTw > 0 ? number_format($subTotalTw, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @else
                            @foreach ($targetMonths as $m)
                            <td
                                class="border border-gray-300 px-1 py-2 text-right font-bold text-blue-900 print:text-black">
                                @php $propBln = "bln_$m"; $subTotalBln = $itemsByKegiatan->sum($propBln); @endphp
                                {{ $subTotalBln > 0 ? number_format($subTotalBln, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $totalKolom }}" class="text-center py-8 text-gray-500 font-medium">
                                Tidak ada data untuk periode terpilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- 5. FOOTER GRAND TOTAL --}}
                    @if($filteredRkas->isNotEmpty())
                    <tfoot class="bg-gray-800 text-white font-bold print:bg-gray-800 print:text-white">
                        <tr>
                            <td colspan="3"
                                class="border border-gray-700 px-3 py-3 text-right uppercase tracking-widest text-gray-200 print:text-white">
                                Grand Total Keseluruhan
                            </td>
                            <td
                                class="border border-gray-700 px-2 py-3 text-right bg-yellow-500/20 text-yellow-300 text-sm print:text-yellow-300">
                                {{ number_format($filteredRkas->sum('total_akb_setahun'), 0, ',', '.') }}
                            </td>

                            @if($tampilan == 'triwulan')
                            @foreach($targetQuarters as $tw)
                            <td class="border border-gray-700 px-2 py-3 text-right text-gray-100 print:text-white">
                                @php $propTw = "tw_$tw"; $grandTotalTw = $filteredRkas->sum($propTw); @endphp
                                {{ $grandTotalTw > 0 ? number_format($grandTotalTw, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @else
                            @foreach ($targetMonths as $m)
                            <td class="border border-gray-700 px-1 py-3 text-right text-gray-100 print:text-white">
                                @php $propBln = "bln_$m"; $grandTotalBln = $filteredRkas->sum($propBln); @endphp
                                {{ $grandTotalBln > 0 ? number_format($grandTotalBln, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @endif
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>