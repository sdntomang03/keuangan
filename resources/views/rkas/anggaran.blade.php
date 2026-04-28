<x-app-layout>
    <x-slot name="header">
    </x-slot>

    {{-- Tambahan CSS khusus Print --}}
    <style>
        @media print {
            body {
                /* Memaksa browser mencetak warna background tabel */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                /* Set ukuran kertas otomatis Landscape agar tabel bulanan muat */
                size: landscape;
                margin: 1cm;
            }

            /* Menyembunyikan elemen navigasi dari layout utama (opsional, tergantung struktur x-app-layout) */
            header,
            nav,
            aside {
                display: none !important;
            }
        }
    </style>

    {{-- Filter Form (Ditambahkan print:hidden agar tidak ikut tercetak) --}}
    <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200 print:hidden">
        <form action="{{ route('rkas.anggaran') }}" method="GET" class="flex flex-wrap items-end gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 font-bold text-indigo-600">Mode Tampilan</label>
                <select name="tampilan"
                    class="mt-1 block w-40 rounded-md border-indigo-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-indigo-50">
                    <option value="bulanan" {{ request('tampilan')=='bulanan' ? 'selected' : '' }}>Bulanan (1-12)
                    </option>
                    <option value="triwulan" {{ request('tampilan')=='triwulan' ? 'selected' : '' }}>Triwulan (TW)
                    </option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm transition-colors">
                    Filter
                </button>

                <button type="button" onclick="window.print()"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Laporan
                </button>
            </div>
        </form>
    </div>

    {{-- Area Tabel --}}
    <div class="py-4 print:py-0">
        <div class="mx-auto">
            <div
                class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto print:shadow-none print:p-0 print:overflow-visible">

                <div class="hidden print:block text-center mb-6">
                    <h2 class="text-xl font-bold uppercase">Laporan Anggaran RKAS</h2>
                    <p class="text-sm text-gray-600">Mode: {{ request('tampilan') == 'triwulan' ? 'Triwulan' : 'Bulanan'
                        }}</p>
                </div>

                <table class="w-full border-collapse border border-gray-300 text-[11px] print:text-[10px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1">Kegiatan / Kode Rekening</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1 w-32">Total Anggaran</th>
                            @if(request('tampilan') == 'triwulan')
                            <th colspan="4"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50 print:bg-blue-50">Rekap
                                Per Triwulan</th>
                            @else
                            <th colspan="12"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50 print:bg-blue-50">Rekap
                                Per Bulan</th>
                            @endif
                        </tr>
                        <tr>
                            @if(request('tampilan') == 'triwulan')
                            @foreach(['TW 1', 'TW 2', 'TW 3', 'TW 4'] as $tw)
                            <th class="border border-gray-300 px-1 py-1 text-center w-28">{{ $tw }}</th>
                            @endforeach
                            @else
                            @for ($i = 1; $i <= 12; $i++) <th class="border border-gray-300 px-1 py-1 text-center w-16">
                                {{ $i }}</th>
                                @endfor
                                @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataRkas as $idbl => $groupsByKorek)
                        @php
                        $firstItem = $groupsByKorek->first()->first();
                        $namaKegiatan = $firstItem->kegiatan->namagiat ?? 'Kegiatan #'.$idbl;

                        // Menyiapkan data koleksi untuk total per kegiatan
                        $allRkasInKegiatan = $groupsByKorek->flatten();
                        @endphp

                        {{-- HEADER JUDUL KEGIATAN --}}
                        <tr class="bg-gray-200 font-bold italic print:bg-gray-200">
                            <td colspan="{{ request('tampilan') == 'triwulan' ? 6 : 14 }}"
                                class="border border-gray-300 px-2 py-2 text-indigo-900">
                                KEGIATAN: {{ $namaKegiatan }}
                            </td>
                        </tr>

                        {{-- LOOP REKAP PER KOREK --}}
                        @foreach ($groupsByKorek as $kodeakun => $items)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-6 py-2">
                                <div class="text-gray-500">{{ $items->first()->namaakun }}</div>
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right font-semibold">
                                {{ number_format($items->sum('totalharga'), 0, ',', '.') }}
                            </td>

                            @if(request('tampilan') == 'triwulan')
                            @php $tws = [1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12]]; @endphp
                            @foreach($tws as $months)
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                @php $sumTw = $items->flatMap->akbrincis->whereIn('bulan', $months)->sum('nominal');
                                @endphp
                                {{ $sumTw > 0 ? number_format($sumTw, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @else
                            @for ($m = 1; $m <= 12; $m++) <td
                                class="border border-gray-300 px-1 py-2 text-right text-[10px]">
                                @php $sumBulan = $items->flatMap->akbrincis->where('bulan', $m)->sum('nominal'); @endphp
                                {{ $sumBulan > 0 ? number_format($sumBulan, 0, ',', '.') : '-' }}
                                </td>
                                @endfor
                                @endif
                        </tr>
                        @endforeach

                        {{-- BARIS JUMLAH TOTAL PER KEGIATAN --}}
                        <tr class="bg-blue-100 font-bold border-b-2 border-gray-400 print:bg-blue-100">
                            <td class="border border-gray-300 px-2 py-2 text-right text-blue-900 uppercase">
                                Jumlah Per Kegiatan
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-blue-900">
                                {{ number_format($allRkasInKegiatan->sum('totalharga'), 0, ',', '.') }}
                            </td>

                            @if(request('tampilan') == 'triwulan')
                            @php $tws = [1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12]]; @endphp
                            @foreach($tws as $months)
                            <td
                                class="border border-gray-300 px-2 py-2 text-right text-blue-900 bg-blue-50/50 print:bg-blue-50">
                                {{ number_format($allRkasInKegiatan->flatMap->akbrincis->whereIn('bulan',
                                $months)->sum('nominal'), 0, ',', '.') }}
                            </td>
                            @endforeach
                            @else
                            @for ($m = 1; $m <= 12; $m++) <td
                                class="border border-gray-300 px-1 py-2 text-right text-blue-900 bg-blue-50/50 print:bg-blue-50">
                                {{ number_format($allRkasInKegiatan->flatMap->akbrincis->where('bulan',
                                $m)->sum('nominal'), 0, ',', '.') }}
                                </td>
                                @endfor
                                @endif
                        </tr>

                        @empty
                        <tr>
                            <td colspan="15" class="p-10 text-center text-gray-500 italic">Data tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- FOOTER GRAND TOTAL (TOTAL SEMUA KEGIATAN) --}}
                    <tfoot class="bg-gray-800 text-white font-bold print:bg-gray-800 print:text-black">
                        <tr>
                            <td class="border border-gray-300 px-4 py-3 text-center uppercase">Grand Total Keseluruhan
                            </td>
                            <td class="border border-gray-300 px-2 py-3 text-right">
                                {{ number_format($dataRkas->flatten()->sum('totalharga'), 0, ',', '.') }}
                            </td>
                            @if(request('tampilan') == 'triwulan')
                            @foreach([ [1,2,3], [4,5,6], [7,8,9], [10,11,12] ] as $months)
                            <td class="border border-gray-300 px-2 py-3 text-right text-yellow-400 print:text-gray-900">
                                {{ number_format($dataRkas->flatten()->flatMap->akbrincis->whereIn('bulan',
                                $months)->sum('nominal'), 0, ',', '.') }}
                            </td>
                            @endforeach
                            @else
                            @for ($m = 1; $m <= 12; $m++) <td
                                class="border border-gray-300 px-1 py-3 text-right text-yellow-400 print:text-gray-900">
                                {{ number_format($dataRkas->flatten()->flatMap->akbrincis->where('bulan',
                                $m)->sum('nominal'), 0, ',', '.') }}
                                </td>
                                @endfor
                                @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>