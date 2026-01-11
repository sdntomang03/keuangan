<x-app-layout>
    <div class="py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                
                <h2 class="text-xl font-bold mb-4 text-gray-800 uppercase tracking-wider border-b pb-2">
                    Distribusi Anggaran Kas Bulanan (AKB)
                </h2>

                <div class="overflow-x-auto border rounded-lg shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                        <thead class="bg-gray-100 font-bold text-gray-700 uppercase">
                            <tr>
                                <th class="px-3 py-3 border-r sticky left-0 bg-gray-100 z-10" style="min-width: 250px;">Kegiatan & Komponen</th>
                                <th class="px-2 py-3 border-r">Akun</th>
                                <th class="px-2 py-3 border-r bg-indigo-50">Total RKAS</th>
                                @for($i = 1; $i <= 12; $i++)
                                    <th class="px-2 py-3 border-r w-24">Bulan {{ $i }}</th>
                                @endfor
                                <th class="px-2 py-3 bg-green-50">Sisa</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border-r sticky left-0 bg-white z-10 shadow-sm">
                                    <div class="font-bold text-indigo-700">{{ $item->kegiatan?->snp ?? 'SNP' }}</div>
                                    <div class="text-gray-900 font-medium truncate w-56" title="{{ $item->namakomponen }}">
                                        {{ $item->namakomponen }}
                                    </div>
                                    <div class="text-[9px] text-gray-400 italic">{{ $item->spek }}</div>
                                </td>

                                <td class="px-2 py-2 border-r text-center font-bold text-green-700">
                                    {{ $item->korek?->singkat ?? '-' }}
                                </td>

                                <td class="px-2 py-2 border-r bg-indigo-50 text-right font-bold">
                                    {{ number_format($item->totalharga, 0, ',', '.') }}
                                </td>

                                @for($i = 1; $i <= 12; $i++)
                                    @php $val = $item->akb?->{"bulan$i"} ?? 0; @endphp
                                    <td class="px-2 py-2 border-r text-right {{ $val > 0 ? 'text-blue-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}
                                    </td>
                                @endfor

                                @php
                                    $sisa = $item->totalharga - ($item->akb?->totalakb ?? 0);
                                @endphp
                                <td class="px-2 py-2 text-right bg-green-50 font-bold {{ $sisa != 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($sisa, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="px-4 py-10 text-center text-gray-500">Data Tidak Ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $data->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>