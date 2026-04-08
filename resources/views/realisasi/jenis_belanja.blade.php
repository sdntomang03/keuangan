<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight uppercase">
                    Realisasi per Jenis Belanja
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    Tahun Anggaran {{ $anggaran->tahun }} — {{ strtoupper($sekolah->nama_sekolah) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 border border-indigo-200 text-indigo-700 shadow-sm uppercase">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $anggaran->nama_anggaran }}
                </span>
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-800 text-white shadow-sm uppercase">
                    <svg class="w-4 h-4 mr-1.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ $periodeText }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-[11px] min-w-[1000px]">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-4 text-left font-bold min-w-[250px]">Komponen / Spesifikasi</th>
                                <th class="px-3 py-4 text-right font-bold w-24 border-l border-gray-700">Harga Satuan
                                </th>
                                <th class="px-3 py-4 text-center font-bold w-20 border-l border-gray-700">Vol Angg</th>
                                <th class="px-3 py-4 text-center font-bold w-20">Vol Real</th>
                                <th class="px-3 py-4 text-center font-bold w-16">Satuan</th>
                                <th class="px-4 py-4 text-right font-bold w-28 border-l border-gray-700">Pagu (A)</th>
                                <th class="px-4 py-4 text-right font-bold w-28">Realisasi (B)</th>
                                <th class="px-4 py-4 text-right font-bold w-28">Sisa</th>
                                <th class="px-3 py-4 text-center font-bold w-16 border-l border-gray-700">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataRkas as $jenisBelanja => $perAkun)
                            @php
                            // Hitung Subtotal per Jenis Belanja (Operasional, Mesin, dll)
                            $subTotalAnggaran = $perAkun->flatten()->sum('total_anggaran');
                            $subTotalRealisasi = $perAkun->flatten()->sum('total_realisasi');
                            $subTotalSisa = $subTotalAnggaran - $subTotalRealisasi;
                            $subPersen = $subTotalAnggaran > 0 ? ($subTotalRealisasi / $subTotalAnggaran) * 100 : 0;
                            @endphp

                            <tr class="bg-indigo-900 text-white border-b-2 border-indigo-950">
                                <td class="px-4 py-3 font-black text-[13px] uppercase tracking-widest" colspan="5">
                                    <div class="flex items-center">
                                        <span class="bg-indigo-700 p-1 rounded mr-3">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                                </path>
                                            </svg>
                                        </span>
                                        JENIS BELANJA: {{ $jenisBelanja }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold border-l border-indigo-800">{{
                                    number_format($subTotalAnggaran, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-yellow-300">{{
                                    number_format($subTotalRealisasi, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($subTotalSisa, 0, ',', '.')
                                    }}</td>
                                <td class="px-3 py-3 text-center font-bold border-l border-indigo-800">{{
                                    number_format($subPersen, 1) }}%</td>
                            </tr>

                            @foreach($perAkun as $kodeakun => $items)
                            @php
                            $namaRekening = $items->first()->korek->uraian_singkat ?? 'Tanpa Rekening';
                            @endphp
                            <tr class="bg-indigo-50/80 border-b border-indigo-100">
                                <td colspan="9" class="px-4 py-2.5 pl-8">
                                    <div class="flex items-center">
                                        <span
                                            class="font-mono text-[11px] font-bold text-indigo-700 bg-white border border-indigo-200 px-2 py-0.5 rounded shadow-sm mr-3">{{
                                            $kodeakun }}</span>
                                        <span class="font-bold text-indigo-900 text-[11px] uppercase tracking-wide">{{
                                            $namaRekening }}</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach($items as $item)
                            @php
                            $anggaranVal = $item->total_anggaran ?? 0;
                            $realisasiVal = $item->total_realisasi ?? 0;
                            $sisaVal = $anggaranVal - $realisasiVal;
                            $volAngg = $item->total_volume_anggaran ?? 0;
                            $volReal = $item->volume_realisasi ?? 0;
                            $persen = $anggaranVal > 0 ? ($realisasiVal / $anggaranVal) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                                <td class="px-4 py-3 align-top pl-14">
                                    <div class="font-bold text-gray-800 text-[12px] leading-tight">{{
                                        $item->namakomponen }}</div>
                                    <div class="mt-1 flex flex-col gap-0.5">
                                        @if($item->spek) <div class="text-[10px] text-gray-500"><span
                                                class="font-semibold text-gray-600">Spek:</span> {{ $item->spek }}</div>
                                        @endif
                                        @if($item->koefisien) <div class="text-[10px] text-gray-400"><span
                                                class="font-semibold text-gray-500">Koef:</span> {{ $item->koefisien }}
                                        </div> @endif
                                    </div>
                                </td>

                                <td
                                    class="px-3 py-3 text-right font-mono text-gray-500 align-top border-l border-gray-100">
                                    {{ number_format($item->hargasatuan, 0, ',', '.') }}</td>
                                <td
                                    class="px-3 py-3 text-center align-top font-medium text-gray-600 border-l border-gray-100">
                                    {{ $volAngg }}</td>
                                <td class="px-3 py-3 text-center align-top font-bold text-indigo-600">{{ $volReal }}
                                </td>
                                <td class="px-3 py-3 text-center align-top text-gray-400 uppercase text-[10px]">{{
                                    $item->satuan }}</td>

                                <td
                                    class="px-4 py-3 text-right font-mono text-gray-600 align-top border-l border-gray-100">
                                    {{ number_format($anggaranVal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-indigo-600 align-top">{{
                                    number_format($realisasiVal, 0, ',', '.') }}</td>
                                <td
                                    class="px-4 py-3 text-right font-mono font-bold align-top {{ $sisaVal < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($sisaVal, 0, ',', '.') }}</td>

                                <td class="px-3 py-3 text-center align-top border-l border-gray-100">
                                    <span
                                        class="text-[10px] font-black {{ $persen > 100 ? 'text-white bg-red-500 px-1.5 py-0.5 rounded' : 'text-gray-600' }}">
                                        {{ number_format($persen, 0) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                            @empty
                            <tr>
                                <td colspan="9" class="py-16 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="mt-4 text-sm font-bold text-gray-400 uppercase tracking-widest">Data Tidak
                                        Ditemukan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        @if($dataRkas->isNotEmpty())
                        <tfoot class="bg-gray-800 text-white uppercase tracking-wider text-[12px]">
                            <tr>
                                <td colspan="5"
                                    class="px-4 py-4 font-black text-right border-r border-gray-700 tracking-widest">
                                    GRAND TOTAL PENGELUARAN
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold border-r border-gray-700">{{
                                    number_format($grandTotalAnggaran, 0, ',', '.') }}</td>
                                <td
                                    class="px-4 py-4 text-right font-mono font-bold text-yellow-300 border-r border-gray-700">
                                    {{ number_format($grandTotalRealisasi, 0, ',', '.') }}</td>
                                <td
                                    class="px-4 py-4 text-right font-mono font-bold {{ $grandTotalSisa < 0 ? 'text-red-400' : 'text-emerald-400' }} border-r border-gray-700">
                                    {{ number_format($grandTotalSisa, 0, ',', '.') }}</td>
                                <td class="px-3 py-4 text-center font-black">{{ number_format($grandPersen, 1) }}%</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>