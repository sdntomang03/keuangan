<x-app-layout>
    {{-- Tambahan CSS khusus Print --}}
    <style>
        @media print {
            body {
                /* Memaksa browser mencetak warna background tabel */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white !important;
            }

            @page {
                /* Set ukuran kertas otomatis Landscape agar tabel 12 bulan muat */
                size: landscape;
                margin: 10mm;
            }

            /* Menyembunyikan navigasi bawaan dari layout utama */
            header,
            nav,
            aside {
                display: none !important;
            }

            .max-w-\\[95\\%\\] {
                max-width: 100% !important;
            }
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 print:px-0">
            <div class="bg-white shadow-xl sm:rounded-lg p-6 print:shadow-none print:p-0">

                <div class="flex justify-between items-end border-b pb-4 mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">
                            Distribusi Anggaran Kas Bulanan (AKB)
                        </h2>
                        <p class="text-gray-600 font-bold text-sm mt-1">
                            Unit Sekolah: {{ auth()->user()->sekolah->nama_sekolah ?? auth()->user()->name ?? 'Nama
                            Sekolah Tidak Tersedia' }}
                        </p>
                    </div>

                    <button type="button" onclick="window.print()"
                        class="print:hidden bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak AKB
                    </button>
                </div>

                <div
                    class="overflow-x-auto border rounded-lg shadow-sm print:border-none print:shadow-none print:overflow-visible">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px] print:text-[10px]">
                        <thead class="bg-gray-100 font-bold text-gray-700 uppercase print:bg-gray-200">
                            <tr>
                                <th class="px-3 py-3 border-r sticky left-0 bg-gray-100 print:bg-gray-200 z-10"
                                    style="min-width: 250px;">Kegiatan & Komponen</th>
                                <th class="px-2 py-3 border-r">Akun</th>
                                <th class="px-2 py-3 border-r bg-indigo-50 print:bg-indigo-100">Total RKAS</th>
                                @for($i = 1; $i <= 12; $i++) <th class="px-2 py-3 border-r w-24">Bulan {{ $i }}</th>
                                    @endfor
                                    <th class="px-2 py-3 bg-green-50 print:bg-green-100">Sisa</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $item)
                            <tr class="hover:bg-gray-50">
                                <td
                                    class="px-3 py-2 border-r sticky left-0 bg-white z-10 shadow-sm print:shadow-none border-b">
                                    <div class="font-bold text-indigo-700">{{ $item->kegiatan?->snp ?? 'SNP' }}</div>
                                    <div class="text-gray-900 font-medium truncate w-56 print:whitespace-normal print:w-auto"
                                        title="{{ $item->namakomponen }}">
                                        {{ $item->namakomponen }}
                                    </div>
                                    <div class="text-[9px] text-gray-400 italic">{{ $item->spek }}</div>
                                </td>

                                <td class="px-2 py-2 border-r text-center font-bold text-green-700 border-b">
                                    {{ $item->korek?->singkat ?? '-' }}
                                </td>

                                <td
                                    class="px-2 py-2 border-r bg-indigo-50 print:bg-indigo-50 text-right font-bold border-b">
                                    {{ number_format($item->totalharga, 0, ',', '.') }}
                                </td>

                                @for($i = 1; $i <= 12; $i++) @php $val=$item->akb?->{"bulan$i"} ?? 0; @endphp
                                    <td
                                        class="px-2 py-2 border-r text-right border-b {{ $val > 0 ? 'text-blue-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}
                                    </td>
                                    @endfor

                                    @php
                                    $sisa = $item->totalharga - ($item->akb?->totalakb ?? 0);
                                    @endphp
                                    <td
                                        class="px-2 py-2 text-right bg-green-50 print:bg-green-50 font-bold border-b {{ $sisa != 0 ? 'text-red-600' : 'text-green-600' }}">
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

                <div class="mt-4 print:hidden">
                    {{ $data->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>