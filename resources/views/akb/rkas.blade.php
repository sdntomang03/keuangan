<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Matriks Anggaran Kas (AKB) - ') . ($dataRkas->first()->tahun ?? '') }}
        </h2>
    </x-slot>

    {{-- Filter Form (Sama seperti sebelumnya) --}}
    <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('coba.rkas') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">-- Semua Tahun --</option>
                    @foreach([2024, 2025, 2026] as $th)
                        <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Anggaran</label>
                <select name="jenis_anggaran" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">-- Semua Jenis --</option>
                    <option value="bos" {{ request('jenis_anggaran') == 'bos' ? 'selected' : '' }}>BOS</option>
                    <option value="bop" {{ request('jenis_anggaran') == 'bop' ? 'selected' : '' }}>BOP</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 font-bold text-indigo-600">Mode Tampilan</label>
                <select name="tampilan" class="mt-1 block w-40 rounded-md border-indigo-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-indigo-50">
                    <option value="bulanan" {{ request('tampilan') == 'bulanan' ? 'selected' : '' }}>Bulanan (1-12)</option>
                    <option value="triwulan" {{ request('tampilan') == 'triwulan' ? 'selected' : '' }}>Triwulan (TW)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">Filter</button>
                <a href="{{ route('coba.rkas') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
            </div>
                    <a href="{{ route('akb.export_excel', request()->all()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </a>
        </form>
    </div>

    <div class="py-4">
        <div class="mx-auto">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-[11px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1">Komponen / Rekening</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1">Spek</th>
                            <th rowspan="2" class="border border-gray-300 px-2 py-1 w-28">Total RKAS</th>
                            @if(request('tampilan') == 'triwulan')
                                <th colspan="4" class="border border-gray-300 px-2 py-1 text-center bg-blue-50">Rincian Per Triwulan</th>
                            @else
                                <th colspan="12" class="border border-gray-300 px-2 py-1 text-center bg-blue-50">Bulan (Rincian AKB)</th>
                            @endif
                        </tr>
                        <tr>
                            @if(request('tampilan') == 'triwulan')
                                @foreach(['TW 1', 'TW 2', 'TW 3', 'TW 4'] as $tw)
                                    <th class="border border-gray-300 px-1 py-1 text-center w-32">{{ $tw }}</th>
                                @endforeach
                            @else
                                @for ($i = 1; $i <= 12; $i++)
                                    <th class="border border-gray-300 px-1 py-1 text-center w-20">{{ $i }}</th>
                                @endfor
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataRkas as $rkas)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1">
                                    <div class="font-bold text-gray-800">{{ $rkas->namakomponen }}</div>
                                    <div class="text-[8px] text-neutral-300 bg-slate-600 uppercase">{{ $rkas->korek->singkat ?? '-' }}</div>
                                </td>
                                <td class="border border-gray-300 text-xs px-2 py-1">{{ $rkas->spek }}</td>
                                <td class="border border-gray-300 px-2 py-1 text-right font-bold bg-yellow-50">
                                    {{ number_format($rkas->totalharga, 0, ',', '.') }}
                                </td>

                                @if(request('tampilan') == 'triwulan')
                                    @php $tws = [1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12]]; @endphp
                                    @foreach($tws as $months)
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            @php $sumTw = $rkas->akbrincis->whereIn('bulan', $months)->sum('nominal'); @endphp
                                            {{ $sumTw > 0 ? number_format($sumTw, 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                @else
                                    @for ($m = 1; $m <= 12; $m++)
                                        <td class="border border-gray-300 px-1 py-1 text-right">
                                            @php $akb = $rkas->akbrincis->firstWhere('bulan', $m); @endphp
                                            {{ $akb ? number_format($akb->nominal, 0, ',', '.') : '-' }}
                                        </td>
                                    @endfor
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- BARIS TOTAL --}}
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <tr>
                            <td colspan="2" class="border border-gray-300 px-2 py-2 text-center uppercase">Total Keseluruhan</td>
                            <td class="border border-gray-300 px-2 py-2 text-right bg-yellow-100 text-indigo-700">
                                {{ number_format($dataRkas->sum('totalharga'), 0, ',', '.') }}
                            </td>
                            
                            @if(request('tampilan') == 'triwulan')
                                @php $tws = [1 => [1,2,3], 2 => [4,5,6], 3 => [7,8,9], 4 => [10,11,12]]; @endphp
                                @foreach($tws as $months)
                                    <td class="border border-gray-300 px-2 py-2 text-right text-indigo-700">
                                        {{ number_format($dataRkas->flatMap->akbrincis->whereIn('bulan', $months)->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                @endforeach
                            @else
                                @for ($m = 1; $m <= 12; $m++)
                                    <td class="border border-gray-300 px-1 py-2 text-right text-indigo-700">
                                        {{ number_format($dataRkas->flatMap->akbrincis->where('bulan', $m)->sum('nominal'), 0, ',', '.') }}
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