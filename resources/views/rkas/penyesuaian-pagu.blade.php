<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Penyesuaian Pagu Komponen · Pergeseran/Perubahan</h2>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                {{ strtoupper($anggaran->singkatan) }} {{ $anggaran->tahun }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-100">
                Pilih jenis penyesuaian dan komponen yang akan disesuaikan. Nilai pagu dan realisasi yang ditampilkan adalah akumulasi satu tahun anggaran aktif. Data yang disimpan hanya berupa catatan, tidak mengubah tabel RKAS.
            </div>

            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('info'))
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                    {{ session('info') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <form action="{{ route('rkas.penyesuaian-pagu') }}" method="GET"
                    class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800 md:col-span-2">
                    <div class="min-w-[14rem] flex-1">
                        <label for="filter-kegiatan" class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Filter kegiatan</label>
                        <select id="filter-kegiatan" name="kegiatan"
                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Semua kegiatan</option>
                            @foreach ($daftarKegiatan as $kegiatan)
                                <option value="{{ $kegiatan->idbl }}" {{ $kegiatanDipilih === $kegiatan->idbl ? 'selected' : '' }}>
                                    {{ $kegiatan->kodegiat ? $kegiatan->kodegiat.' · ' : '' }}{{ $kegiatan->namagiat ?: $kegiatan->idbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                        Tampilkan
                    </button>
                    @if ($kegiatanDipilih)
                        <a href="{{ route('rkas.penyesuaian-pagu') }}"
                            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Hapus filter
                        </a>
                    @endif
                </form>

                <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
                    <label for="jenis" class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Jenis penyesuaian</label>
                    <select id="jenis" name="jenis" form="penyesuaian-pagu-form" required
                        class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih pergeseran atau perubahan</option>
                        <option value="pergeseran" {{ old('jenis') === 'pergeseran' ? 'selected' : '' }}>Pergeseran</option>
                        <option value="perubahan" {{ old('jenis') === 'perubahan' ? 'selected' : '' }}>Perubahan</option>
                    </select>
                </div>
            </div>

            <form id="penyesuaian-pagu-form" action="{{ route('rkas.penyesuaian-pagu.proses') }}" method="POST" x-data="{ selected: [] }">
                @csrf
                @if ($kegiatanDipilih)
                    <input type="hidden" name="kegiatan" value="{{ $kegiatanDipilih }}">
                @endif
                @forelse ($komponenPerKegiatan as $items)
                    @php
                        $kegiatan = $items->first()->kegiatan;
                        $idbl = $items->first()->idbl;
                        $groupId = 'kegiatan-'.$loop->index;
                    @endphp
                    <section class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                                    {{ $kegiatan->kodegiat ?? $idbl ?? 'Kegiatan tidak terdefinisi' }}
                                </p>
                                <h3 class="mt-1 font-bold text-gray-900 dark:text-white">
                                    {{ $kegiatan->namagiat ?? 'Kegiatan tidak terdefinisi' }}
                                </h3>
                            </div>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-600 dark:text-gray-300">
                                <input type="checkbox"
                                    @change="document.querySelectorAll('.{{ $groupId }}').forEach(input => { input.checked = $event.target.checked; input.dispatchEvent(new Event('change')); })"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Pilih kegiatan
                            </label>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-center">Pilih</th>
                                        <th class="px-4 py-3 text-left">Komponen / spesifikasi</th>
                                        <th class="px-4 py-3 text-left">Akun</th>
                                        <th class="px-4 py-3 text-right">Pagu setahun</th>
                                        <th class="px-4 py-3 text-right">Realisasi setahun</th>
                                        <th class="px-4 py-3 text-right">Sisa pagu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($items as $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="komponen[]" value="{{ $item->id }}"
                                                    class="{{ $groupId }} rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    x-model="selected">
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $item->namakomponen ?: 'Komponen tanpa nama' }}</p>
                                                @if ($item->spek)
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->spek }}</p>
                                                @endif
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Volume {{ $item->koefisien ?: '-' }} {{ $item->satuan }}
                                                </p>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                {{ $item->korek->singkat ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                Rp{{ number_format((float) $item->pagu_setahun, 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-300">
                                                Rp{{ number_format((float) $item->realisasi_setahun, 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                Rp{{ number_format((float) $item->sisa_pagu, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @empty
                    <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                        Belum ada data pagu atau realisasi komponen pada anggaran aktif.
                    </div>
                @endforelse

                @if ($komponenPerKegiatan->isNotEmpty())
                    <div class="sticky bottom-0 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                        <p class="text-sm text-gray-600 dark:text-gray-300"><span x-text="selected.length"></span> komponen dipilih</p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('rkas.penyesuaian-pagu.daftar') }}"
                                    class="rounded-lg border border-indigo-300 px-5 py-2.5 text-sm font-bold text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-700 dark:text-indigo-300 dark:hover:bg-gray-700">
                                    Lihat Catatan Penyesuaian
                                </a>
                                <button type="submit" :disabled="selected.length === 0"
                                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    Proses Penyesuaian
                                </button>
                            </div>
                        </div>
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
