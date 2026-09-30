<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Pagu TW Hasil Penyesuaian</h2>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                {{ strtoupper($anggaran->singkatan) }} {{ $anggaran->tahun }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <form method="GET" action="{{ route('rkas.penyesuaian-pagu.tw') }}" class="flex flex-wrap items-end gap-4">
                        <div class="w-full max-w-xs">
                            <label for="tw" class="mb-1 block text-sm font-bold text-gray-700 dark:text-gray-200">Triwulan</label>
                            <select id="tw" name="tw" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                @foreach (range(1, 4) as $tw)
                                    <option value="{{ $tw }}" {{ $twDipilih === $tw ? 'selected' : '' }}>TW {{ $tw }} (bulan {{ ($tw - 1) * 3 + 1 }}–{{ $tw * 3 }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                            Tampilkan
                        </button>
                    </form>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('rkas.penyesuaian-pagu.daftar', ['tw' => $twDipilih]) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Daftar Penyesuaian
                        </a>
                        <a href="{{ route('rkas.penyesuaian-pagu') }}" class="rounded-lg border border-indigo-300 px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-700 dark:text-indigo-300 dark:hover:bg-gray-700">
                            Buat Penyesuaian
                        </a>
                    </div>
                </div>
                <p class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-100">
                    Pagu dihitung dari AKB untuk bulan {{ $bulanTw[0] }}–{{ $bulanTw[2] }} dan perubahan terakhir yang tersimpan per komponen dan bulan. Laporan ini hanya membaca data; tabel RKAS, AKB, dan AKB rincian tidak diubah.
                </p>
            </section>

            @if ($rekeningRekap->isNotEmpty())
                <section class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white">Rekap Pagu per Kode Rekening · TW {{ $twDipilih }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kode rekening</th>
                                    <th class="px-4 py-3 text-left">Akun</th>
                                    <th class="px-4 py-3 text-right">Pagu awal</th>
                                    <th class="px-4 py-3 text-right">Penyesuaian bersih</th>
                                    <th class="px-4 py-3 text-right">Pagu TW setelah penyesuaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($rekeningRekap as $rekening)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $rekening['kode'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $rekening['akun'] }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">Rp{{ number_format((float) $rekening['pagu_dasar'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm {{ $rekening['penyesuaian'] < 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                                            {{ $rekening['penyesuaian'] > 0 ? '+' : '' }}Rp{{ number_format((float) $rekening['penyesuaian'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">Rp{{ number_format((float) $rekening['pagu_hasil'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white">Rincian Komponen · TW {{ $twDipilih }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kegiatan</th>
                                    <th class="px-4 py-3 text-left">Kode rekening / akun</th>
                                    <th class="px-4 py-3 text-left">Komponen</th>
                                    <th class="px-4 py-3 text-right">Pagu awal</th>
                                    <th class="px-4 py-3 text-right">Penyesuaian bersih</th>
                                    <th class="px-4 py-3 text-right">Pagu TW hasil</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($komponen as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="block font-semibold">{{ $item['kodegiat'] }}</span>
                                            <span class="text-xs">{{ $item['namagiat'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="block font-semibold">{{ $item['kode_rekening'] }}</span>
                                            <span class="text-xs">{{ $item['akun'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            <span class="block font-semibold">{{ $item['komponen'] }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['idblrinci'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">Rp{{ number_format((float) $item['pagu_dasar'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm {{ $item['penyesuaian'] < 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                                            {{ $item['penyesuaian'] > 0 ? '+' : '' }}Rp{{ number_format((float) $item['penyesuaian'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">Rp{{ number_format((float) $item['pagu_hasil'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @else
                <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                    Belum ada pagu AKB atau penyesuaian untuk TW {{ $twDipilih }}.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
