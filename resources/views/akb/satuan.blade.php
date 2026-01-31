<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Rincian AKB - ') . ($anggaran->tahun ?? '-') }}
            </h2>
            {{-- Tombol Export Excel (Opsional) --}}
            <a href="{{ route('akb.export_excel') }}" class=" bg-green-600 hover:bg-green-700 text-white font-bold py-2
                px-4 rounded text-sm">
                Export Excel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Program</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Kegiatan</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Sub Kegiatan</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Keterangan</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Kode Rekening</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Nama Komponen</th>
                                    <th
                                        class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider border">
                                        Spesifikasi</th>
                                    <th
                                        class="px-3 py-3 text-center font-medium text-gray-500 uppercase tracking-wider border">
                                        Satuan</th>
                                    <th
                                        class="px-3 py-3 text-right font-medium text-gray-500 uppercase tracking-wider border">
                                        Harga</th>
                                    <th
                                        class="px-3 py-3 text-center font-medium text-gray-500 uppercase tracking-wider border">
                                        Pajak</th>

                                    {{-- Loop Header Bulan 1-12 --}}
                                    @for ($i = 1; $i <= 12; $i++) <th
                                        class="px-2 py-3 text-center font-medium text-blue-600 uppercase tracking-wider border bg-blue-50">
                                        Bln {{ $i }}</th>
                                        @endfor

                                        <th
                                            class="px-3 py-3 text-center font-medium text-gray-500 uppercase tracking-wider border bg-gray-100">
                                            Tot Vol</th>
                                        <th
                                            class="px-3 py-3 text-right font-medium text-gray-500 uppercase tracking-wider border bg-gray-100">
                                            Total Harga</th>
                                        <th
                                            class="px-3 py-3 text-center font-medium text-gray-500 uppercase tracking-wider border">
                                            Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($rkas as $item)
                                {{-- LOGIC PHP (Sama seperti di Export Class) --}}
                                @php
                                $volAkb = $item->akb->volume ?? 0;
                                $totalVolRinci = $item->akbRincis->sum('volume');
                                $selisih = $volAkb - $totalVolRinci;

                                // Penentuan Status & Warna Badge
                                $statusText = '';
                                $statusClass = '';

                                if ($selisih == 0 && $volAkb > 0) {
                                $statusText = 'MATCH ' . $volAkb;
                                $statusClass = 'bg-green-100 text-green-800';
                                } elseif ($selisih > 0) {
                                $statusText = 'SELISIH (' . number_format($selisih, 0) . ')';
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                } elseif ($volAkb == 0 && $totalVolRinci == 0) {
                                $statusText = 'EMPTY';
                                $statusClass = 'bg-gray-100 text-gray-800';
                                } else {
                                $statusText = 'OVER';
                                $statusClass = 'bg-red-100 text-red-800';
                                }
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap border">{{ $item->kegiatan->snp ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 border min-w-[150px]">{{ $item->kegiatan->namagiat }}</td>
                                    <td class="px-3 py-2 border min-w-[150px]">{{ $item->giatsubteks ?? '-' }}</td>
                                    <td class="px-3 py-2 border min-w-[150px]">{{ $item->keterangan ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap border font-mono">{{ $item->korek->singkat ??
                                        '-' }}</td>
                                    <td class="px-3 py-2 border">{{ $item->namakomponen ?? '-' }}</td>
                                    <td class="px-3 py-2 border">{{ $item->spek ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center border">{{ $item->satuan ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap border">
                                        {{ number_format($item->hargasatuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center border">
                                        @if($item->totalpajak > 0)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">PPN</span>
                                        @else
                                        -
                                        @endif
                                    </td>

                                    {{-- Loop Data Bulan 1-12 --}}
                                    @for ($m = 1; $m <= 12; $m++) @php $rowBulan=$item->akbRincis->firstWhere('bulan',
                                        $m);
                                        $valBulan = $rowBulan ? $rowBulan->volume : 0;
                                        @endphp
                                        <td
                                            class="px-2 py-2 text-center border {{ $valBulan > 0 ? 'font-bold bg-blue-50 text-blue-700' : 'text-gray-300' }}">
                                            {{ $valBulan }}
                                        </td>
                                        @endfor

                                        <td class="px-3 py-2 text-center font-bold border bg-gray-50">
                                            {{ $totalVolRinci }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-bold border bg-gray-50 whitespace-nowrap">
                                            {{ number_format($item->totalharga, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-center border">
                                            <span
                                                class="px-2 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="25" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data rincian AKB untuk anggaran ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
