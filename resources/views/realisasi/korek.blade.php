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

            {{-- Navigasi Triwulan --}}
            @php
            $listNavigasi = [
            'tahun' => 'Tahunan',
            '1' => 'TW I',
            '2' => 'TW II',
            '3' => 'TW III',
            '4' => 'TW IV',
            ];
            @endphp

            <div class="flex justify-center mb-6 print:hidden">
                <div
                    class="inline-flex p-1.5 bg-gray-200/50 rounded-2xl border border-gray-200 shadow-inner backdrop-blur-sm">
                    @foreach ($listNavigasi as $key => $label)
                    <a href="{{ route('realisasi.korek', ['tw' => $key]) }}"
                        class="px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 {{ $tw == $key ? 'bg-white text-indigo-600 shadow-md transform scale-105' : 'text-gray-500 hover:text-gray-800' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
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
                    PERIODE: {{ $tw == 'tahun' ? 'JANUARI - DESEMBER' : 'TRIWULAN ' . $tw }} TAHUN {{ $anggaran->tahun
                    }}
                </p>
            </div>

            {{-- 2. DBOARD SUMMARY (BARU DITAMBAHKAN) --}}
            {{-- Menampilkan ringkasan Pagu, Realisasi, dan Sisa secara mencolok --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pagu ({{ $tw == 'tahun'
                        ? 'Thn' : 'TW '.$tw }})</div>
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
                            <td class="px-6 py-5 text-right">TOTAL ({{ $tw == 'tahun' ? 'TAHUNAN' : 'TW ' . $tw }})</td>
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