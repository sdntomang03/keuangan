<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input SPJ Baru') }}
            </h2>
            <a href="{{ route('belanja.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; Batal / Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tampilkan Error Validasi Jika Ada --}}
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Terjadi Kesalahan!</strong>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('ekskul.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="belanja_id" value="{{ $belanja->id }}">

                {{-- BAGIAN 1: DATA UMUM (HEADER) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold mb-4 text-indigo-600">A. Data Umum Kegiatan</h3>

                        {{-- Info Sumber Dana --}}
                        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm rounded">
                            <span class="font-bold">Sumber Dana:</span> {{ $belanja->uraian }} <br>
                            <span class="font-bold">Sisa Pagu:</span> Rp {{ number_format($belanja->total_anggaran, 0,
                            ',', '.') }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Pilih Ekskul --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Jenis
                                    Ekstrakurikuler</label>
                                <select name="ref_ekskul_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">-- Pilih Ekskul --</option>
                                    @foreach($daftarEkskul as $eks)
                                    <option value="{{ $eks->id }}" {{ old('ref_ekskul_id')==$eks->id ? 'selected' : ''
                                        }}>
                                        {{ $eks->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pilih Pelatih --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Pelatih / Rekanan
                                </label>

                                {{-- 1. INPUT HIDDEN: Ini yang akan terkirim ke database (ID-nya) --}}
                                <input type="hidden" name="rekanan_id" value="{{ $belanja->rekanan_id }}">

                                {{-- 2. INPUT TAMPILAN: Hanya untuk dilihat user (Readonly) --}}
                                @php
                                $namaPelatih = $belanja->rekanan->nama_rekanan ?? 'Error: Data Rekanan Tidak Ditemukan';
                                $statusNpwp = $belanja->rekanan ? ($belanja->rekanan->npwp ? 'NPWP Ada' : 'Non-NPWP') :
                                '-';
                                @endphp

                                <input type="text" value="{{ $namaPelatih }} ({{ $statusNpwp }})"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 bg-gray-100 text-gray-600 rounded-md shadow-sm focus:ring-0 cursor-not-allowed"
                                    readonly>

                                <p class="text-xs text-gray-500 mt-1">
                                    *Pelatih otomatis diambil dari data Anggaran/RKAS.
                                </p>
                            </div>

                            {{-- Pilih TW --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Periode
                                    Triwulan (TW)</label>

                                {{-- PERBAIKAN: Tambah Input Hidden agar data terkirim --}}
                                <input type="hidden" name="tw" value="{{ $twaktif }}">

                                {{-- Input Visual --}}
                                <input type="text" value="Triwulan {{ $twaktif }}"
                                    class="w-full bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 rounded-md shadow-sm cursor-not-allowed focus:ring-0"
                                    readonly>

                                <p class="text-xs text-gray-500 mt-1">
                                    *Periode otomatis mengikuti Triwulan Aktif Sekolah.
                                </p>
                            </div>

                            {{-- Input Honor --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Honor Per Pertemuan
                                </label>

                                @php
                                // Ambil harga satuan dari rincian pertama, default 0 jika error
                                $hargaSatuan = $belanja->rincis->first()->harga_satuan ?? 0;
                                @endphp

                                {{-- PERBAIKAN: Input Hidden Honor --}}
                                <input type="hidden" name="honor" value="{{ $hargaSatuan }}">

                                {{-- Input Visual --}}
                                <input type="text" value="Rp {{ number_format($hargaSatuan, 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 rounded-md shadow-sm cursor-not-allowed focus:ring-0"
                                    readonly>

                                <p class="text-xs text-gray-500 mt-1">
                                    *Nominal honor dikunci sesuai Standar Harga Satuan di Anggaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN 2: RINCIAN PERTEMUAN (DYNAMIC ROWS) --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-indigo-600">B. Rincian & Dokumentasi Pertemuan</h3>
                            <button type="button" onclick="addMeetingRow()"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition">
                                + Tambah Pertemuan
                            </button>
                        </div>

                        {{-- Container Baris Input --}}
                        <div id="meeting-container" class="space-y-4">

                            {{-- BARIS PERTAMA (DEFAULT) --}}
                            <div
                                class="meeting-row border border-gray-200 dark:border-gray-700 p-4 rounded bg-gray-50 dark:bg-gray-700 relative">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                                    {{-- Tanggal --}}
                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                                        <input type="date" name="pertemuan[0][tanggal]"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm" required>
                                    </div>

                                    {{-- Materi --}}
                                    <div class="md:col-span-5">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Materi
                                            Kegiatan</label>
                                        <input type="text" name="pertemuan[0][materi]"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm"
                                            placeholder="Contoh: Latihan Dasar PBB" required>
                                    </div>

                                    {{-- Foto --}}
                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bukti
                                            Foto</label>
                                        <input type="file" name="pertemuan[0][foto]"
                                            class="w-full text-sm bg-white border border-gray-300 rounded p-1"
                                            accept="image/*" required>
                                    </div>

                                    <div class="md:col-span-1 text-right">
                                        {{-- Placeholder --}}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT --}}
                <div class="flex items-center justify-end gap-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition hover:scale-105">
                        Simpan Data SPJ
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- SCRIPT UNTUK MENAMBAH BARIS OTOMATIS --}}
    <script>
        let meetingIndex = 1;

        function addMeetingRow() {
            const container = document.getElementById('meeting-container');

            // HTML Template untuk baris baru
            const html = `
            <div class="meeting-row border border-gray-200 dark:border-gray-700 p-4 rounded bg-gray-50 dark:bg-gray-700 relative mt-4 transition-all duration-300 ease-in-out">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                        <input type="date" name="pertemuan[${meetingIndex}][tanggal]" class="w-full text-sm border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Materi Kegiatan</label>
                        <input type="text" name="pertemuan[${meetingIndex}][materi]" class="w-full text-sm border-gray-300 rounded-md shadow-sm" placeholder="Materi..." required>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bukti Foto</label>
                        <input type="file" name="pertemuan[${meetingIndex}][foto]" class="w-full text-sm bg-white border border-gray-300 rounded p-1" accept="image/*" required>
                    </div>

                    <div class="md:col-span-1 text-right">
                        <button type="button" onclick="this.closest('.meeting-row').remove()" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded shadow text-xs">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', html);
            meetingIndex++;
        }
    </script>
</x-app-layout>