<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 2v-6m-9-9H7c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7l-5-5z">
                    </path>
                </svg>
                Monitoring Penarikan Dana — <span class="text-indigo-600 font-black">Triwulan {{ $triwulanAktif
                    }}</span>
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('npd.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-md transition duration-150">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat NPD Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Success --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="mb-6 bg-white border-l-4 border-green-500 p-4 shadow-sm flex justify-between items-center rounded-r-lg">
                <div class="flex items-center text-green-700 font-bold text-sm">
                    <svg class="w-5 h-5 me-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"></path>
                    </svg></button>
            </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-xs font-bold opacity-80 uppercase tracking-widest">Total Dana Ditarik (NPD)</p>
                        <p class="text-3xl font-black mt-1">Rp {{ number_format($totalPengajuan, 0, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Placeholder untuk card tambahan (misal realisasi belanja total) --}}
            </div>

            {{-- Main Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-800 text-white uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-5 text-left">NPD & Tanggal</th>
                                <th class="px-6 py-5 text-left">Kegiatan / Rekening</th>
                                <th class="px-6 py-5 text-right bg-gray-700">Pagu NPD (A)</th>
                                <th class="px-6 py-5 text-right text-indigo-300 italic">Realisasi Spj (B)</th>
                                <th class="px-6 py-5 text-right bg-indigo-900">Sisa Dana (A-B)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($listNpd as $npd)
                            @php
                            $realisasi = $npd->realisasi_nota ?? 0;
                            $sisa = $npd->nilai_npd - $realisasi;
                            @endphp
                            <tr class="hover:bg-indigo-50/50 transition duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <div class="font-black text-indigo-900">{{ $npd->nomor_npd }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono italic">{{
                                        $npd->tanggal->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="font-bold text-gray-800 uppercase text-[10px] line-clamp-1 group-hover:text-indigo-700 transition">
                                        {{ $npd->kegiatan->namagiat ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono italic">
                                        {{ $npd->korek->ket ?? '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 border-l border-gray-50">
                                    {{ number_format($npd->nilai_npd, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right text-indigo-600 font-medium bg-indigo-50/20">
                                    {{ number_format($realisasi, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right bg-gray-50">
                                    <span
                                        class="font-black text-base {{ $sisa > 0 ? 'text-orange-500' : 'text-green-600' }}">
                                        {{ number_format($sisa, 0, ',', '.') }}
                                    </span>
                                    @if($sisa > 0)
                                    <div class="text-[8px] font-bold text-orange-400 uppercase tracking-tighter">STS
                                    </div>
                                    @else
                                    <div
                                        class="flex justify-end items-center gap-1 text-[8px] font-bold text-green-500 uppercase tracking-tighter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Sesuai
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-24 text-center text-gray-400 bg-white">
                                    <div class="flex flex-col items-center">
                                        <div class="p-4 bg-gray-50 rounded-full mb-4">
                                            <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="font-bold text-gray-300 uppercase tracking-widest text-xs italic">Data
                                            pengajuan NPD belum tersedia untuk triwulan ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- Footer Totals --}}
                        @if($listNpd->isNotEmpty())
                        <tfoot class="bg-gray-800 text-white font-black uppercase text-[10px]">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-right tracking-widest">Total Halaman Ini</td>
                                <td class="px-6 py-4 text-right">Rp {{ number_format($listNpd->sum('nilai_npd'), 0, ',',
                                    '.') }}</td>
                                <td class="px-6 py-4 text-right text-indigo-300">Rp {{
                                    number_format($listNpd->sum('realisasi_nota'), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right bg-indigo-900">
                                    Rp {{ number_format($listNpd->sum('nilai_npd') - $listNpd->sum('realisasi_nota'), 0,
                                    ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <div class="px-6 py-4 bg-white border-t border-gray-100">
                    {{ $listNpd->links() }}
                </div>
            </div>

            <div class="mt-4 text-[10px] text-gray-400 italic">
                * Realisasi Nota (B) dihitung berdasarkan data belanja pada bulan-bulan dalam Triwulan {{ $triwulanAktif
                }} yang memiliki kode kegiatan & rekening yang sama.
            </div>
        </div>
    </div>
</x-app-layout>
