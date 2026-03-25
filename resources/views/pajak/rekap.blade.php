<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Rekapitulasi Pajak</h2>
                    <p class="text-sm text-gray-500">Anggaran Aktif: <span class="font-bold">{{ $anggaran->nama_anggaran
                            ?? $anggaran->singkatan }}</span></p>
                </div>
                <a href="{{ route('pajak.siap-setor') }}"
                    class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="bg-white border border-gray-200 shadow-sm rounded-3xl overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest font-black text-gray-500">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Jenis Pajak</th>
                            <th class="px-6 py-4 text-right">Total Dipungut</th>
                            <th class="px-6 py-4 text-right">Total Disetor</th>
                            <th class="px-6 py-4 text-right">Sisa (Belum Disetor)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                        $total_pungut = 0;
                        $total_setor = 0;
                        $total_sisa = 0;
                        @endphp

                        @forelse($rekap as $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $data['nama_pajak'] }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">
                                Rp {{ number_format($data['dipungut'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-green-600 font-bold">
                                Rp {{ number_format($data['disetor'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-orange-600">
                                Rp {{ number_format($data['sisa'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @php
                        $total_pungut += $data['dipungut'];
                        $total_setor += $data['disetor'];
                        $total_sisa += $data['sisa'];
                        @endphp
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                Belum ada data rekap pajak pada anggaran ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if(count($rekap) > 0)
                    <tfoot class="bg-gray-100 border-t border-gray-200">
                        <tr>
                            <th colspan="2"
                                class="px-6 py-4 text-center text-xs uppercase tracking-widest font-black text-gray-800">
                                TOTAL KESELURUHAN
                            </th>
                            <th class="px-6 py-4 text-right font-black text-gray-800">
                                Rp {{ number_format($total_pungut, 0, ',', '.') }}
                            </th>
                            <th class="px-6 py-4 text-right font-black text-green-600">
                                Rp {{ number_format($total_setor, 0, ',', '.') }}
                            </th>
                            <th class="px-6 py-4 text-right font-black text-orange-600">
                                Rp {{ number_format($total_sisa, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>
</x-app-layout>