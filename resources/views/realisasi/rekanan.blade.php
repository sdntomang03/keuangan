<x-app-layout>
    <x-slot name="header">
        {{-- Judul di Website (Akan hilang saat print) --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight print:hidden">
            {{ __('Laporan Realisasi Per Rekanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- TOMBOL CETAK (Hilang saat print) --}}
            <div class="mb-4 flex justify-end print:hidden">
                <button onclick="window.print()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Laporan
                </button>
            </div>

            {{-- AREA YANG AKAN DICETAK (Beri ID printable-area) --}}
            <div id="printable-area" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 relative">

                {{-- KOP SURAT (Muncul HANYA saat print) --}}
                <div class="hidden print:block">
                    <x-kop :sekolah="$sekolah" />
                </div>

                {{-- JUDUL LAPORAN --}}
                <div class="text-center mb-8 mt-6 print:mt-0 px-4">
                    {{-- Judul Utama --}}
                    <h1 class="text-xl font-bold uppercase text-gray-900 leading-tight">
                        LAPORAN REALISASI PER REKANAN
                    </h1>

                    {{-- Sub Judul --}}
                    <h2 class="text-lg font-semibold uppercase text-gray-800 mt-1">
                        {{ $anggaran->nama_anggaran }}
                    </h2>

                    {{-- Tahun --}}
                    <p class="text-md mt-1 text-gray-600">
                        Tahun Anggaran {{ $anggaran->tahun }}
                    </p>

                </div>

                {{-- TABEL DATA --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 text-sm">
                        <thead class="bg-gray-100 print:bg-gray-200">
                            <tr>
                                <th rowspan="2" class="border border-gray-300 px-4 py-2 text-center align-middle w-10">
                                    No</th>
                                <th rowspan="2" class="border border-gray-300 px-4 py-2 text-left align-middle">Nama
                                    Rekanan / Toko</th>
                                <th colspan="4" class="border border-gray-300 px-4 py-2 text-center">Realisasi Per
                                    Triwulan</th>
                                <th rowspan="2" class="border border-gray-300 px-4 py-2 text-right align-middle">Total
                                    Setahun</th>

                                {{-- KOLOM AKSI (HILANG SAAT PRINT) --}}
                                <th rowspan="2"
                                    class="border border-gray-300 px-2 py-2 text-center align-middle w-16 print:hidden">
                                    Aksi</th>
                            </tr>
                            <tr>
                                <th
                                    class="border border-gray-300 px-2 py-1 text-center w-28 bg-blue-50 print:bg-transparent">
                                    TW I</th>
                                <th
                                    class="border border-gray-300 px-2 py-1 text-center w-28 bg-green-50 print:bg-transparent">
                                    TW II</th>
                                <th
                                    class="border border-gray-300 px-2 py-1 text-center w-28 bg-yellow-50 print:bg-transparent">
                                    TW III</th>
                                <th
                                    class="border border-gray-300 px-2 py-1 text-center w-28 bg-red-50 print:bg-transparent">
                                    TW IV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataRekap as $index => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-4 py-2 font-semibold">
                                    {{ $row->rekanan->nama_rekanan ?? 'Tanpa Nama' }}
                                    <div class="text-[10px] text-gray-500 font-normal print:text-black">
                                        {{ $row->rekanan->npwp ?? '-' }}
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    {{ $row->tw1 > 0 ? number_format($row->tw1, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    {{ $row->tw2 > 0 ? number_format($row->tw2, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    {{ $row->tw3 > 0 ? number_format($row->tw3, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    {{ $row->tw4 > 0 ? number_format($row->tw4, 0, ',', '.') : '-' }}
                                </td>
                                <td
                                    class="border border-gray-300 px-4 py-2 text-right font-bold bg-gray-50 print:bg-transparent">
                                    {{ number_format($row->total_setahun, 0, ',', '.') }}
                                </td>

                                {{-- TOMBOL DOWNLOAD (HILANG SAAT PRINT) --}}
                                <td class="border border-gray-300 px-2 py-2 text-center print:hidden">
                                    <a href="{{ route('rekap.rekanan.export_detail', $row->rekanan_id) }}"
                                        target="_blank" title="Download Rincian Excel"
                                        class="inline-flex items-center justify-center bg-green-100 hover:bg-green-200 text-green-700 border border-green-300 rounded p-1.5 transition shadow-sm">
                                        {{-- Icon Download / Excel --}}
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                    Belum ada data realisasi belanja.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-200 font-bold print:bg-gray-100">
                            <tr>
                                <td colspan="2" class="border border-gray-300 px-4 py-3 text-center">TOTAL KESELURUHAN
                                </td>
                                <td class="border border-gray-300 px-2 py-3 text-right">{{
                                    number_format($grandTotal['tw1'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-3 text-right">{{
                                    number_format($grandTotal['tw2'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-3 text-right">{{
                                    number_format($grandTotal['tw3'], 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-3 text-right">{{
                                    number_format($grandTotal['tw4'], 0, ',', '.') }}</td>
                                <td
                                    class="border border-gray-300 px-4 py-3 text-right text-indigo-800 print:text-black">
                                    {{ number_format($grandTotal['total'], 0, ',', '.') }}</td>

                                {{-- Sel Kosong untuk Footer Kolom Aksi (Hilang saat print) --}}
                                <td class="border border-gray-300 print:hidden bg-gray-200"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- TANDA TANGAN (Muncul HANYA saat print) --}}
                <div class="hidden print:flex justify-between mt-16 px-10 text-center break-inside-avoid">
                    <div>
                        <p>Mengetahui,</p>
                        <p>Kepala Sekolah</p>
                        <br><br><br><br>
                        <p class="font-bold underline">...................................</p>
                        <p>NIP. ...........................</p>
                    </div>
                    <div>
                        <p>{{ now()->format('d F Y') }}</p>
                        <p>Bendahara Sekolah</p>
                        <br><br><br><br>
                        <p class="font-bold underline">...................................</p>
                        <p>NIP. ...........................</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- CSS STYLE KHUSUS PRINT --}}
    <style>
        @media print {

            /* 1. Sembunyikan SEMUA elemen body */
            body * {
                visibility: hidden;
            }

            /* 2. Tampilkan HANYA area dengan ID 'printable-area' */
            #printable-area,
            #printable-area * {
                visibility: visible;
            }

            /* 3. Atur Posisi agar pas di kertas */
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                /* Padding kertas */
                background-color: white !important;
                box-shadow: none !important;
                border: none !important;
            }

            /* 4. Pastikan Background tercetak (untuk header tabel) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* 5. Hilangkan URL/Link di footer cetakan browser */
            @page {
                margin: 0.5cm;
                size: auto;
            }
        }
    </style>
</x-app-layout>