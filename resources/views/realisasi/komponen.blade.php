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

            {{-- Box Ringkasan Cepat --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @php
                $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran');
                $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi');
                $sisaTotal = $grandAnggaran - $grandRealisasi;
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

                {{-- Dropdown Filter Periode --}}
                <form method="GET" action="{{ route('realisasi.komponen') }}"
                    class="w-full md:w-auto flex items-center gap-2">
                    <label for="periode" class="text-sm font-bold text-gray-600 whitespace-nowrap">Tampilkan:</label>
                    <select name="periode" id="periode" onchange="this.form.submit()"
                        class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer font-medium bg-gray-50">
                        <option value="tahun" {{ $periode=='tahun' ? 'selected' : '' }}>Tahunan (Semua)</option>

                        <optgroup label="Per Triwulan">
                            <option value="tw1" {{ $periode=='tw1' ? 'selected' : '' }}>Triwulan I (Jan-Mar)</option>
                            <option value="tw2" {{ $periode=='tw2' ? 'selected' : '' }}>Triwulan II (Apr-Jun)</option>
                            <option value="tw3" {{ $periode=='tw3' ? 'selected' : '' }}>Triwulan III (Jul-Sep)</option>
                            <option value="tw4" {{ $periode=='tw4' ? 'selected' : '' }}>Triwulan IV (Okt-Des)</option>
                        </optgroup>

                        <optgroup label="Per Bulan">
                            @php $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                            'Agustus', 'September', 'Oktober', 'November', 'Desember']; @endphp
                            @foreach($namaBulan as $index => $nama)
                            <option value="b{{ $index + 1 }}" {{ $periode=='b' .($index + 1) ? 'selected' : '' }}>{{
                                $nama }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </form>

                {{-- Tombol Cetak --}}
                <button onclick="window.print()"
                    class="bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-black transition shadow-md w-full md:w-auto justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            stroke-width="2" />
                    </svg>
                    CETAK
                </button>
            </div>

            {{-- Tabel Realisasi --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full border-collapse text-[11px]">
                    <thead class="bg-gray-800 text-white uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold">Komponen / Spesifikasi</th>
                            <th class="px-4 py-3 text-left font-bold">Harga</th>
                            <th class="px-4 py-3 text-center font-bold">Vol. {{ $periodeText }}</th>
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
                            <td colspan="7" class="px-4 py-2">
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
                                <div class="text-[10px] text-gray-400 font-medium italic mt-0.5">{{ $item->koefisien }}
                                </div>
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
                                        class="text-[9px] font-black {{ $persen > 100 ? 'text-red-600' : 'text-gray-600' }}">
                                        {{ number_format($persen, 0) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center">
                                <div class="text-gray-300 font-bold text-lg uppercase tracking-widest">Data Tidak
                                    Ditemukan</div>
                                <p class="text-gray-400 text-xs italic">Tidak ada anggaran atau realisasi untuk periode
                                    ini.</p>
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

            rows.forEach(row => {
                if (row.classList.contains('bg-indigo-50/50')) {
                    currentKegiatanRow = row;
                    row.style.display = 'none';
                } else {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
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

            nav,
            aside,
            footer {
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
                size: A4 portrait;
                margin: 1cm;
            }

            .shadow-xl,
            .shadow-sm {
                shadow: none !important;
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
        }
    </style>
</x-app-layout>