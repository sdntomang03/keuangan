<x-app-layout>
    <x-slot name="header">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input Massal Dokumentasi') }}
            </h2>
            <a href="{{ route('ekskul.manage_details', $spj->belanja_id) }}"
                class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 hover:text-indigo-600 transition shadow-sm">
                &larr; Batal / Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- HEADER INFO --}}
            <div
                class="bg-white border border-gray-100 p-6 rounded-xl shadow-sm mb-8 flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                <div class="z-10">
                    <h3 class="font-bold text-gray-900 text-xl mb-1">{{ $spj->ekskul->nama }}</h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Pelatih: <span class="font-medium text-gray-700">{{ $spj->rekanan->nama_rekanan }}</span>
                    </div>
                </div>
                <div class="flex gap-8 z-10 bg-gray-50 px-6 py-3 rounded-lg border border-gray-100">
                    <div class="text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Kuota</span>
                        <span class="text-lg font-bold text-indigo-600">{{ $spj->jumlah_pertemuan }}</span>
                    </div>
                    <div class="w-px h-10 bg-gray-200"></div>
                    <div class="text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Terisi</span>
                        <span class="text-lg font-bold text-gray-700">{{ $spj->details->count() }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('ekskul.store_detail_bulk') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="spj_ekskul_id" value="{{ $spj->id }}">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    {{-- KOLOM KIRI: INPUT (STICKY) --}}
                    <div class="lg:col-span-1 lg:sticky lg:top-6 space-y-6">

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">1</span>
                                Data & File
                            </h4>

                            {{-- 1. Input JSON --}}
                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">List Materi (JSON)</label>

                                {{-- CONTOH FORMAT JSON --}}
                                <div
                                    class="text-xs text-slate-600 mb-2 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <p class="mb-1 font-medium">Contoh penulisan yang benar:</p>
                                    <code
                                        class="block font-mono text-indigo-600 bg-white p-2 rounded border border-slate-100 shadow-sm mt-1">
            ["Latihan Fisik", "Latihan Teknik Passing", "Game Internal"]
        </code>
                                    <p class="mt-1 text-[10px] text-slate-400">
                                        *Pastikan menggunakan tanda kurung siku <strong>[ ]</strong> dan tanda kutip dua
                                        <strong>" "</strong>.
                                    </p>
                                </div>

                                <div class="relative">
                                    <textarea name="materi_json" id="jsonArea" rows="6"
                                        class="w-full text-xs font-mono bg-slate-50 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm p-3 leading-relaxed"
                                        placeholder='["Materi 1", "Materi 2", "Materi 3"]' required></textarea>

                                </div>
                                @error('materi_json')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- 2. Input Jam (GLOBAL) - PERUBAHAN DISINI --}}
                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Kegiatan (Berlaku
                                    Semua)</label>
                                <div class="relative">
                                    <input type="number" name="jam_global" min="0" max="23" value="14" required
                                        class="w-full text-sm font-semibold text-gray-700 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-xs">WIB (0-23)</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Jam ini akan dipakai untuk semua foto yang
                                    diupload.</p>
                            </div>

                            <hr class="border-gray-100 my-5">

                            {{-- 3. Input File --}}
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Foto</label>
                                <label
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-indigo-50 hover:border-indigo-300 transition group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-2 text-gray-400 group-hover:text-indigo-500" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <p class="text-xs text-gray-500"><span class="font-semibold">Klik pilih</span>
                                            atau drag & drop</p>
                                    </div>
                                    <input type="file" name="foto_kegiatan[]" id="fileInput" multiple class="hidden"
                                        accept="image/*" required />
                                </label>
                                <div id="fileCountBadge" class="hidden mt-2 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span id="fileCountText">0</span> File Dipilih
                                    </span>
                                </div>
                            </div>

                            <button type="submit" id="btnSubmit" disabled
                                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg shadow-md transition-all transform active:scale-95 flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Data
                            </button>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: PREVIEW --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-h-[600px]">
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                                <h4 class="font-bold text-gray-800 flex items-center gap-2">
                                    <span
                                        class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">2</span>
                                    Preview & Pengaturan Tanggal
                                </h4>
                                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded">Sesuaikan tanggal per
                                    foto</span>
                            </div>

                            <div id="previewContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div
                                    class="col-span-1 md:col-span-2 flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                                    <div class="bg-white p-4 rounded-full shadow-sm mb-4">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h5 class="font-medium text-slate-600">Belum ada foto yang dipilih</h5>
                                    <p class="text-sm text-slate-400 mt-1 max-w-xs">Silakan upload foto pada kolom
                                        sebelah kiri.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        const btnSubmit = document.getElementById('btnSubmit');
        const fileCountBadge = document.getElementById('fileCountBadge');
        const fileCountText = document.getElementById('fileCountText');

        fileInput.addEventListener('change', function(event) {
            const files = event.target.files;

            // Reset Container
            previewContainer.innerHTML = '';

            if (files.length === 0) {
                btnSubmit.disabled = true;
                fileCountBadge.classList.add('hidden');
                // Restore placeholder code...
                previewContainer.innerHTML = `<div class="col-span-1 md:col-span-2 flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50"><h5 class="font-medium text-slate-600">Belum ada foto dipilih</h5></div>`;
                return;
            }

            // Update UI State
            btnSubmit.disabled = false;
            fileCountBadge.classList.remove('hidden');
            fileCountText.innerText = files.length;

            // Loop Files
            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.className = "group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col";

                    // HTML CARD (Hanya Tanggal, Jam dihapus)
                    card.innerHTML = `
                        <div class="relative h-48 w-full bg-gray-100 border-b border-gray-100">
                            <img src="${e.target.result}" class="w-full h-full object-contain p-2">
                            <div class="absolute top-2 left-2 bg-indigo-600/90 backdrop-blur text-white px-2 py-1 rounded-md text-xs font-bold shadow-sm">
                                #${index + 1}
                            </div>
                        </div>

                        <div class="p-4 bg-white flex-grow flex flex-col gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Tanggal</label>
                                <input type="date" name="tanggals[]" required
                                    class="w-full text-sm font-semibold text-gray-700 bg-gray-50 border-gray-300 rounded-lg px-2 py-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            </div>

                            <div class="mt-auto pt-3 border-t border-gray-100">
                                <p class="text-[10px] text-gray-400 mb-1 uppercase font-bold">Materi Kegiatan</p>
                                <div class="text-xs text-indigo-700 bg-indigo-50 px-2 py-1.5 rounded border border-indigo-100 font-medium truncate">
                                    JSON Index [${index}]
                                </div>
                            </div>
                        </div>
                    `;

                    previewContainer.appendChild(card);
                }

                reader.readAsDataURL(file);
            });
        });
    </script>
</x-app-layout>