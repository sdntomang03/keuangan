<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Realisasi Anggaran (Ringkasan) - ') . $tahun }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Informasi Sekolah --}}
            <div class="bg-white p-6 mb-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <h3 class="text-lg font-bold uppercase">{{ $setting->nama_sekolah ?? 'NAMA SEKOLAH' }}</h3>
                <p class="text-sm text-gray-600">Sumber Dana: <span class="uppercase font-semibold text-indigo-600">{{ $jenis }}</span> | Tahun {{ $tahun }}</p>
            </div>

            {{-- Filter Form --}}
            <div class="bg-white p-4 mb-6 rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('realisasi.korek') }}" method="GET" class="flex flex-wrap items-end gap-4">
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
                        <a href="{{ route('realisasi.korek') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Tabel Realisasi --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-[11px]">
                    <thead class="bg-gray-100 font-bold text-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left">Uraian Kegiatan / Kode Rekening</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Anggaran</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Realisasi</th>
                            <th class="border border-gray-300 px-3 py-2 w-32 text-right">Sisa</th>
                            <th class="border border-gray-300 px-3 py-2 w-24 text-center text-blue-600">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataRkas as $idbl => $perAkun)
                            {{-- Baris Kegiatan (Subtotal Kegiatan) --}}
                            @php
                                $totalAnggaranGiat = $perAkun->flatten()->sum('total_anggaran');
                                $totalRealisasiGiat = $perAkun->flatten()->sum('total_realisasi');
                                $sisaGiat = $totalAnggaranGiat - $totalRealisasiGiat;
                                $persenGiat = $totalAnggaranGiat > 0 ? ($totalRealisasiGiat / $totalAnggaranGiat) * 100 : 0;
                            @endphp
                            <tr class="bg-indigo-600 text-white">
                                <td class="border border-gray-300 px-2 py-2 font-bold uppercase">
                                    {{ $perAkun->first()->first()->kegiatan->namagiat ?? 'Kegiatan ID: '.$idbl }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-bold">{{ number_format($totalAnggaranGiat, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-bold">{{ number_format($totalRealisasiGiat, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-bold">{{ number_format($sisaGiat, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center font-bold">{{ number_format($persenGiat, 1) }}%</td>
                            </tr>

                            @foreach($perAkun as $kodeakun => $items)
                                {{-- Baris Kode Rekening (Subtotal per Akun) --}}
                                @php
                                    $totalAnggaranRek = $items->sum('total_anggaran');
                                    $totalRealisasiRek = $items->sum('total_realisasi');
                                    $sisaRek = $totalAnggaranRek - $totalRealisasiRek;
                                    $persenRek = $totalAnggaranRek > 0 ? ($totalRealisasiRek / $totalAnggaranRek) * 100 : 0;
                                @endphp
                                <tr class="bg-gray-50 hover:bg-gray-100 transition">
                                    <td class="border border-gray-300 px-6 py-2 font-medium text-gray-700 italic">
                                        {{ $items->first()->korek->uraian_singkat ?? 'Tanpa Nama' }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-right text-gray-700">
                                        {{ number_format($totalAnggaranRek, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-right text-indigo-700">
                                        {{ number_format($totalRealisasiRek, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-right {{ $sisaRek < 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                                        {{ number_format($sisaRek, 0, ',', '.') }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center text-gray-600">
                                        {{ number_format($persenRek, 1) }}%
                                    </td>
                                </tr>
                                {{-- Perulangan rincian komponen (items) dihapus dari sini --}}
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="border border-gray-300 px-4 py-8 text-center text-gray-500 italic">
                                    Tidak ada data realisasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Baris Total Keseluruhan --}}
                    @if($dataRkas->count() > 0)
                    <tfoot class="bg-gray-800 text-white font-bold border-t-2">
                        <tr>
                            <td class="border border-gray-300 px-4 py-3 text-right uppercase">Total Keseluruhan</td>
                            <td class="border border-gray-300 px-3 py-3 text-right">
                                @php $grandAnggaran = $dataRkas->flatten(2)->sum('total_anggaran'); @endphp
                                {{ number_format($grandAnggaran, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-right">
                                @php $grandRealisasi = $dataRkas->flatten(2)->sum('total_realisasi'); @endphp
                                {{ number_format($grandRealisasi, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-right">
                                {{ number_format($grandAnggaran - $grandRealisasi, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-center">
                                @php $totalPersen = $grandAnggaran > 0 ? ($grandRealisasi / $grandAnggaran) * 100 : 0; @endphp
                                {{ number_format($totalPersen, 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
