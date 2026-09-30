<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Daftar Pergeseran/Perubahan Pagu</h2>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                {{ strtoupper($anggaran->singkatan) }} {{ $anggaran->tahun }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
                <form method="GET" action="{{ route('rkas.penyesuaian-pagu.daftar') }}" class="flex flex-wrap items-end gap-4">
                    <div class="w-full max-w-xs">
                        <label for="tw" class="mb-1 block text-sm font-bold text-gray-700 dark:text-gray-200">Tampilkan triwulan</label>
                        <select id="tw" name="tw"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="semua" {{ (string) $twDipilih === 'semua' ? 'selected' : '' }}>Semua triwulan</option>
                            <option value="0" {{ (string) $twDipilih === '0' ? 'selected' : '' }}>Tahunan / belum ditentukan</option>
                            @foreach (range(1, 4) as $tw)
                                <option value="{{ $tw }}" {{ (string) $twDipilih === (string) $tw ? 'selected' : '' }}>TW {{ $tw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                        Tampilkan
                    </button>
                    <a href="{{ route('rkas.penyesuaian-pagu') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Buat Penyesuaian
                    </a>
                    <a href="{{ route('rkas.penyesuaian-pagu.tw') }}" class="rounded-lg border border-indigo-300 px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-700 dark:text-indigo-300 dark:hover:bg-gray-700">
                        Lihat Pagu TW Hasil Penyesuaian
                    </a>
                </form>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Triwulan aktif sekolah saat ini: {{ $twAktif ? 'TW '.$twAktif : 'tahunan / belum ditentukan' }}.
                </p>
            </section>

            @if ($rekeningRekap->isNotEmpty())
                <section class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="font-bold text-gray-900 dark:text-white">Rekap Kode Rekening</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Total pagu yang dapat digeser/diubah untuk triwulan yang ditampilkan.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kode rekening</th>
                                    <th class="px-4 py-3 text-left">Akun</th>
                                    <th class="px-4 py-3 text-right">Pagu dapat digeser/diubah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($rekeningRekap as $rekening)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $rekening['kode'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $rekening['singkat'] }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                            Rp{{ number_format((float) $rekening['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @forelse ($penyesuaian as $catatan)
                <section class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-700/50">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">
                                {{ ucfirst($catatan->jenis) }} · {{ $catatan->tw ? 'TW '.$catatan->tw : 'Tahunan / belum ditentukan' }}
                            </h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dicatat {{ $catatan->created_at?->format('d/m/Y H:i') ?? '-' }}
                                @if ($catatan->user)
                                    oleh {{ $catatan->user->name }}
                                @endif
                            </p>
                        </div>
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                            {{ $catatan->rincis->count() }} perubahan bulanan
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-white text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left">Komponen</th>
                                    <th class="px-4 py-3 text-center">Bulan</th>
                                    <th class="px-4 py-3 text-right">Volume awal</th>
                                    <th class="px-4 py-3 text-right">Volume setelah</th>
                                    <th class="px-4 py-3 text-right">Selisih volume</th>
                                    <th class="px-4 py-3 text-right">Pagu dapat digeser/diubah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($catatan->rincis as $rinci)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="block font-semibold text-gray-900 dark:text-white">{{ $rinci->namakomponen ?: 'Komponen tanpa nama' }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $rinci->idblrinci }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $rinci->bulan }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $rinci->volume_awal, 2, ',', '.') }} {{ $rinci->satuan }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $rinci->volume_setelah, 2, ',', '.') }} {{ $rinci->satuan }}</td>
                                        <td class="px-4 py-3 text-right text-sm {{ $rinci->volume_selisih > 0 ? 'text-green-700 dark:text-green-300' : 'text-blue-700 dark:text-blue-300' }}">
                                            {{ $rinci->volume_selisih > 0 ? '+' : '' }}{{ number_format((float) $rinci->volume_selisih, 2, ',', '.') }} {{ $rinci->satuan }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                            Rp{{ number_format((float) $rinci->pagu_dapat_digeser, 0, ',', '.') }}
                                            @if ($rinci->ppn_persen > 0)
                                                <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">termasuk PPN {{ $rinci->ppn_persen }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                    Belum ada catatan pergeseran/perubahan untuk triwulan yang dipilih.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
