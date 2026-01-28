<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Jurnal Kegiatan') }}
            </h2>
            <a href="{{ route('belanja.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- 2. FORM INPUT & TABEL LIST --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- FORM TAMBAH --}}
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

                            {{-- Tanggal --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                                <input type="date" name="tanggal_kegiatan"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>

                            {{-- Materi --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Materi</label>

                                <textarea name="materi" id="editor-materi"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Contoh: Latihan Dribble dan Passing...">{{ old('materi') }}</textarea>
                            </div>

                            {{-- Foto --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Dokumentasi</label>
                                <input type="file" name="foto_kegiatan"
                                    class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2"
                                    accept="image/*" required>
                                <p class="text-xs text-gray-500 mt-1">*Wajib upload foto kegiatan.</p>
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                                Simpan
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- TABEL LIST --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Riwayat Pertemuan</h3>

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
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Materi
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Foto
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($spj->details as $detail)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($detail->tanggal_kegiatan)->translatedFormat('d M
                                            Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {!! $detail->materi !!}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ asset('storage/'.$detail->foto_kegiatan) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$detail->foto_kegiatan) }}"
                                                    class="h-10 w-10 object-cover rounded border hover:scale-150 transition-transform cursor-pointer mx-auto">
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('ekskul.delete_detail', $detail->id) }}"
                                                method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-2 py-1 rounded">
                                                    Hapus
                                                </button>
                                            </form>
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
    <style>
        /* Mengatur tinggi minimal editor */
        .ck-editor__editable_inline {
            min-height: 150px;
        }

        /* Memperbaiki tampilan list (ul/ol) yang sering hilang kena reset CSS Tailwind */
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
        ClassicEditor
            .create(document.querySelector('#editor-materi'), {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo' ]
            })
            .catch(error => {
                console.error(error);
            });
    });
    </script>
</x-app-layout>
