<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Realisasi Anggaran - ') . $tahun }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Informasi Sekolah --}}
            <div class="bg-white p-6 mb-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <h3 class="text-lg font-bold uppercase">{{ $setting->nama_sekolah ?? 'NAMA SEKOLAH' }}</h3>
                <p class="text-sm text-gray-600">Sumber Dana: <span class="uppercase font-semibold text-indigo-600">{{ $jenis }}</span> | Tahun {{ $tahun }}</p>
            </div>
{{-- Search Input --}}
<div class="bg-white p-4 mb-4 rounded-lg shadow-sm border border-gray-200">
    <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </span>
        <input type="text" id="komponenSearch"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Ketik nama komponen atau spesifikasi untuk mencari...">
    </div>
</div>
            {{-- Filter Form --}}
            <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('realisasi.komponen') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun</label>
                        <select name="tahun" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach([2024, 2025, 2026] as $th)
                                <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Anggaran</label>
                        <select name="jenis_anggaran" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="bos" {{ $jenis == 'bos' ? 'selected' : '' }}>BOS</option>
                            <option value="bop" {{ $jenis == 'bop' ? 'selected' : '' }}>BOP</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                            Filter Laporan
                        </button>
                        <a href="{{ route('realisasi.komponen') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Tabel Realisasi --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-[11px]">
                    <thead class="bg-gray-100 font-bold text-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left">Komponen / Rekening</th>
                            <th class="border border-gray-300 px-3 py-2 w-24 text-center">Volume</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Anggaran (A)</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Realisasi (B)</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Sisa (A-B)</th>
                            <th class="border border-gray-300 px-3 py-2 w-24 text-center text-blue-600">% Serapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataRkas as $idbl => $perAkun)
                            {{-- Header Kegiatan --}}
                            <tr class="bg-indigo-50">
                                <td colspan="5" class="border border-gray-300 px-2 py-1.5">
                                    <div class="flex items-center">
                                        <span class="bg-indigo-600 text-white px-2 py-0.5 rounded text-[9px] mr-2 uppercase">Kegiatan</span>
                                        <span class="font-bold text-indigo-900 text-xs">{{ $perAkun->first()->first()->kegiatan->namagiat ?? 'ID: '.$idbl }}</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach($perAkun as $kodeakun => $items)
                                {{-- Header Rekening --}}
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="border border-gray-300 px-6 py-1 italic text-gray-600 font-medium">
                                        {{ $items->first()->korek->uraian_singkat ?? $kodeakun }}
                                    </td>
                                </tr>

                                @foreach($items as $item)
                                    @php
                                        $anggaran = $item->total_anggaran ?? 0;
                                        $realisasi = $item->total_realisasi ?? 0;
                                        $sisa = $anggaran - $realisasi;
                          $volAnggaran = $item->akb->volume ?? 0;

        $volRealisasi = $item->volume_realisasi ?? 0;
        $sisaVol = $volAnggaran - $volRealisasi;
                                        $persen = $anggaran > 0 ? ($realisasi / $anggaran) * 100 : 0;
                                    @endphp
                                    <tr class="hover:bg-yellow-50/50 transition">
                                        <td class="border border-gray-300 px-3 py-2">
                                            <div class="font-semibold text-gray-800">{{ $item->namakomponen }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $item->spek }}</div>
                                        </td>
                                      <td class="border border-gray-300 px-3 py-2 text-center">
            <div class="font-bold {{ $volRealisasi > $volAnggaran ? 'text-red-600' : 'text-gray-700' }}">
                {{ number_format($volRealisasi, 0) }} / {{ number_format($volAnggaran, 0) }}
            </div>
            <div class="text-[10px] text-gray-400 uppercase">{{ $item->satuan }}</div>
        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-right font-medium text-gray-700">
                                            {{ number_format($anggaran, 0, ',', '.') }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-right font-medium text-indigo-600 bg-indigo-50/30">
                                            {{ number_format($realisasi, 0, ',', '.') }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-right font-bold {{ $sisa < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($sisa, 0, ',', '.') }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2">
                                            <div class="flex flex-col items-center">
                                                <span class="text-[10px] font-bold mb-1">{{ number_format($persen, 1) }}%</span>
                                                <div class="w-full bg-gray-200 rounded-full h-1">
                                                    <div class="h-1 rounded-full {{ $persen > 100 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ min($persen, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="border border-gray-300 px-4 py-8 text-center text-gray-500 italic">
                                    Tidak ada data realisasi untuk kriteria filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- Baris Total Keseluruhan --}}
                    @if($dataRkas->count() > 0)
                   <tfoot class="bg-gray-800 text-white font-bold border-t-2 border-gray-400">
    <tr>
        {{-- Merge Kolom Nama Komponen dan Kolom Volume --}}
        <td colspan="2" class="border border-gray-300 px-4 py-3 text-right uppercase font-bold">
            Total Keseluruhan
        </td>

        {{-- Kolom Anggaran --}}
        <td class="border border-gray-300 px-3 py-3 text-right">
            @php $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran'); @endphp
            {{ number_format($grandAnggaran, 0, ',', '.') }}
        </td>

        {{-- Kolom Realisasi --}}
        <td class="border border-gray-300 px-3 py-3 text-right bg-gray-700">
            @php $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi'); @endphp
            {{ number_format($grandRealisasi, 0, ',', '.') }}
        </td>

        {{-- Kolom Sisa --}}
        <td class="border border-gray-300 px-3 py-3 text-right text-yellow-400">
            {{ number_format($grandAnggaran - $grandRealisasi, 0, ',', '.') }}
        </td>

        {{-- Kolom Persen --}}
        <td class="border border-gray-300 px-3 py-3 text-center text-xs">
            @php
                $totalPersen = $grandAnggaran > 0 ? ($grandRealisasi / $grandAnggaran) * 100 : 0;
            @endphp
            {{ number_format($totalPersen, 1) }}%
        </td>
    </tr>
</tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('komponenSearch').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        let lastKegiatanRow = null;
        let lastRekeningRow = null;
        let hasVisibleComponentInRekening = false;
        let hasVisibleComponentInKegiatan = false;

        // Reset semua row dulu agar logika pengecekan bersih
        rows.forEach(row => row.style.display = 'none');

        rows.forEach((row, index) => {
            if (row.classList.contains('bg-indigo-50')) {
                // Ini row Kegiatan
                lastKegiatanRow = row;
                hasVisibleComponentInKegiatan = false;
            }
            else if (row.classList.contains('bg-gray-50')) {
                // Ini row Rekening
                lastRekeningRow = row;
                hasVisibleComponentInRekening = false;
            }
            else if (!row.classList.contains('italic')) {
                // Ini row Komponen
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleComponentInRekening = true;
                    hasVisibleComponentInKegiatan = true;

                    // Tampilkan parent-nya (Kegiatan & Rekening)
                    if (lastKegiatanRow) lastKegiatanRow.style.display = '';
                    if (lastRekeningRow) lastRekeningRow.style.display = '';
                }
            }
        });

        // Jika search kosong, tampilkan semua
        if (searchTerm === "") {
            rows.forEach(row => row.style.display = '');
        }
    });
</script>
</x-app-layout>
