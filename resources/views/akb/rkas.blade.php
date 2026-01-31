<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Matriks Anggaran Kas (AKB) - ') . ($dataRkas->first()->tahun ?? '') }}
        </h2>
    </x-slot>

    {{-- Filter Form --}}
    <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('akb.rincian') }}" method="GET" class="flex flex-wrap items-end gap-4">

            {{-- Filter Fokus Triwulan (BARU) --}}
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

            {{-- Mode Tampilan --}}
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
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">Filter</button>
                <a href="{{ route('akb.rincian') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- LOGIC PHP UNTUK MENENTUKAN KOLOM YANG TAMPIL --}}
    @php
    // Definisi Bulan per Triwulan
    $mapTriwulan = [
    1 => [1, 2, 3],
    2 => [4, 5, 6],
    3 => [7, 8, 9],
    4 => [10, 11, 12],
    ];

    // Ambil input filter
    $filterTw = request('filter_tw');
    $tampilan = request('tampilan', 'bulanan');

    // Tentukan Kolom Bulan yang akan di-loop
    $targetMonths = [];
    if ($filterTw) {
    // Jika memfilter TW tertentu, ambil bulan-bulan milik TW tersebut
    $targetMonths = $mapTriwulan[$filterTw];
    } else {
    // Jika tidak, ambil semua bulan (1-12)
    $targetMonths = range(1, 12);
    }

    // Tentukan Kolom Triwulan yang akan di-loop
    $targetQuarters = [];
    if ($filterTw) {
    // Jika memfilter TW tertentu, array hanya berisi TW tersebut
    $targetQuarters = [$filterTw];
    } else {
    // Jika tidak, ambil semua TW (1-4)
    $targetQuarters = [1, 2, 3, 4];
    }
    @endphp

    <div class="py-4">
        <div class="mx-auto">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-[11px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1 align-middle text-left w-64">
                                Komponen / Rekening</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1 align-middle text-center w-24">Spek
                            </th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1 align-middle text-center w-24">Harga
                                Satuan
                            </th>
                            {{-- Total RKAS Tetap menampilkan Total Setahun Penuh (Anggaran Murni) --}}
                            <th rowspan="2"
                                class="border border-gray-300 px-2 py-1 align-middle text-right w-28 bg-gray-50">Total
                                RKAS<br><span class="text-[9px] font-normal">(Setahun)</span></th>

                            @if($tampilan == 'triwulan')
                            <th colspan="{{ count($targetQuarters) }}"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50">
                                {{ $filterTw ? 'Rincian Triwulan ' . $filterTw : 'Rincian Per Triwulan' }}
                            </th>
                            @else
                            <th colspan="{{ count($targetMonths) }}"
                                class="border border-gray-300 px-2 py-1 text-center bg-blue-50">
                                {{ $filterTw ? 'Rincian Bulan (TW ' . $filterTw . ')' : 'Rincian Per Bulan (Jan - Des)'
                                }}
                            </th>
                            @endif
                        </tr>
                        <tr>
                            @if($tampilan == 'triwulan')
                            {{-- Loop berdasarkan Filter Quarter --}}
                            @foreach($targetQuarters as $tw)
                            <th class="border border-gray-300 px-1 py-1 text-center w-28">TW {{ $tw }}</th>
                            @endforeach
                            @else
                            {{-- Loop berdasarkan Filter Month --}}
                            @foreach ($targetMonths as $m)
                            <th class="border border-gray-300 px-1 py-1 text-center w-16">
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                            </th>
                            @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataRkas as $rkas)
                        @php
                        // Hitung total nominal HANYA untuk bulan-bulan yang sedang ditampilkan ($targetMonths)
                        // Jika tidak ada filter TW, $targetMonths adalah 1-12 (Setahun)
                        // Jika filter TW 1, $targetMonths adalah 1-3
                        $totalTampil = $rkas->akbrincis->whereIn('bulan', $targetMonths)->sum('nominal');
                        @endphp

                        {{-- Jika total pada periode yang dipilih adalah 0, lewati baris ini --}}
                        @if($totalTampil == 0)
                        @continue
                        @endif
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <div class="font-bold text-gray-800">{{ $rkas->namakomponen }}</div>
                                <div class="text-[10px] text-gray-500 uppercase italic">{{ $rkas->korek->singkat ?? '-'
                                    }}</div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center">{{ $rkas->spek }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-right font-bold ">
                                {{ number_format($rkas->hargasatuan, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-right font-bold bg-yellow-50">
                                {{ number_format($rkas->totalharga, 0, ',', '.') }}
                            </td>

                            @if($tampilan == 'triwulan')
                            {{-- LOOP DATA TRIWULAN --}}
                            @foreach($targetQuarters as $tw)
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                {{-- Ambil bulan-bulan milik TW ini --}}
                                @php
                                $monthsInTw = $mapTriwulan[$tw];
                                $sumTw = $rkas->akbrincis->whereIn('bulan', $monthsInTw)->sum('nominal');
                                @endphp
                                {{ $sumTw > 0 ? number_format($sumTw, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @else
                            {{-- LOOP DATA BULANAN --}}
                            @foreach ($targetMonths as $m)
                            <td class="border border-gray-300 px-1 py-1 text-right">
                                @php $akb = $rkas->akbrincis->firstWhere('bulan', $m); @endphp
                                {{ $akb && $akb->nominal > 0 ? number_format($akb->nominal, 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    {{-- FOOTER TOTAL --}}
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <tr>
                            <td colspan="3" class="border border-gray-300 px-2 py-2 text-center uppercase">Total</td>
                            {{-- Total Keseluruhan (RKAS) --}}
                            <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-100 text-indigo-700">
                                {{ number_format($dataRkas->sum('totalharga'), 0, ',', '.') }}
                            </td>

                            @if($tampilan == 'triwulan')
                            {{-- TOTAL PER KOLOM TRIWULAN --}}
                            @foreach($targetQuarters as $tw)
                            <td class="border border-gray-300 px-2 py-2 text-right text-indigo-700">
                                @php
                                $monthsInTw = $mapTriwulan[$tw];
                                $totalPerTw = $dataRkas->flatMap->akbrincis->whereIn('bulan',
                                $monthsInTw)->sum('nominal');
                                @endphp
                                {{ number_format($totalPerTw, 0, ',', '.') }}
                            </td>
                            @endforeach
                            @else
                            {{-- TOTAL PER KOLOM BULAN --}}
                            @foreach ($targetMonths as $m)
                            <td class="border border-gray-300 px-1 py-2 text-right text-indigo-700">
                                {{ number_format($dataRkas->flatMap->akbrincis->where('bulan', $m)->sum('nominal'), 0,
                                ',', '.') }}
                            </td>
                            @endforeach
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
