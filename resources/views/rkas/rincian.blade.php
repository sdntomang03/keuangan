<x-app-layout>
    <div class="py-12">
        <div class="max-w-[98%] mx-auto sm:px-4 lg:px-6">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                
                <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-4 uppercase tracking-wider">
                    Monitoring Distribusi Anggaran 12 Bulan
                </h2>

                <div class="overflow-x-auto shadow-sm border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px]">
                        <thead class="bg-gray-100 font-bold text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left sticky left-0 bg-gray-100 z-10 border-r" style="min-width: 250px;">SNP & Kegiatan / Komponen</th>
                                <th class="px-3 py-3 text-left border-r">Akun</th>
                                <th class="px-3 py-3 text-center border-r">Koef</th>
                                <th class="px-4 py-3 text-right bg-indigo-50 border-r text-indigo-800">Total Harga</th>
                                
                                @for($i = 1; $i <= 12; $i++)
                                    <th class="px-2 py-3 text-center border-r w-28">Bulan {{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 sticky left-0 bg-white z-10 border-r shadow-sm">
                                    <span class="text-[9px] font-bold text-indigo-600 block uppercase tracking-tighter">
                                        {{ $item->kegiatan->snp ?? 'N/A' }}
                                    </span>
                                    <div class="font-bold text-gray-900 truncate w-56" title="{{ $item->namakomponen }}">
                                        {{ $item->namakomponen }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 italic truncate w-56">
                                        {{ $item->kegiatan->namagiat ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-3 border-r text-center">
                                    @if($item->korek)
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded font-bold uppercase text-[9px]">
                                            {{ $item->korek->singkat }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">{{ $item->kodeakun }}</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-center border-r text-gray-600">
                                    {{ $item->koefisien }}
                                </td>

                                <td class="px-4 py-3 text-right font-bold bg-indigo-50 border-r text-indigo-900">
                                    {{ number_format($item->totalharga, 0, ',', '.') }}
                                </td>

                                @for($m = 1; $m <= 12; $m++)
                                    @php 
                                        // Mengambil nilai nominal bulan dari relasi akb
                                        $nominalBulan = $item->akb->{"bulan$m"} ?? 0; 
                                    @endphp
                                    <td class="px-2 py-3 text-right border-r {{ $nominalBulan > 0 ? 'bg-blue-50 font-bold text-blue-800' : 'text-gray-300' }}">
                                        @if($nominalBulan > 0)
                                            {{ number_format($nominalBulan, 0, ',', '.') }}
                                        @else
                                            <span class="text-center block">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="px-4 py-10 text-center text-gray-500 font-medium">
                                    Data anggaran atau distribusi bulanan belum tersedia.
                                </td>
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