<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ringkasan Alokasi Per Item') }}
        </h2>
    </x-slot>

    {{-- Inisialisasi Alpine Data dengan ID Anggaran --}}
    <div x-data="rincianPage({{ $anggaran->id }})" x-init="init()" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. INFO ANGGARAN --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm text-blue-700">
                        Anggaran Aktif: <span class="font-bold text-lg">{{ strtoupper($anggaran->singkatan) }} {{
                            $anggaran->tahun }}</span>
                    </p>
                    {{-- Indikator Loading Kecil di Atas --}}
                    <div x-show="isLoading" class="flex items-center text-blue-600 text-xs font-bold animate-pulse">
                        <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat Data...
                    </div>
                </div>
            </div>

            {{-- 2. CONTROL BAR (SEARCH & SORT) --}}
            <div class="mb-4 flex flex-col md:flex-row gap-4 justify-between items-end">
                {{-- Search --}}
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Pencarian</label>
                    <div class="relative">
                        <input type="text" x-model.debounce.500ms="search" @input="fetchData()"
                            placeholder="Cari Nama Barang, Spek, atau Kode Rekening..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm pl-9">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Sort --}}
                <div class="flex gap-3 w-full md:w-auto">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Sorting</label>
                        <select x-model="sortField" @change="fetchData()"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 w-full">
                            <option value="created_at">Input Terbaru</option>
                            <option value="namakomponen">Nama Barang</option>
                            <option value="hargasatuan">Harga Satuan</option>
                            <option value="idkomponen">ID Komponen</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Arah</label>
                        <select x-model="sortDirection" @change="fetchData()"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 w-full">
                            <option value="asc">A - Z (Kecil ke Besar)</option>
                            <option value="desc">Z - A (Besar ke Kecil)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0"> {{-- Padding 0 agar tabel full width --}}

                    {{-- 3. TABEL DATA --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-center w-12 font-bold text-gray-500 uppercase tracking-wider text-xs">
                                        No
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left w-1/4 font-bold text-gray-500 uppercase tracking-wider text-xs">
                                        Uraian / Barang
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider text-xs">
                                        Harga Satuan
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">
                                        Alokasi (Ringkas)
                                    </th>

                                    {{-- PERBAIKAN DI SINI: HAPUS 'w-1/5', GANTI DENGAN 'min-w-[250px]' --}}
                                    <th
                                        class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-xs min-w-[250px]">
                                        ID Komp
                                    </th>

                                    <th
                                        class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider text-xs">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">

                                {{-- A. TAMPILAN SAAT LOADING --}}
                                <tr x-show="isLoading">
                                    <td colspan="6" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="animate-spin h-8 w-8 text-blue-500 mb-2"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <span class="text-gray-500 text-sm">Sedang mengambil data...</span>
                                        </div>
                                    </td>
                                </tr>

                                {{-- B. LOOPING DATA --}}
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr class="hover:bg-gray-50 transition-colors align-top">
                                        {{-- No --}}
                                        <td class="px-4 py-3 text-center text-gray-500 font-mono text-xs"
                                            x-text="index + 1"></td>

                                        {{-- Uraian --}}
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900" x-text="item.namakomponen"></div>
                                            <div class="text-xs text-gray-500 mt-1" x-show="item.spek">
                                                Spek: <span x-text="item.spek"></span>
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-blue-600 bg-blue-50 inline-block px-1 rounded border border-blue-100">
                                                Total Vol: <span class="font-bold" x-text="item.total_volume"></span>
                                                <span x-text="item.satuan"></span>
                                            </div>
                                        </td>

                                        {{-- Harga --}}
                                        <td class="px-4 py-3 text-right font-mono font-medium text-gray-700"
                                            x-text="formatRupiah(item.hargasatuan*1.12)"></td>

                                        {{-- Alokasi Bulan --}}
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1.5" x-show="item.alokasi_aktif.length > 0">
                                                <template x-for="alokasi in item.alokasi_aktif">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 border border-green-200">
                                                        <span x-text="alokasi.nama_bulan"></span>
                                                        <span
                                                            class="ml-1 font-bold bg-white px-1 rounded text-green-900"
                                                            x-text="alokasi.volume"></span>
                                                    </span>
                                                </template>
                                            </div>
                                            <span x-show="item.alokasi_aktif.length === 0"
                                                class="text-red-400 text-xs italic">Belum ada alokasi</span>
                                        </td>

                                        {{-- ID Komponen (EDITABLE) --}}
                                        {{-- Pastikan class min-w-[250px] juga ada di sini --}}
                                        <td class="px-4 py-3 text-left min-w-[250px]">
                                            <div class="relative flex items-center">
                                                {{-- Input Field --}}
                                                <input type="text" x-model="item.idkomponen"
                                                    @keydown.enter="$event.target.blur()" @blur="updateIdKomponen(item)"
                                                    class="w-full text-xs font-mono border-gray-300 rounded focus:border-blue-500 focus:ring-blue-500 px-2 py-1 transition-colors"
                                                    :class="{'bg-green-50 border-green-400': item.save_status === 'success', 'bg-red-50 border-red-400': item.save_status === 'error'}"
                                                    placeholder="-">

                                                {{-- Indikator Status (Kanan Input) --}}
                                                <div class="absolute right-2 flex items-center pointer-events-none">
                                                    {{-- Loading Spinner --}}
                                                    <svg x-show="item.is_updating"
                                                        class="animate-spin h-3 w-3 text-blue-500"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>

                                                    {{-- Success Checkmark --}}
                                                    <svg x-show="item.save_status === 'success'"
                                                        x-transition.duration.500ms class="h-4 w-4 text-green-600"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>

                                                    {{-- Error Icon --}}
                                                    <svg x-show="item.save_status === 'error'"
                                                        class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-4 py-3 text-center">
                                            <button @click="openModal(item)"
                                                class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 px-3 py-1.5 rounded text-xs font-medium transition-all flex items-center mx-auto shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Rincian
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                {{-- C. EMPTY STATE --}}
                                <tr x-show="!isLoading && items.length === 0" style="display: none;">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic bg-gray-50">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <p>Data tidak ditemukan.</p>
                                            <p class="text-xs">Coba ubah kata kunci pencarian.</p>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL (X-TELEPORT ke Body agar Z-Index aman) --}}
        {{-- ========================================================== --}}
        <template x-teleport="body">
            <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-[9999] overflow-y-auto" role="dialog"
                aria-modal="true">

                {{-- Backdrop --}}
                <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                {{-- Modal Panel --}}
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="isOpen" @click.away="closeModal()" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">

                        {{-- Header --}}
                        <div
                            class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Detail Rincian Belanja</h3>
                            <button @click="closeModal()"
                                class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                                {{-- Kolom Kiri: Info Barang --}}
                                <div class="space-y-4">
                                    {{-- SNP --}}
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Standar
                                            Pendidikan</label>
                                        <p class="text-sm text-gray-800 mt-1 font-medium bg-gray-50 p-2 rounded border border-gray-100"
                                            x-text="data.snp || '-'"></p>
                                    </div>

                                    {{-- Sub Kegiatan (Copyable) --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Sub
                                            Kegiatan</label>
                                        <div x-data="{ copied: false }" class="relative group">
                                            <p class="text-sm text-gray-800 mt-1 leading-snug cursor-pointer hover:text-blue-600 hover:bg-blue-50 rounded px-2 py-1 -mx-2 transition-all border border-transparent hover:border-blue-100"
                                                x-text="data.kegiatan" title="Klik untuk menyalin"
                                                @click="copyToClipboard(data.kegiatan); copied = true; setTimeout(() => copied = false, 2000);">
                                            </p>
                                            <div x-show="copied" style="display: none;"
                                                class="absolute left-0 -top-8 bg-black text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg z-10 flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-green-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg> Tersalin!
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kode Rekening (Copyable) --}}
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Kode
                                            Rekening</label>
                                        <div x-data="{ copied: false }" class="relative group">
                                            <div class="flex items-center mt-1 cursor-pointer hover:bg-blue-50 p-1 -m-1 rounded transition-colors"
                                                title="Klik Salin Nama Rekening"
                                                @click="copyToClipboard(data.nama_rekening); copied = true; setTimeout(() => copied = false, 2000);">
                                                <span
                                                    class="bg-gray-100 text-gray-800 text-xs font-mono px-2 py-0.5 rounded mr-2 border border-gray-200"
                                                    x-text="data.kode_rekening"></span>
                                                <span class="text-sm text-gray-700 leading-tight"
                                                    x-text="data.nama_rekening"></span>
                                            </div>
                                            <div x-show="copied" style="display: none;"
                                                class="absolute left-0 -top-8 bg-black text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg z-10 flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-green-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg> Tersalin!
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Komponen --}}
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Komponen
                                            / Barang</label>
                                        <p class="text-md font-bold text-gray-900 mt-1" x-text="data.namakomponen"></p>
                                        <p class="text-xs text-gray-500 italic mt-0.5" x-show="data.spek"
                                            x-text="'Spek: ' + data.spek"></p>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Info Harga & Form --}}
                                <div class="flex flex-col justify-center">
                                    <div
                                        class="bg-blue-50 rounded-lg p-5 border border-blue-100 shadow-sm h-full flex flex-col justify-between">

                                        <div class="space-y-4">
                                            <div
                                                class="flex justify-between items-center border-b border-blue-200 pb-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-blue-600 uppercase">Harga
                                                        Satuan</label>
                                                    <p class="text-xs text-gray-500" x-text="'Per ' + data.satuan"></p>
                                                </div>
                                                <p class="text-lg font-semibold text-gray-900"
                                                    x-text="formatRupiah(data.hargasatuan)"></p>
                                            </div>

                                            {{-- Harga PPN --}}
                                            <div
                                                class="flex justify-between items-center border-b border-blue-200 pb-3">
                                                <label class="block text-xs font-medium text-blue-600 uppercase">Harga +
                                                    PPN 12%</label>
                                                <p class="text-lg font-semibold text-gray-900"
                                                    x-text="formatRupiah(Math.round(data.hargasatuan * 1.12))"></p>
                                            </div>

                                            <div class="flex justify-between items-center pt-1">
                                                <label class="block text-xs font-bold text-blue-700 uppercase">Total
                                                    Pagu (1 Thn)</label>
                                                <p class="text-xl font-bold text-blue-800"
                                                    x-text="formatRupiah(data.total_pagu)"></p>
                                            </div>
                                        </div>

                                        {{-- FORM UPDATE ID KOMPONEN --}}
                                        <div class="mt-4 pt-3 border-t border-blue-200 border-dashed">
                                            {{-- Perhatikan :action yang dinamis --}}
                                            <form method="POST" :action="'/rkas/' + data.id + '/update-idkomponen'"
                                                class="block">
                                                @csrf
                                                @method('PATCH')

                                                <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Edit
                                                    ID Komponen</label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="idkomponen" x-model="data.idkomponen"
                                                        class="w-full text-sm border-blue-300 bg-white text-gray-800 rounded focus:ring-blue-500 focus:border-blue-500 px-2 py-1 shadow-sm placeholder-blue-200"
                                                        placeholder="Input ID...">
                                                    <button type="submit"
                                                        class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded hover:bg-blue-700 transition shadow-sm">
                                                        Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200 mb-4">

                            {{-- Grid Rincian Bulanan --}}
                            <div>
                                <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Rincian Alokasi Per Bulan
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <template x-for="(bulanName, index) in listBulan" :key="index">
                                        <div class="border rounded-md p-2 text-center transition-all"
                                            :class="hasAlokasi(index + 1) ? 'bg-green-50 border-green-200 shadow-sm' : 'bg-gray-50 border-gray-100 opacity-60'">

                                            <div class="text-xs font-semibold mb-1"
                                                :class="hasAlokasi(index + 1) ? 'text-green-700' : 'text-gray-400'"
                                                x-text="bulanName"></div>

                                            <template x-if="hasAlokasi(index + 1)">
                                                <div>
                                                    <div class="text-xs font-bold text-gray-800"
                                                        x-text="formatRupiah(getNominal(index + 1))"></div>
                                                    <div class="text-[10px] text-gray-500 mt-0.5">Vol: <span
                                                            class="font-bold" x-text="getVolume(index + 1)"></span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="!hasAlokasi(index + 1)">
                                                <div class="text-xs text-gray-300 font-mono">-</div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button @click="closeModal()" type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>

    {{-- SCRIPT DEFINITION --}}
    <script>
        function rincianPage(anggaranId) {
            return {
            // State
            isOpen: false,
            items: [],
            search: '',
            sortField: 'created_at',
            sortDirection: 'desc',
            isLoading: false,
            anggaranId: anggaranId,
            data: {},
            listBulan: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],

            init() {
                this.fetchData();
            },

            fetchData() {
                this.isLoading = true;
                let url = `/api/rkas/${this.anggaranId}/data?search=${this.search}&sort_field=${this.sortField}&sort_direction=${this.sortDirection}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Mapping Data: Tambahkan properti untuk tracking status edit per item
                        this.items = data.map(item => ({
                            ...item,
                            original_id: item.idkomponen, // Simpan nilai asli
                            is_updating: false,          // Status Loading
                            save_status: null            // Status Success/Error
                        }));
                        this.isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isLoading = false;
                    });
            },

            // --- FUNGSI UPDATE LANGSUNG (INLINE EDIT) ---
            updateIdKomponen(item) {
                // 1. Cek apakah ada perubahan (agar tidak spam request)
                if (item.idkomponen === item.original_id) return;

                // 2. Set State Loading
                item.is_updating = true;
                item.save_status = null;

                // 3. Kirim Request PATCH
                fetch(`/rkas/${item.id}/update-idkomponen`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        idkomponen: item.idkomponen
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Gagal update');
                    return response.json();
                })
                .then(data => {
                    // Sukses
                    item.is_updating = false;
                    item.save_status = 'success';
                    item.original_id = item.idkomponen; // Update nilai asli

                    // Hilangkan tanda sukses setelah 2 detik
                    setTimeout(() => { item.save_status = null; }, 2000);
                })
                .catch(error => {
                    // Gagal
                    console.error('Error update:', error);
                    item.is_updating = false;
                    item.save_status = 'error';
                });
            },

                // Modal Actions
                openModal(itemData) {
                    this.data = itemData;
                    this.isOpen = true;
                },
                closeModal() {
                    this.isOpen = false;
                },

                // Helper: Copy Clipboard
                copyToClipboard(text) {
                    if (!text) return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text);
                    } else {
                        // Fallback HTTP
                        let textArea = document.createElement("textarea");
                        textArea.value = text;
                        textArea.style.position = "fixed"; textArea.style.left = "-9999px"; textArea.style.top = "0";
                        document.body.appendChild(textArea);
                        textArea.focus(); textArea.select();
                        try { document.execCommand('copy'); } catch (err) {}
                        document.body.removeChild(textArea);
                    }
                },

                // Helper: Format Rupiah
                formatRupiah(angka) {
                    if (angka === undefined || angka === null) return 'Rp 0';
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(angka);
                },

                // Helper: Logic Bulan Aktif
                hasAlokasi(bulanAngka) {
                    if (!this.data.rincian || !this.data.rincian[bulanAngka]) return false;
                    return this.data.rincian[bulanAngka].nominal > 0 || this.data.rincian[bulanAngka].volume > 0;
                },
                getNominal(bulanAngka) {
                    return this.data.rincian[bulanAngka].nominal;
                },
                getVolume(bulanAngka) {
                    return this.data.rincian[bulanAngka].volume;
                }
            }
        }
    </script>
</x-app-layout>