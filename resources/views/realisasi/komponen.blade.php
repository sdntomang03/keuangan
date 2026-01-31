<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight uppercase">
                    Laporan Realisasi {{ $anggaran->singkatan }}
                </h2>
                <p class="text-sm text-gray-500 font-medium">
                    Tahun Anggaran {{ $anggaran->tahun }} — {{ $sekolah->nama_sekolah }}
                </p>
            </div>
            <div class="mt-2 md:mt-0 flex items-center gap-3">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                    {{ $anggaran->singkatan == 'BOS' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                    Mode Aktif: {{ $anggaran->nama_anggaran }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Definisi Array dipisah agar aman dari error formatting --}}
            @php
            $listTriwulan = [
            'tahun' => 'Tahunan',
            '1' => 'TW I',
            '2' => 'TW II',
            '3' => 'TW III',
            '4' => 'TW IV'
            ];
            @endphp

            {{-- Navigasi Triwulan --}}
            <div class="flex justify-center mb-6 print:hidden">
                <div class="inline-flex p-1 bg-gray-200 rounded-xl shadow-inner">
                    @foreach($listTriwulan as $key => $label)
                    <a href="{{ route('realisasi.komponen', ['tw' => $key]) }}"
                        class="px-5 py-2 rounded-lg text-xs font-bold uppercase transition-all {{ $tw == $key ? 'bg-white text-indigo-600 shadow' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Box Ringkasan Cepat --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @php
                $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran');
                $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi');
                $sisaTotal = $grandAnggaran - $grandRealisasi;
                $periodeText = $tw == 'tahun' ? 'Tahunan' : 'TW ' . $tw;
                @endphp
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Pagu {{ $periodeText }}</p>
                    <p class="text-xl font-black text-gray-800">Rp {{ number_format($grandAnggaran, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Realisasi {{ $periodeText }}</p>
                    <p class="text-xl font-black text-indigo-600">Rp {{ number_format($grandRealisasi, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Sisa Pagu {{ $periodeText }}</p>
                    <p class="text-xl font-black {{ $sisaTotal < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        Rp {{ number_format($sisaTotal, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Filter & Search --}}
            <div
                class="bg-white p-4 mb-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 print:hidden">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                        </svg>
                    </span>
                    <input type="text" id="komponenSearch"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Cari komponen belanja atau spesifikasi...">
                </div>
                <button onclick="window.print()"
                    class="bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-black transition shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            stroke-width="2" />
                    </svg>
                    CETAK LAPORAN
                </button>
            </div>

            {{-- Tabel Realisasi --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full border-collapse text-[11px]">
                    <thead class="bg-gray-800 text-white uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold">Komponen / Spesifikasi</th>
                            <th class="px-4 py-3 text-left font-bold">Harga</th>
                            <th class="px-4 py-3 text-center font-bold">Vol. {{ $tw == 'tahun' ? 'Real/Pagu' : 'TW' }}
                            </th>
                            <th class="px-4 py-3 text-right font-bold w-32">Pagu (A)</th>
                            <th class="px-4 py-3 text-right font-bold w-32">Realisasi (B)</th>
                            <th class="px-4 py-3 text-right font-bold w-32">Sisa</th>
                            <th class="px-4 py-3 text-center font-bold w-24">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataRkas as $idbl => $perAkun)
                        {{-- Header Kegiatan --}}
                        <tr class="bg-indigo-50/50 print:bg-gray-100 border-b border-indigo-100">
                            <td colspan="6" class="px-4 py-2">
                                <div class="flex items-center">
                                    <div class="w-2 h-4 bg-indigo-600 mr-3 rounded-sm"></div>
                                    <span class="font-black text-indigo-900 text-[11px] uppercase">
                                        {{ $perAkun->first()->first()->kegiatan->namagiat ?? 'Kegiatan Tidak
                                        Terdefinisi' }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                        @foreach($perAkun as $kodeakun => $items)
                        @foreach($items as $item)
                        @php
                        $anggaranVal = $item->total_anggaran ?? 0;
                        $realisasiVal = $item->total_realisasi ?? 0;
                        $sisaVal = $anggaranVal - $realisasiVal;
                        $volAnggaran = $item->total_volume_anggaran ?? 0;
                        $volRealisasi = $item->volume_realisasi ?? 0;
                        $persen = $anggaranVal > 0 ? ($realisasiVal / $anggaranVal) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">{{ $item->namakomponen }}</div>
                                <div class="text-[10px] text-gray-400 font-medium italic mt-0.5">{{ $item->spek }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-500">
                                {{ number_format($item->hargasatuan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="font-mono font-bold {{ $volRealisasi > $volAnggaran ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ number_format($volRealisasi, 0) }} / {{ number_format($volAnggaran, 0) }}
                                </span>
                                <div class="text-[9px] text-gray-400 uppercase font-bold">{{ $item->satuan }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-500">
                                {{ number_format($anggaranVal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-indigo-600">
                                {{ number_format($realisasiVal, 0, ',', '.') }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-mono font-bold {{ $sisaVal < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ number_format($sisaVal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 bg-gray-200 rounded-full h-1 mb-1 overflow-hidden">
                                        <div class="h-1 {{ $persen > 100 ? 'bg-red-500' : 'bg-indigo-500' }}"
                                            style="width: {{ min($persen, 100) }}%"></div>
                                    </div>
                                    <span
                                        class="text-[9px] font-black {{ $persen > 100 ? 'text-red-600' : 'text-gray-600' }}">{{
                                        number_format($persen, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="6" class="p-20 text-center">
                                <div class="text-gray-300 font-bold text-lg uppercase tracking-widest">Data Tidak
                                    Ditemukan</div>
                                <p class="text-gray-400 text-xs italic">Tidak ada anggaran atau realisasi untuk periode
                                    {{ $periodeText }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script Search --}}
    <script>
        document.getElementById('komponenSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = Array.from(document.querySelectorAll('tbody tr'));
            let currentKegiatanRow = null;
            let hasVisibleComponents = false;

            // Kita proses dari bawah ke atas atau simpan referensi header
            rows.forEach(row => {
                if (row.classList.contains('bg-indigo-50/50')) {
                    // Simpan header dan sembunyikan dulu
                    currentKegiatanRow = row;
                    row.style.display = 'none';
                    hasVisibleComponents = false;
                } else {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleComponents = true;
                        // Jika ada komponen yang cocok, tampilkan headernya
                        if (currentKegiatanRow) currentKegiatanRow.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    </script>
    <style>
        @media print {

            /* Sembunyikan navigasi bawaan Laravel (sidebar/topbar) */
            nav,
            aside,
            footer {
                display: none !important;
            }

            /* Hilangkan padding default pada wrapper layout */
            .py-6,
            .py-12 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            /* Pastikan container menggunakan lebar penuh */
            .max-w-7xl {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Paksa background muncul (warna tabel/tombol) */
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Pengaturan margin kertas (A4) */
            @page {
                size: A4 portrait;
                margin: 1cm;
            }

            /* Hilangkan bayangan/border yang tidak perlu di cetak */
            .shadow-xl,
            .shadow-sm {
                shadow: none !important;
                box-shadow: none !important;
            }

            .rounded-xl,
            .rounded-\[2rem\] {
                border-radius: 0 !important;
            }

            /* Jaga agar tanda tangan tidak terpotong di akhir halaman */
            .print\:block {
                display: block !important;
                page-break-inside: avoid;
            }
        }
    </style>
</x-app-layout>
