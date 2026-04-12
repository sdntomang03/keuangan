<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight uppercase">
                    Rekapitulasi Total RKAS
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    Ringkasan alokasi pagu berdasarkan SNP, Kegiatan, dan Kode Rekening.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center px-4 py-1.5 rounded-lg text-sm font-bold bg-indigo-50 border border-indigo-200 text-indigo-700 shadow-sm uppercase">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Mode: {{ $anggaran->nama_anggaran }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <form method="GET" action="{{ route('rkas.rekap') }}" class="flex items-center gap-2 mb-6 print:hidden">
            <label for="tw" class="text-sm font-bold text-gray-700">Periode:</label>
            <select name="tw" id="tw" onchange="this.form.submit()"
                class="border-gray-300 rounded-lg text-sm font-semibold text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="tahun" {{ $tw=='tahun' ? 'selected' : '' }}>Satu Tahun Penuh</option>
                <option value="1" {{ $tw=='1' ? 'selected' : '' }}>Triwulan 1 (Jan-Mar)</option>
                <option value="2" {{ $tw=='2' ? 'selected' : '' }}>Triwulan 2 (Apr-Jun)</option>
                <option value="3" {{ $tw=='3' ? 'selected' : '' }}>Triwulan 3 (Jul-Sep)</option>
                <option value="4" {{ $tw=='4' ? 'selected' : '' }}>Triwulan 4 (Okt-Des)</option>
            </select>
        </form>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. KARTU GRAND TOTAL --}}
            <div
                class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl shadow-md border border-gray-700 p-6 mb-6 flex flex-col md:flex-row justify-between items-center text-white">
                <div class="flex items-center gap-4 mb-4 md:mb-0">
                    <div class="p-3 bg-gray-700/50 rounded-lg">
                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-gray-400 text-sm font-bold tracking-widest uppercase mb-1">Grand Total Anggaran
                            Aktif</h3>
                        <p class="text-3xl font-black font-mono tracking-tight text-white">
                            <span class="text-gray-500 text-xl mr-1">Rp</span>{{ number_format($grandTotalAnggaran, 0,
                            ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <button onclick="window.print()"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-wider transition shadow-lg flex items-center print:hidden">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            {{-- 2. TABEL REKAP SNP --}}
            <div class="mb-8 bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-blue-900 text-lg uppercase tracking-wider flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        Rekapitulasi per Standar Pendidikan (SNP)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3 text-center font-bold w-16 border-r border-gray-700">No</th>
                                <th class="px-5 py-3 text-left font-bold">Standar Nasional Pendidikan (SNP)</th>
                                <th class="px-5 py-3 text-center font-bold w-24 border-l border-gray-700">Item</th>
                                <th class="px-5 py-3 text-right font-bold w-48 border-l border-gray-700">Total Pagu (Rp)
                                </th>
                                <th class="px-5 py-3 text-center font-bold w-32 border-l border-gray-700">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($rekapSnp as $index => $item)
                            @php
                            $persen = $grandTotalAnggaran > 0 ? ($item->total_anggaran / $grandTotalAnggaran) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-5 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-bold text-gray-800 text-[12px] uppercase">{{ $item->uraian }}
                                </td>
                                <td class="px-5 py-3 text-center font-medium text-gray-500">{{ $item->jumlah_item }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-gray-800">{{
                                    number_format($item->total_anggaran, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-black text-gray-600">{{ number_format($persen, 1)
                                            }}%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="h-1.5 rounded-full bg-blue-500" style="width: {{ $persen }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5"
                                    class="p-10 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Data SNP belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- TFOOT REKAP SNP --}}
                        @if($rekapSnp->isNotEmpty())
                        <tfoot class="bg-gray-100 font-bold text-[12px]">
                            <tr>
                                <td colspan="2" class="px-5 py-4 text-right uppercase tracking-widest text-gray-600">
                                    Total Keseluruhan</td>
                                <td class="px-5 py-4 text-center text-gray-700">{{ $rekapSnp->sum('jumlah_item') }}</td>
                                <td class="px-5 py-4 text-right font-mono text-blue-700 text-sm">{{
                                    number_format($rekapSnp->sum('total_anggaran'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- 3. TABEL REKAP KEGIATAN --}}
            <div class="mb-8 bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-indigo-900 text-lg uppercase tracking-wider flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        Rekapitulasi per Kegiatan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3 text-center font-bold w-16 border-r border-gray-700">No</th>

                                <th class="px-5 py-3 text-left font-bold">Uraian Kegiatan</th>
                                <th class="px-5 py-3 text-center font-bold w-24 border-l border-gray-700">Item</th>
                                <th class="px-5 py-3 text-right font-bold w-48 border-l border-gray-700">Total Pagu (Rp)
                                </th>
                                <th class="px-5 py-3 text-center font-bold w-32 border-l border-gray-700">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($rekapKegiatan as $index => $item)
                            @php
                            $persen = $grandTotalAnggaran > 0 ? ($item->total_anggaran / $grandTotalAnggaran) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-indigo-50/50 transition">
                                <td class="px-5 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>

                                <td class="px-5 py-3 font-bold text-gray-800 text-[12px] uppercase">{{ $item->uraian }}
                                </td>
                                <td class="px-5 py-3 text-center font-medium text-gray-500">{{ $item->jumlah_item }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-gray-800">{{
                                    number_format($item->total_anggaran, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-black text-gray-600">{{ number_format($persen, 1)
                                            }}%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ $persen }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6"
                                    class="p-10 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Tidak ada data kegiatan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- TFOOT REKAP KEGIATAN --}}
                        @if($rekapKegiatan->isNotEmpty())
                        <tfoot class="bg-gray-100 font-bold text-[12px]">
                            <tr>
                                <td colspan="2" class="px-5 py-4 text-right uppercase tracking-widest text-gray-600">
                                    Total Keseluruhan</td>
                                <td class="px-5 py-4 text-center text-gray-700">{{ $rekapKegiatan->sum('jumlah_item') }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-indigo-700 text-sm">{{
                                    number_format($rekapKegiatan->sum('total_anggaran'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- 4. TABEL REKAP KODE REKENING --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-emerald-900 text-lg uppercase tracking-wider flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                        Rekapitulasi per Kode Rekening
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-5 py-3 text-center font-bold w-16 border-r border-gray-700">No</th>

                                <th class="px-5 py-3 text-left font-bold">Uraian Rekening</th>
                                <th class="px-5 py-3 text-center font-bold w-24 border-l border-gray-700">Item</th>
                                <th class="px-5 py-3 text-right font-bold w-48 border-l border-gray-700">Total Pagu (Rp)
                                </th>
                                <th class="px-5 py-3 text-center font-bold w-32 border-l border-gray-700">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($rekapRekening as $index => $item)
                            @php
                            $persen = $grandTotalAnggaran > 0 ? ($item->total_anggaran / $grandTotalAnggaran) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-emerald-50/50 transition">
                                <td class="px-5 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>

                                <td class="px-5 py-3 font-bold text-gray-800 text-[12px] uppercase">{{ $item->uraian }}
                                </td>
                                <td class="px-5 py-3 text-center font-medium text-gray-500">{{ $item->jumlah_item }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-gray-800">{{
                                    number_format($item->total_anggaran, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-black text-gray-600">{{ number_format($persen, 1)
                                            }}%</span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="h-1.5 rounded-full bg-emerald-500"
                                                style="width: {{ $persen }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5"
                                    class="p-10 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Tidak ada data kode rekening.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- TFOOT REKAP REKENING --}}
                        @if($rekapRekening->isNotEmpty())
                        <tfoot class="bg-gray-100 font-bold text-[12px]">
                            <tr>
                                <td colspan="2" class="px-5 py-4 text-right uppercase tracking-widest text-gray-600">
                                    Total Keseluruhan</td>
                                <td class="px-5 py-4 text-center text-gray-700">{{ $rekapRekening->sum('jumlah_item') }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-emerald-700 text-sm">{{
                                    number_format($rekapRekening->sum('total_anggaran'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            {{-- 5. TABEL REKAP KODE REKENING PER TRIWULAN (SETAHUN PENUH) --}}
            <div class="mt-8 bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden print:mt-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-black text-purple-900 text-lg uppercase tracking-wider flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Rekapitulasi Kode Rekening per Triwulan (1 Tahun)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider text-[11px]">
                            <tr>
                                <th rowspan="2"
                                    class="px-5 py-3 text-center font-bold w-16 border-r border-gray-700 align-middle">
                                    No</th>
                                <th rowspan="2"
                                    class="px-5 py-3 text-left font-bold border-r border-gray-700 align-middle">Uraian
                                    Rekening</th>
                                <th colspan="4" class="px-5 py-2 text-center font-bold border-b border-gray-700">Alokasi
                                    per Triwulan (Rp)</th>
                                <th rowspan="2"
                                    class="px-5 py-3 text-right font-bold w-40 border-l border-gray-700 align-middle">
                                    Total 1 Tahun (Rp)</th>
                            </tr>
                            <tr>
                                <th class="px-3 py-2 text-right font-bold w-32 border-r border-gray-700">TW 1</th>
                                <th class="px-3 py-2 text-right font-bold w-32 border-r border-gray-700">TW 2</th>
                                <th class="px-3 py-2 text-right font-bold w-32 border-r border-gray-700">TW 3</th>
                                <th class="px-3 py-2 text-right font-bold w-32 border-r border-gray-700">TW 4</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- Gunakan variabel baru dari controller, misal $rekapRekeningSemuaTw --}}
                            @forelse($rekapRekeningSemuaTw ?? [] as $index => $item)
                            <tr class="hover:bg-purple-50/50 transition">
                                <td class="px-5 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-bold text-gray-800 text-[12px] uppercase">
                                    {{ $item->uraian }}
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">
                                    {{ number_format($item->tw1 ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">
                                    {{ number_format($item->tw2 ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">
                                    {{ number_format($item->tw3 ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">
                                    {{ number_format($item->tw4 ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-gray-900 bg-gray-50/50">
                                    {{ number_format($item->total_anggaran ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7"
                                    class="p-10 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Tidak ada data untuk rekap semua triwulan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        @if(isset($rekapRekeningSemuaTw) && $rekapRekeningSemuaTw->isNotEmpty())
                        <tfoot class="bg-gray-100 font-bold text-[12px]">
                            <tr>
                                <td colspan="2" class="px-5 py-4 text-right uppercase tracking-widest text-gray-600">
                                    Grand Total
                                </td>
                                <td class="px-3 py-4 text-right font-mono text-purple-700">
                                    {{ number_format($rekapRekeningSemuaTw->sum('tw1'), 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-4 text-right font-mono text-purple-700">
                                    {{ number_format($rekapRekeningSemuaTw->sum('tw2'), 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-4 text-right font-mono text-purple-700">
                                    {{ number_format($rekapRekeningSemuaTw->sum('tw3'), 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-4 text-right font-mono text-purple-700">
                                    {{ number_format($rekapRekeningSemuaTw->sum('tw4'), 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono text-purple-900 text-sm bg-gray-200/50">
                                    {{ number_format($rekapRekeningSemuaTw->sum('total_anggaran'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>