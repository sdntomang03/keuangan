<x-app-layout>
    <x-slot name="header">

    </x-slot>

    {{-- Filter Form (Sama seperti sebelumnya) --}}
    <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('rkas.anggaran') }}" method="GET" class="flex flex-wrap items-end gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 font-bold text-indigo-600">Mode Tampilan</label>
                <select name="tampilan" class="mt-1 block w-40 rounded-md border-indigo-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-indigo-50">
                    <option value="bulanan" {{ request('tampilan') == 'bulanan' ? 'selected' : '' }}>Bulanan (1-12)</option>
                    <option value="triwulan" {{ request('tampilan') == 'triwulan' ? 'selected' : '' }}>Triwulan (TW)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">Filter</button>

            </div>
        </form>
    </div>

    <div class="py-4">
        <div class="mx-auto">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-[11px]">
    <thead class="bg-gray-100 font-bold">
        <tr>
            <th rowspan="2" class="border border-gray-300 px-2 py-1">Kegiatan / Kode Rekening</th>
            <th rowspan="2" class="border border-gray-300 px-2 py-1 w-32">Total Anggaran</th>
            @if(request('tampilan') == 'triwulan')
                <th colspan="4" class="border border-gray-300 px-2 py-1 text-center bg-blue-50">Rekap Per Triwulan</th>
            @else
                <th colspan="12" class="border border-gray-300 px-2 py-1 text-center bg-blue-50">Rekap Per Bulan</th>
            @endif
        </tr>
        <tr>
            @if(request('tampilan') == 'triwulan')
                @foreach(['TW 1', 'TW 2', 'TW 3', 'TW 4'] as $tw)
                    <th class="border border-gray-300 px-1 py-1 text-center w-28">{{ $tw }}</th>
                @endforeach
            @else
                @for ($i = 1; $i <= 12; $i++)
                    <th class="border border-gray-300 px-1 py-1 text-center w-16">{{ $i }}</th>
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
            <tr class="bg-gray-200 font-bold italic">
                <td colspan="{{ request('tampilan') == 'triwulan' ? 6 : 14 }}" class="border border-gray-300 px-2 py-2 text-indigo-900">
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
                                @php $sumTw = $items->flatMap->akbrincis->whereIn('bulan', $months)->sum('nominal'); @endphp
                                {{ $sumTw > 0 ? number_format($sumTw, 0, ',', '.') : '-' }}
                            </td>
                        @endforeach
                    @else
                        @for ($m = 1; $m <= 12; $m++)
                            <td class="border border-gray-300 px-1 py-2 text-right text-[10px]">
                                @php $sumBulan = $items->flatMap->akbrincis->where('bulan', $m)->sum('nominal'); @endphp
                                {{ $sumBulan > 0 ? number_format($sumBulan, 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                    @endif
                </tr>
            @endforeach

            {{-- BARIS JUMLAH TOTAL PER KEGIATAN --}}
            <tr class="bg-blue-100 font-bold border-b-2 border-gray-400">
                <td class="border border-gray-300 px-2 py-2 text-right text-blue-900 uppercase">
                    Jumlah Per Kegiatan
                </td>
                <td class="border border-gray-300 px-2 py-2 text-right text-blue-900">
                    {{ number_format($allRkasInKegiatan->sum('totalharga'), 0, ',', '.') }}
                </td>

                @if(request('tampilan') == 'triwulan')
                    @php $tws = [1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12]]; @endphp
                    @foreach($tws as $months)
                        <td class="border border-gray-300 px-2 py-2 text-right text-blue-900 bg-blue-50/50">
                            {{ number_format($allRkasInKegiatan->flatMap->akbrincis->whereIn('bulan', $months)->sum('nominal'), 0, ',', '.') }}
                        </td>
                    @endforeach
                @else
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="border border-gray-300 px-1 py-2 text-right text-blue-900 bg-blue-50/50">
                            {{ number_format($allRkasInKegiatan->flatMap->akbrincis->where('bulan', $m)->sum('nominal'), 0, ',', '.') }}
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
    <tfoot class="bg-gray-800 text-white font-bold">
        <tr>
            <td class="border border-gray-300 px-4 py-3 text-center uppercase">Grand Total Keseluruhan</td>
            <td class="border border-gray-300 px-2 py-3 text-right">
                {{ number_format($dataRkas->flatten()->sum('totalharga'), 0, ',', '.') }}
            </td>
            @if(request('tampilan') == 'triwulan')
                @foreach([ [1,2,3], [4,5,6], [7,8,9], [10,11,12] ] as $months)
                    <td class="border border-gray-300 px-2 py-3 text-right text-yellow-400">
                        {{ number_format($dataRkas->flatten()->flatMap->akbrincis->whereIn('bulan', $months)->sum('nominal'), 0, ',', '.') }}
                    </td>
                @endforeach
            @else
                @for ($m = 1; $m <= 12; $m++)
                    <td class="border border-gray-300 px-1 py-3 text-right text-yellow-400">
                        {{ number_format($dataRkas->flatten()->flatMap->akbrincis->where('bulan', $m)->sum('nominal'), 0, ',', '.') }}
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
