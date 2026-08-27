<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Jurnal Kegiatan') }}
            </h2>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Tombol Cetak Kwitansi --}}
                <a href="{{ route('ekskul.cetak', $spj->id) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-md shadow-sm transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Kwitansi
                </a>

                {{-- Tombol Cetak Dokumentasi --}}
                <a href="{{ route('ekskul.cetak_absensi', $spj->id) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-md shadow-sm transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Cetak Dokumentasi
                </a>

                {{-- Tombol Cetak Absensi Sederhana --}}
                <a href="{{ route('ekskul.cetak_absensi_sederhana', $spj->id) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold rounded-md shadow-sm transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Cetak Absensi Sederhana
                </a>

                <div class="h-6 border-l border-gray-300 mx-1 hidden xl:block"></div>

                {{-- Tombol Bulk Upload --}}
                <a href="{{ route('ekskul.create_bulk', $spj->belanja_id) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-md shadow-sm transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Mode Bulk Upload
                </a>

                {{-- Tombol Laporan Simple --}}
                <a href="{{ route('ekskul.create_sederhana', $spj->belanja_id) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-md shadow-sm transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Laporan Simple
                </a>

                {{-- Tombol Kembali --}}
                <a href="{{ route('ekskul.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold rounded-md transition ease-in-out duration-150">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $defaultJam = $spj->ekskul && $spj->ekskul->waktu ? \Carbon\Carbon::parse($spj->ekskul->waktu)->format('H') : '14';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alert --}}
            @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg shadow-sm border border-green-200 mb-4">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-lg shadow-sm border border-red-200 mb-4">
                {{ session('error') }}
            </div>
            @endif

            {{-- 1. HEADER INFO --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-indigo-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Nomor Bukti</div>
                        <div class="font-bold text-lg">{{ $spj->belanja->no_bukti }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Pelatih</div>
                        <div class="font-bold text-lg">{{ $spj->rekanan->nama_rekanan }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status Kelengkapan</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                class="font-bold text-2xl {{ $sudahInput == $targetPertemuan ? 'text-green-600' : 'text-orange-500' }}">
                                {{ $sudahInput }} / {{ $targetPertemuan }}
                            </span>
                            <span class="text-sm text-gray-400">Pertemuan</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                            <div class="bg-indigo-600 h-2.5 rounded-full"
                                style="width: {{ ($sudahInput/$targetPertemuan)*100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. GRID LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI: FORM TAMBAH --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Tambah Pertemuan Baru</h3>

                        @if($sudahInput >= $targetPertemuan)
                        <div class="bg-green-100 text-green-700 p-4 rounded text-center text-sm">
                            <span class="font-bold">✓ Lengkap!</span><br>
                            Semua pertemuan sudah diinput.
                        </div>
                        @else
                        <form action="{{ route('ekskul.store_detail') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="spj_ekskul_id" value="{{ $spj->id }}">

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                                <input type="date" name="tanggal_kegiatan"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Materi</label>
                                <textarea name="materi" id="editor-materi"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Contoh: Latihan Dribble...">{{ old('materi') }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kegiatan (WIB)</label>
                                <div class="relative">
                                    <input type="number" name="jam_kegiatan" min="0" max="23" value="{{ $defaultJam }}"
                                        class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Contoh: 14" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Dokumentasi</label>
                                <input type="file" name="foto_kegiatan"
                                    class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2"
                                    accept="image/*" required>
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                                Simpan
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- KOLOM KANAN: TABEL LIST --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">

                        {{-- Header Kolom Kanan dengan Tombol Edit Bulk --}}
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="font-bold text-gray-700">Riwayat Pertemuan</h3>
                            @if(!$spj->details->isEmpty())
                            <button type="button" onclick="openModal('bulkEditFotoModal')"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-bold py-1.5 px-3 rounded flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Ganti Semua Foto
                            </button>
                            @endif
                        </div>

                        @if($spj->details->isEmpty())
                        <div class="text-center text-gray-400 py-10">
                            Belum ada data pertemuan yang diinput.
                        </div>
                        @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tgl
                                            (Otomatis Tersimpan)</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Materi
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Foto
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    {{-- Pastikan data diurutkan berdasarkan tanggal --}}
                                    @foreach($spj->details->sortBy('tanggal_kegiatan') as $detail)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($detail->tanggal_kegiatan)->translatedFormat('d M
                                            Y') }} <br>
                                            <span class="text-xs text-gray-400">{{
                                                \Carbon\Carbon::parse($detail->tanggal_kegiatan)->format('H:i') }}
                                                WIB</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {!! $detail->materi !!}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($detail->foto_kegiatan)
                                            <a href="{{ asset('storage/'.$detail->foto_kegiatan) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$detail->foto_kegiatan) }}"
                                                    class="h-10 w-10 object-cover rounded border hover:scale-150 transition-transform cursor-pointer mx-auto"
                                                    alt="Foto Kegiatan">
                                            </a>
                                            @else
                                            <span class="text-xs italic text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="openModal('editModal-{{ $detail->id }}')"
                                                    class="text-yellow-600 hover:text-yellow-800 font-bold text-xs bg-yellow-100 px-2 py-1 rounded">
                                                    Edit
                                                </button>

                                                <form action="{{ route('ekskul.delete_detail', $detail->id) }}"
                                                    method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-2 py-1 rounded">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL GANTI SEMUA FOTO SERENTAK --}}
    <div id="bulkEditFotoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                onclick="closeModal('bulkEditFotoModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Ganti Semua Foto (Bulk Edit)
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500"
                        onclick="closeModal('bulkEditFotoModal')">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <form action="{{ route('ekskul.update_foto_bulk') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="spj_ekskul_id" value="{{ $spj->id }}">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload File (Pilih Beberapa
                                Foto)</label>

                            {{-- Input multiple untuk banyak foto sekaligus --}}
                            <input type="file" name="foto_kegiatan_bulk[]" multiple accept="image/*"
                                class="w-full text-sm bg-indigo-50 border border-indigo-200 rounded-lg p-3 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer"
                                required>

                            <div class="mt-3 bg-blue-50 p-3 rounded text-xs text-blue-700 text-justify leading-relaxed">
                                <strong>Info:</strong> Silakan block/pilih foto sebanyak <b>{{ $spj->details->count()
                                    }}</b> file sekaligus di perangkat Anda.<br><br>
                                Sistem akan otomatis mendistribusikan foto tersebut dan menimpa foto lama, serta
                                membubuhkan <strong>Watermark menggunakan waktu & tanggal</strong> yang sudah ada di
                                database.
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Mulai Proses Watermark
                        </button>
                        <button type="button" onclick="closeModal('bulkEditFotoModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- 3. LOOPING MODAL EDIT PER BARIS --}}
    @foreach($spj->details as $detail)
    <div id="editModal-{{ $detail->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                onclick="closeModal('editModal-{{ $detail->id }}')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Pertemuan</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500"
                        onclick="closeModal('editModal-{{ $detail->id }}')">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>
                <form action="{{ route('ekskul.update_detail', $detail->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal_kegiatan"
                                value="{{ \Carbon\Carbon::parse($detail->tanggal_kegiatan)->format('Y-m-d') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kegiatan</label>
                            <input type="number" name="jam_kegiatan" min="0" max="23" value="{{ $defaultJam }}"
                                class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2"
                                placeholder="Ganti jika perlu">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Materi</label>
                            <textarea name="materi" id="editor-edit-{{ $detail->id }}"
                                class="w-full">{{ $detail->materi }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ganti Foto (Opsional)</label>
                            <input type="file" name="foto_kegiatan"
                                class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2"
                                accept="image/*">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="closeModal('editModal-{{ $detail->id }}')">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <style>
        .ck-editor__editable_inline {
            min-height: 150px;
        }

        .ck-content ul {
            list-style-type: disc;
            padding-left: 1.5rem;
        }

        .ck-content ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
        }
    </style>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if(document.querySelector('#editor-materi')) {
                ClassicEditor
                    .create(document.querySelector('#editor-materi'), {
                        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
                    })
                    .catch(error => console.error(error));
            }

            @foreach($spj->details as $detail)
                ClassicEditor
                    .create(document.querySelector('#editor-edit-{{ $detail->id }}'), {
                        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
                    })
                    .catch(error => console.error(error));
            @endforeach
        });

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</x-app-layout>