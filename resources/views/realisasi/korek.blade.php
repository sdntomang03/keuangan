<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="print:hidden">
                <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight uppercase">
                    {{ __('Realisasi Ringkasan Rekening') }}
                </h2>
                <p class="text-sm text-gray-500 font-medium italic">
                    Sumber Dana: {{ $anggaran->nama_anggaran }} — TA {{ $anggaran->tahun }}
                </p>
            </div>
            <div class="mt-2 md:mt-0 flex gap-2 print:hidden">
                <button onclick="window.print()"
                    class="bg-gray-800 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-black transition flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            stroke-width="2" />
                    </svg>
                    CETAK
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. HITUNG TOTAL DI AWAL UNTUK SUMMARY --}}
            @php
            // Hitung total keseluruhan dari data yang ada
            $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran');
            $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi');
            $grandSisa = $grandAnggaran - $grandRealisasi;
            $grandPersen = $grandAnggaran > 0 ? ($grandRealisasi / $grandAnggaran) * 100 : 0;
            @endphp

            <div class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm print:hidden sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Filter Periode</h3>
                    <p class="mt-1 text-xs text-gray-500">Pilih tahunan atau satu atau beberapa triwulan.</p>
                </div>
                <form method="GET" action="{{ route('realisasi.korek') }}" class="flex items-center gap-2">
                    <div x-data="{ open: false }" @click.away="open = false" class="relative z-20 w-full sm:w-96">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-left text-sm shadow-sm transition hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="truncate font-medium text-gray-700">{{ $periodeText }}</span>
                            <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak x-transition
                            class="absolute right-0 mt-2 w-full rounded-xl border border-gray-200 bg-white p-4 shadow-xl sm:w-[26rem]">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border-b border-gray-100 p-2.5 hover:bg-gray-50">
                                <input type="checkbox" name="periode[]" value="tahun"
                                    @change="if ($event.target.checked) $el.form.querySelectorAll('input[name=&quot;periode[]&quot;]:not([value=&quot;tahun&quot;])').forEach(input => input.checked = false);"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ in_array('tahun', $periode, true) ? 'checked' : '' }}>
                                <span class="text-sm font-semibold text-gray-800">Tahunan (Semua)</span>
                            </label>
                            <div class="space-y-1 pt-2">
                                <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Triwulan</p>
                                @php
                                    $triwulans = ['tw1' => 'Triwulan I (Jan-Mar)', 'tw2' => 'Triwulan II (Apr-Jun)', 'tw3' => 'Triwulan III (Jul-Sep)', 'tw4' => 'Triwulan IV (Okt-Des)'];
                                @endphp
                                @foreach ($triwulans as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg p-2 hover:bg-gray-50">
                                    <input type="checkbox" name="periode[]" value="{{ $value }}"
                                        @change="if ($event.target.checked) $el.form.querySelector('input[name=&quot;periode[]&quot;][value=&quot;tahun&quot;]').checked = false;"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ in_array($value, $periode, true) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            <button type="submit"
                                class="mt-3 w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-indigo-700">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Header Kop Surat --}}
            <div
                class="bg-white p-6 mb-6 rounded-xl shadow-sm border border-gray-200 text-center relative overflow-hidden">
                <div
                    class="absolute top-0 left-0 w-full h-1 {{ $anggaran->singkatan == 'BOS' ? 'bg-indigo-600' : 'bg-emerald-600' }}">
                </div>
                <h3 class="text-xl font-black uppercase text-gray-800 tracking-wider">{{ $sekolah->nama_sekolah ?? 'NAMA
                    SEKOLAH' }}</h3>
                <p class="text-sm text-gray-600 mt-1 uppercase">
                    LAPORAN REALISASI PENGGUNAAN DANA <span class="font-bold text-indigo-600">{{ $anggaran->singkatan
                        }}</span>
                </p>
                <p class="text-xs font-bold text-gray-500 mt-1 uppercase">
                    PERIODE: {{ strtoupper($periodeText) }} TAHUN {{ $anggaran->tahun }}
                </p>
            </div>

            {{-- 2. DBOARD SUMMARY (BARU DITAMBAHKAN) --}}
            {{-- Menampilkan ringkasan Pagu, Realisasi, dan Sisa secara mencolok --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pagu ({{ $periodeText }})</div>
                    <div class="mt-1 text-lg font-black text-gray-800 font-mono">
                        {{ number_format($grandAnggaran, 0, ',', '.') }}
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Realisasi</div>
                    <div class="mt-1 text-lg font-black text-yellow-600 font-mono">
                        {{ number_format($grandRealisasi, 0, ',', '.') }}
                    </div>
                </div>

                <div
                    class="bg-white p-4 rounded-xl shadow-sm border-l-4 {{ $grandSisa < 0 ? 'border-red-500' : 'border-emerald-500' }}">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sisa Pagu</div>
                    <div
                        class="mt-1 text-lg font-black {{ $grandSisa < 0 ? 'text-red-600' : 'text-emerald-600' }} font-mono">
                        {{ number_format($grandSisa, 0, ',', '.') }}
                    </div>
                </div>

                <div
                    class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-indigo-500 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Serapan</div>
                        <div class="mt-1 text-lg font-black text-indigo-700">
                            {{ number_format($grandPersen, 1) }}%
                        </div>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Tabel Realisasi --}}
            <div class="bg-white shadow-xl sm:rounded-[2rem] overflow-hidden border border-gray-200">
                <table class="w-full border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-gray-800 text-white uppercase tracking-widest">
                            <th class="px-6 py-4 text-left font-bold">Uraian Kegiatan / Kode Rekening</th>
                            {{-- Judul kolom diperjelas --}}
                            <th class="px-4 py-4 text-right font-bold w-40">Pagu Anggaran</th>
                            <th class="px-4 py-4 text-right font-bold w-40">Realisasi</th>
                            <th class="px-4 py-4 text-right font-bold w-40">Sisa Pagu</th>
                            <th class="px-4 py-4 text-center font-bold w-20">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($dataRkas as $idbl => $perAkun)
                        @php
                        $totalAnggaranGiat = $perAkun->flatten()->sum('total_anggaran');
                        $totalRealisasiGiat = $perAkun->flatten()->sum('total_realisasi');
                        $sisaGiat = $totalAnggaranGiat - $totalRealisasiGiat;
                        $persenGiat = $totalAnggaranGiat > 0 ? ($totalRealisasiGiat / $totalAnggaranGiat) * 100 : 0;
                        @endphp
                        <tr class="bg-indigo-50/50 print:bg-gray-100">
                            <td class="px-6 py-3 font-black text-indigo-900 uppercase">
                                {{ $perAkun->first()->first()->kegiatan->namagiat ?? 'Kegiatan ID: ' . $idbl }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-indigo-900 font-mono">
                                {{ number_format($totalAnggaranGiat, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-indigo-900 font-mono">
                                {{ number_format($totalRealisasiGiat, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-indigo-900 font-mono">
                                {{ number_format($sisaGiat, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-black text-indigo-900">
                                {{ number_format($persenGiat, 1) }}%</td>
                        </tr>

                        @foreach ($perAkun as $kodeakun => $items)
                        @php
                        $totalAnggaranRek = $items->sum('total_anggaran');
                        $totalRealisasiRek = $items->sum('total_realisasi');
                        $sisaRek = $totalAnggaranRek - $totalRealisasiRek;
                        $persenRek = $totalAnggaranRek > 0 ? ($totalRealisasiRek / $totalAnggaranRek) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-12 py-3 font-medium text-gray-700">
                                <div class="flex items-center">
                                    {{ $items->first()->korek->ket ?? 'Rekening tidak ditemukan' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 font-mono">
                                {{ number_format($totalAnggaranRek, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 font-mono">
                                {{ number_format($totalRealisasiRek, 0, ',', '.') }}</td>
                            <td
                                class="px-4 py-3 text-right font-bold font-mono {{ $sisaRek < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ number_format($sisaRek, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div
                                    class="text-[10px] font-black {{ $persenRek > 100 ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ number_format($persenRek, 0) }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center text-gray-400 italic">Data tidak ditemukan untuk
                                periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- Footer Total --}}
                    @if ($dataRkas->count() > 0)
                    <tfoot
                        class="bg-gray-900 text-white font-bold uppercase tracking-widest border-t-4 border-indigo-500">
                        <tr>
                            <td class="px-6 py-5 text-right">TOTAL ({{ strtoupper($periodeText) }})</td>
                            <td class="px-4 py-5 text-right font-mono text-base">
                                {{ number_format($grandAnggaran, 0, ',', '.') }}</td>
                            <td class="px-4 py-5 text-right font-mono text-base text-yellow-400">
                                {{ number_format($grandRealisasi, 0, ',', '.') }}</td>
                            <td class="px-4 py-5 text-right font-mono text-base">
                                {{ number_format($grandSisa, 0, ',', '.') }}</td>
                            <td class="px-4 py-5 text-center text-xs">{{ number_format($grandPersen, 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Tabel Rekapitulasi Per Kode Rekening --}}
            <div class="mt-8 mb-6 print:mt-8 print:break-inside-avoid">
                <h3
                    class="text-lg font-black text-gray-800 uppercase tracking-widest mb-4 border-l-4 border-indigo-500 pl-3">
                    Rekapitulasi Per Kode Rekening
                </h3>
                <div class="bg-white shadow-xl sm:rounded-[2rem] overflow-hidden border border-gray-200">
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-gray-700 text-white uppercase tracking-widest">
                                <th class="px-6 py-4 text-left font-bold">Uraian Rekening</th>
                                <th class="px-4 py-4 text-right font-bold w-40">Total Pagu</th>
                                <th class="px-4 py-4 text-right font-bold w-40">Total Realisasi</th>
                                <th class="px-4 py-4 text-right font-bold w-40">Sisa Pagu</th>
                                <th class="px-4 py-4 text-center font-bold w-20">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php
                            // Mengambil semua item, lalu mengelompokkan berdasarkan nama rekening (korek->ket)
                            $rekapRekening = $dataRkas->flatten(2)->groupBy(function($item) {
                            return $item->korek->ket ?? 'Rekening tidak ditemukan';
                            })->sortKeys(); // Mengurutkan alfabetis berdasarkan nama rekening
                            @endphp

                            @forelse($rekapRekening as $namaRekening => $itemsRek)
                            @php
                            $rekPagu = $itemsRek->sum('total_anggaran');
                            $rekRealisasi = $itemsRek->sum('total_realisasi');
                            $rekSisa = $rekPagu - $rekRealisasi;
                            $rekPersen = $rekPagu > 0 ? ($rekRealisasi / $rekPagu) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $namaRekening }}</td>
                                <td class="px-4 py-3 text-right text-gray-500 font-mono">
                                    {{ number_format($rekPagu, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 font-mono">
                                    {{ number_format($rekRealisasi, 0, ',', '.') }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-bold font-mono {{ $rekSisa < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($rekSisa, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div
                                        class="text-[10px] font-black {{ $rekPersen > 100 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ number_format($rekPersen, 0) }}%
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-gray-400 italic">Data rekap tidak
                                    tersedia.</td>
                            </tr>
                            @endforelse
                        </tbody>

                        {{-- Footer Total Rekap --}}
                        @if ($rekapRekening->count() > 0)
                        <tfoot
                            class="bg-gray-800 text-white font-bold uppercase tracking-widest border-t-4 border-indigo-500">
                            <tr>
                                <td class="px-6 py-4 text-right">TOTAL KESELURUHAN</td>
                                <td class="px-4 py-4 text-right font-mono text-sm">
                                    {{ number_format($grandAnggaran, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-sm text-yellow-400">
                                    {{ number_format($grandRealisasi, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-sm">
                                    {{ number_format($grandSisa, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-center text-xs">
                                    {{ number_format($grandPersen, 1) }}%
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Tanda Tangan --}}
            <div class="hidden print:block mt-16">
                <div class="flex justify-around text-xs text-center font-bold">
                    <div class="w-1/3">
                        Mengetahui,<br>Kepala Sekolah<br><br><br><br><br>
                        <span class="border-b border-black px-4 ">{{ $sekolah->nama_kepala_sekolah ??
                            '................................' }}</span><br>
                        NIP. {{ $sekolah->nip_kepala_sekolah ?? '................................' }}
                    </div>
                    <div class="w-1/3">
                        {{ now()->translatedFormat('d F Y') }}<br>Bendahara,<br><br><br><br><br>
                        <span class="border-b border-black px-4">{{ $sekolah->nama_bendahara ??
                            '................................' }}</span><br>
                        NIP. {{ $sekolah->nip_bendahara ?? '................................' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media print {

            /* 1. TAMBAHKAN KODE INI UNTUK MENCEGAH TERPOTONG */
            /* 1. Sembunyikan header, nav, aside, dan footer sepenuhnya */
            nav,
            aside,
            footer,
            header {
                display: none !important;
            }

            /* 2. Matikan efek melayang (sticky/fixed) yang menimpa konten */
            * {
                position: static !important;
            }

            /* 3. Mencegah tabel terpotong (dari solusi sebelumnya) */
            html,
            body {
                height: auto !important;
                overflow: visible !important;
            }

            .overflow-hidden,
            .overflow-x-auto,
            .overflow-y-auto {
                overflow: visible !important;
                height: auto !important;
            }

            /* ============================================== */

            /* Kode Anda sebelumnya tetap di bawah ini */
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

            tfoot {
                display: table-row-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</x-app-layout>