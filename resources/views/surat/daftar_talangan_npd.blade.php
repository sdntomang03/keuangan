<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            {{-- JUDUL HALAMAN (KIRI) --}}
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Daftar Surat Talangan & NPD
            </h2>

            {{-- KUMPULAN TOMBOL AKSI (KANAN) --}}
            <div class="flex items-center gap-3">

                {{-- Tombol Reset Nomor --}}
                <a href="{{ route('surat.regenerate_all') }}"
                    onclick="return confirm('Apakah Anda yakin ingin mengurutkan ulang semua nomor surat? Tindakan ini tidak dapat dibatalkan.')"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Reset Nomor</span>
                </a>

                {{-- TOMBOL INPUT BARU (Modal Alpine.js) --}}
                <button x-data @click="$dispatch('open-npd-modal')" type="button"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Input Surat NPD
                </button>

            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- TABEL DATA SURAT TALANGAN --}}
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead
                            class="bg-gray-100 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-4 text-left">No</th>
                                <th class="px-6 py-4 text-left">Tanggal Buat</th>
                                <th class="px-6 py-4 text-left">Nomor Surat</th>
                                <th class="px-6 py-4 text-left">Jenis Surat</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($listSurat as $surat)
                            <tr class="hover:bg-indigo-50/50 dark:hover:bg-gray-700/50 transition duration-150 group">

                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">
                                    {{ \Carbon\Carbon::parse($surat->tanggal_surat ??
                                    $surat->created_at)->translatedFormat('d F Y') }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-gray-100">
                                        {{ $surat->nomor_surat ?? 'Belum ada nomor' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 uppercase tracking-wider">
                                        {{ $surat->jenis_surat }}
                                    </span>
                                </td>

                                {{-- KOLOM AKSI: TOMBOL DELETE --}}
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('surat.hapus_talangan_npd', $surat->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat ini? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200 hover:border-transparent rounded-md text-xs font-bold transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-gray-800">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500">Belum ada data Surat Talangan/NPD pada
                                        triwulan ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Input NPD --}}
    <div x-data="{ openModalNpd: false }" @open-npd-modal.window="openModalNpd = true">

        <div x-show="openModalNpd" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div x-show="openModalNpd" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="openModalNpd = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

                <div x-show="openModalNpd" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                    <div
                        class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-gray-100"
                                    id="modal-title">
                                    Input Surat NPD
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Silakan masukkan tanggal untuk dokumen Nota Pencairan Dana (NPD).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('npd.store_surat') }}" method="POST">
                        @csrf
                        <div class="px-4 py-5 sm:p-6 space-y-4">
                            {{-- Input Jenis Surat --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    Jenis Surat
                                </label>
                                <select name="jenis_surat" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                    <option value="" disabled selected>-- Pilih Jenis Surat --</option>
                                    <option value="NPD">NPD (Nota Pencairan Dana)</option>
                                    <option value="STS">STS (Surat Tanda Setoran)</option>
                                </select>
                            </div>

                            {{-- Input Tanggal --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    Tanggal Surat
                                </label>
                                <input type="date" name="tanggal_npd" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-gray-700">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition-all">
                                Simpan Tanggal
                            </button>
                            <button type="button" @click="openModalNpd = false"
                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto transition-all">
                                Batal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
