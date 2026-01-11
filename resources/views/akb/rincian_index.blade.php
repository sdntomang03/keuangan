<x-app-layout>
    <div class="py-12">
        <div class="max-w-[100%] mx-auto sm:px-4 lg:px-6">
            <div class="bg-white shadow-xl sm:rounded-lg p-6 overflow-hidden">
                <h2 class="text-xl font-bold mb-6 text-gray-800 uppercase border-b pb-4">
                    Integrasi Data Anggaran (RKAS - AKB - RINCI)
                </h2>
<div class="bg-gray-50 p-4 rounded-lg mb-6 shadow-sm border">
    <form action="{{ route('akb.indexrincian') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div class="w-full md:w-48">
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Tahun</label>
            <select name="tahun" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="">-- Semua Tahun --</option>
                @foreach($listTahun as $t)
                    <option value="{{ $t->tahun }}" {{ request('tahun') == $t->tahun ? 'selected' : '' }}>
                        {{ $t->tahun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full md:w-64">
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jenis Anggaran</label>
            <select name="jenis_anggaran" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="">-- Semua Anggaran --</option>
                @foreach($listAnggaran as $a)
                    <option value="{{ $a->jenis_anggaran }}" {{ request('jenis_anggaran') == $a->jenis_anggaran ? 'selected' : '' }}>
                        {{ $a->jenis_anggaran }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                Cari Data
            </button>
            <a href="{{ route('akb.export_excel', request()->all()) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export Excel
    </a>
            <a href="{{ route('akb.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition">
                Reset
            </a>
        </div>
    </form>
</div>
                <div class="overflow-x-auto border rounded-lg shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-[10px]">
                        <thead class="bg-gray-50 font-bold text-gray-700 uppercase">
                            <tr>
                                <th class="px-3 py-4 border-r sticky left-0 bg-gray-50 z-20">Info Kegiatan & Komponen</th>
                                <th class="px-2 py-4 border-r">Spek</th>
                                <th class="px-2 py-4 border-r">Akun (Korek)</th>
                                
                                <th class="px-2 py-4 border-r bg-yellow-50">AKB</th>
                             
                                @for($i = 1; $i <= 12; $i++)
                                    <th class="px-1 py-4 border-r w-14 text-center bg-gray-100">Bln {{ $i }}</th>
                                @endfor
                                   <th class="px-2 py-4 border-r bg-green-50">Total Rincian</th>
                                <th class="px-2 py-4 border-r">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($data as $item)
                            @php
                                $volRkas = $item->volume;
                                $volAkb = $item->akb->volume ?? 0;
                                $totalVolRinci = $item->akbRincis->sum('volume');
                                
                                $selisih = abs($volAkb - $totalVolRinci);
                                $isOk = $selisih < 0.0001;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-2 border-r sticky left-0 bg-white shadow-sm z-10">
                                    <span class="text-blue-600 font-bold block text-[9px]">{{ $item->kegiatan->snp ?? '-' }}</span>
                                    <div class="font-bold text-gray-900 truncate w-48">{{ $item->namakomponen }}</div>
                                    <div class="text-[9px] text-gray-400">{{ $item->kegiatan->namagiat ?? '-' }}</div>
                                </td>

                                <td class="px-2 py-2 border-r">
                                    {{ $item->spek ?? '-' }}
                                </td>
                                <td class="px-2 py-2 border-r text-center font-bold text-green-700 uppercase">
                                    {{ $item->korek->singkat ?? '-' }}
                                </td>

                        

                                <td class="px-2 py-2 border-r text-center bg-yellow-50/30 font-bold text-yellow-700">
                                    {{ number_format($volAkb, 2, ',', '.') }}
                                </td>

                                

                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $rowBulan = $item->akbRincis->firstWhere('bulan', $m);
                                    @endphp
                                    <td class="px-1 py-2 border-r text-center font-mono {{ $rowBulan ? 'text-blue-600 font-bold' : 'text-gray-300' }}">
                                        {{ $rowBulan ? number_format($rowBulan->volume, 2, ',', '.') : '-' }}
                                    </td>
                                @endfor
                                <td class="px-2 py-2 border-r text-center bg-green-50/30 font-bold text-green-700">
                                    {{ number_format($totalVolRinci, 2, ',', '.') }}
                                </td>

                                <td class="px-2 py-2 border-r text-center">
                                    @if($isOk && $totalVolRinci > 0)
                                        <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-bold text-[8px]">MATCH</span>
                                    @elseif($totalVolRinci == 0)
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-bold text-[8px]">EMPTY</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-800 font-bold text-[8px]">DIFF</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $data->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>