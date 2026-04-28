<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between print:hidden">
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
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $anggaran->singkatan == 'BOS' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                    Mode Aktif: {{ $anggaran->nama_anggaran }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- KOP LAPORAN (Khusus Print) --}}
            <div class="hidden print:block text-center mb-6 border-b pb-4">
                <h2 class="text-xl font-bold uppercase tracking-wider">
                    Laporan Realisasi {{ $anggaran->nama_anggaran }}
                </h2>
                <p class="text-sm text-gray-700 font-bold mt-1">
                    Tahun Anggaran {{ $anggaran->tahun }} — {{ $sekolah->nama_sekolah }}
                </p>
            </div>

            {{-- Box Ringkasan Cepat --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 print:hidden">
                @php
                $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran');
                $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi');
                $sisaTotal = $grandAnggaran - $grandRealisasi;
                @endphp

                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Pagu {{ Str::limit($periodeText, 25) }}
                    </p>
                    <p class="text-xl font-black text-gray-800">Rp {{ number_format($grandAnggaran, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Realisasi {{ Str::limit($periodeText, 25)
                        }}</p>
                    <p class="text-xl font-black text-indigo-600">Rp {{ number_format($grandRealisasi, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-[10px] text-gray-400 uppercase font-black">Sisa Pagu {{ Str::limit($periodeText, 25)
                        }}</p>
                    <p class="text-xl font-black {{ $sisaTotal < 0 ? 'text-red-600' : 'text-emerald-600' }}">Rp {{
                        number_format($sisaTotal, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter Area (Search + Dropdown Periode + Cetak) --}}
            <div
                class="bg-white p-4 mb-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row items-center gap-4 print:hidden">
                {{-- Pencarian --}}
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                        </svg>
                    </span>
                    <input type="text" id="komponenSearch"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Cari komponen atau spesifikasi...">
                </div>

                {{-- Dropdown Filter Periode Multi Select --}}
                <form method="GET" action="{{ route('realisasi.komponen') }}"
                    class="w-full md:w-auto flex items-center gap-2">
                    <label class="text-sm font-bold text-gray-600 whitespace-nowrap">Tampilkan:</label>

                    <div x-data="{ open: false }" @click.away="open = false" class="relative w-full md:w-64 z-50">
                        <button type="button" @click="open = !open"
                            class="flex justify-between items-center w-full bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="truncate block w-full text-gray-700 font-medium">{{ Str::limit($periodeText,
                                25) }}</span>
                            <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity style="display: none;"
                            class="absolute mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-xl p-3 max-h-[80vh] overflow-y-auto right-0 md:left-0">
                            <div class="space-y-3">
                                <label
                                    class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer border-b border-gray-100">
                                    <input type="checkbox" name="periode[]" value="tahun"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" {{
                                        in_array('tahun', $periode) ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-gray-800">Tahunan (Semua)</span>
                                </label>

                                <div>
                                    <span
                                        class="block text-[10px] font-black uppercase text-indigo-500 mb-2 mt-2 tracking-widest">Per
                                        Triwulan</span>
                                    @php
                                    $triwulans = ['tw1' => 'Triwulan I (Jan-Mar)', 'tw2' => 'Triwulan II (Apr-Jun)',
                                    'tw3' => 'Triwulan III (Jul-Sep)', 'tw4' => 'Triwulan IV (Okt-Des)'];
                                    @endphp
                                    @foreach($triwulans as $val => $label)
                                    <label
                                        class="flex items-center space-x-3 p-1.5 hover:bg-gray-50 rounded cursor-pointer ml-2">
                                        <input type="checkbox" name="periode[]" value="{{ $val }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{
                                            in_array($val, $periode) ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>

                                <div>
                                    <span
                                        class="block text-[10px] font-black uppercase text-emerald-500 mb-2 mt-3 pt-2 border-t border-gray-100 tracking-widest">Per
                                        Bulan</span>
                                    <div class="grid grid-cols-2 gap-1 ml-2">
                                        @php $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; @endphp
                                        @foreach($namaBulan as $index => $nama)
                                        @php $valBulan = 'b' . ($index + 1); @endphp
                                        <label
                                            class="flex items-center space-x-2 p-1 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox" name="periode[]" value="{{ $valBulan }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{
                                                in_array($valBulan, $periode) ? 'checked' : '' }}>
                                            <span class="text-[13px] text-gray-600">{{ $nama }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="sticky bottom-0 bg-white border-t border-gray-100 pt-3 pb-1 mt-3 text-right">
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider w-full hover:bg-indigo-700 shadow-sm flex items-center justify-center">
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Tombol Cetak & Excel --}}
                <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
                    <a href="{{ route('realisasi.komponen.export', ['periode' => $periode]) }}"
                        class="bg-emerald-600 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-emerald-700 transition shadow-md w-full md:w-auto justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        EXCEL
                    </a>
                    <button onclick="window.print()"
                        class="bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-black transition shadow-md w-full md:w-auto justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        CETAK
                    </button>
                </div>
            </div>

            {{-- Tabel Realisasi --}}
            <div
                class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden print:shadow-none print:border-gray-400">
                <table class="w-full border-collapse text-[11px] print:text-[10px]">
                    <thead class="bg-gray-800 text-white uppercase tracking-wider print:bg-gray-200 print:text-black">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold border border-gray-300">Komponen & Spesifikasi</th>
                            <th class="px-4 py-3 text-right font-bold border border-gray-300">Harga Satuan</th>
                            <th class="px-4 py-3 text-center font-bold border border-gray-300">Vol. {{
                                Str::limit($periodeText, 10) }}</th>
                            <th class="px-4 py-3 text-right font-bold w-32 border border-gray-300">Pagu (A)</th>
                            <th class="px-4 py-3 text-right font-bold w-32 border border-gray-300">Realisasi (B)</th>
                            <th class="px-4 py-3 text-right font-bold w-32 border border-gray-300">Sisa</th>
                            <th class="px-4 py-3 text-center font-bold w-24 border border-gray-300">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($dataRkas as $idbl => $perAkun)
                        @php
                        $namaKegiatan = $perAkun->first()->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi';
                        @endphp

                        {{-- 1. BARIS HEADER KEGIATAN --}}
                        <tr class="bg-indigo-50/50 print:bg-gray-100 border border-gray-300 header-kegiatan">
                            <td colspan="7" class="px-4 py-2 border border-gray-300">
                                <div class="flex items-center">
                                    <div class="w-2 h-4 bg-indigo-600 mr-3 rounded-sm print:bg-gray-800"></div>
                                    <span
                                        class="font-black text-indigo-900 print:text-black text-[11px] uppercase tracking-wide">
                                        KEGIATAN: {{ $namaKegiatan }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                        {{-- LOOPING PER KODE REKENING --}}
                        @foreach($perAkun as $kodeakun => $items)
                        @php
                        $namaAkun = $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi';
                        $kodeAkunText = $items->first()->korek->kode ?? '-';
                        @endphp

                        {{-- 2. BARIS HEADER KODE REKENING --}}
                        <tr class="bg-slate-50 print:bg-gray-50 border border-gray-300">
                            <td colspan="7" class="px-4 py-2 pl-10 border border-gray-300">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 print:text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                        </path>
                                    </svg>
                                    <span
                                        class="font-bold text-slate-700 print:text-black text-[10px] uppercase tracking-wide">
                                        REKENING: {{ $kodeAkunText }} - {{ $namaAkun }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                        {{-- 3. BARIS ITEM KOMPONEN --}}
                        @foreach($items as $item)
                        @php
                        $anggaranVal = $item->total_anggaran ?? 0;
                        $realisasiVal = $item->total_realisasi ?? 0;
                        $sisaVal = $anggaranVal - $realisasiVal;
                        $volAnggaran = $item->total_volume_anggaran ?? 0;
                        $volRealisasi = $item->volume_realisasi ?? 0;
                        $persen = $anggaranVal > 0 ? ($realisasiVal / $anggaranVal) * 100 : 0;
                        @endphp

                        <tr
                            class="hover:bg-gray-50 transition border border-gray-300 row-item print:break-inside-avoid">
                            <td class="px-4 py-3 border border-gray-300 pl-14">
                                <div class="font-bold text-gray-800 searchable-text">{{ $item->namakomponen }}</div>
                                <div class="text-[10px] text-gray-500 font-medium italic mt-0.5 searchable-text">
                                    Spesifikasi: {{ $item->spek ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-right font-mono text-gray-600 border border-gray-300">
                                {{ number_format($item->hargasatuan, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-center border border-gray-300">
                                <span
                                    class="font-mono font-bold {{ $volRealisasi > $volAnggaran ? 'text-red-600 print:text-red-800' : 'text-gray-700' }}">
                                    {{ number_format($volRealisasi, 0) }} / {{ number_format($volAnggaran, 0) }}
                                </span>
                                <div class="text-[9px] text-gray-400 uppercase font-bold">{{ $item->satuan }}</div>
                            </td>

                            <td class="px-4 py-3 text-right font-mono text-gray-600 border border-gray-300">
                                {{ number_format($anggaranVal, 0, ',', '.') }}
                            </td>

                            <td
                                class="px-4 py-3 text-right font-mono font-bold text-indigo-600 print:text-black border border-gray-300">
                                {{ number_format($realisasiVal, 0, ',', '.') }}
                            </td>

                            <td
                                class="px-4 py-3 text-right font-mono font-bold {{ $sisaVal < 0 ? 'text-red-600 print:text-red-800' : 'text-emerald-600 print:text-black' }} border border-gray-300">
                                {{ number_format($sisaVal, 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3 text-center border border-gray-300">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 bg-gray-200 rounded-full h-1 mb-1 overflow-hidden print:hidden">
                                        <div class="h-1 {{ $persen > 100 ? 'bg-red-500' : 'bg-indigo-500' }}"
                                            style="width: {{ min($persen, 100) }}%"></div>
                                    </div>
                                    <span
                                        class="text-[9px] font-black {{ $persen > 100 ? 'text-red-600 print:text-red-800' : 'text-gray-600 print:text-black' }}">
                                        {{ number_format($persen, 0) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center border border-gray-300">
                                <div class="text-gray-400 font-bold text-lg uppercase tracking-widest">Data Tidak
                                    Ditemukan</div>
                                <p class="text-gray-500 text-xs italic mt-1">Tidak ada anggaran atau realisasi untuk
                                    periode ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Logika Pencarian Tabel (Di-update agar kompatibel dengan hierarki baru)
        document.getElementById('komponenSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = Array.from(document.querySelectorAll('tbody tr'));

            let currentKegiatan = null;
            let currentRekening = null;

            let kegiatanHasVisibleItem = false;
            let rekeningHasVisibleItem = false;

            // Reset tampilan jika search kosong
            if (searchTerm === '') {
                rows.forEach(row => row.style.display = '');
                return;
            }

            // Looping dari bawah ke atas agar parent tahu apakah anaknya ada yang visible
            for (let i = rows.length - 1; i >= 0; i--) {
                const row = rows[i];

                if (row.classList.contains('header-kegiatan')) {
                    row.style.display = kegiatanHasVisibleItem ? '' : 'none';
                    kegiatanHasVisibleItem = false; // Reset untuk kegiatan berikutnya (yang di atasnya)
                }
                else if (row.classList.contains('bg-slate-50')) { // Header Rekening
                    row.style.display = rekeningHasVisibleItem ? '' : 'none';
                    if (rekeningHasVisibleItem) kegiatanHasVisibleItem = true;
                    rekeningHasVisibleItem = false; // Reset untuk rekening berikutnya
                }
                else if (row.classList.contains('row-item')) {
                    // Cari teks spesifik di baris komponen
                    const searchTexts = Array.from(row.querySelectorAll('.searchable-text')).map(el => el.textContent.toLowerCase());
                    const match = searchTexts.some(text => text.includes(searchTerm));

                    if (match) {
                        row.style.display = '';
                        rekeningHasVisibleItem = true;
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        });
    </script>

    <style>
        @media print {

            nav,
            aside,
            footer,
            .print\:hidden {
                display: none !important;
            }

            .py-6,
            .py-12 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            .max-w-7xl {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 landscape;
                /* Landscape agar tabel lebar muat */
                margin: 1cm;
            }

            .shadow-xl,
            .shadow-sm {
                box-shadow: none !important;
            }

            .rounded-xl,
            .rounded-\[2rem\] {
                border-radius: 0 !important;
            }

            .print\:block {
                display: block !important;
                page-break-inside: avoid;
            }

            /* Memaksa batas tabel jelas saat di print */
            table,
            th,
            td {
                border: 1px solid #d1d5db !important;
            }

            th {
                background-color: #e5e7eb !important;
                color: #000 !important;
            }
        }
    </style>
</x-app-layout>