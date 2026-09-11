<x-app-layout>


    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fa fa-sticky-note text-blue-600 mr-3"></i> Catatan &amp; Tindak Lanjut
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $sekolah->nama_sekolah ?? $sekolah->name ?? '' }}
                    @isset($anggaran)
                    <span class="mx-1">&middot;</span> {{ $anggaran->nama ?? $anggaran->tahun_anggaran ?? '' }}
                    @endisset
                </p>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border-l-4 border-green-500 shadow-sm flex items-start"
            role="alert">
            <i class="fa fa-check-circle text-green-500 mt-0.5 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-green-800">Berhasil</h3>
                <div class="mt-1 text-sm text-green-700">{{ session('success') }}</div>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 p-4 rounded-lg bg-rose-50 border-l-4 border-rose-500 shadow-sm flex items-start" role="alert">
            <i class="fa fa-exclamation-circle text-rose-500 mt-0.5 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-rose-800">Gagal</h3>
                <div class="mt-1 text-sm text-rose-700">{{ session('error') }}</div>
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-rose-50 border-l-4 border-rose-500 shadow-sm" role="alert">
            <h3 class="text-sm font-medium text-rose-800">Terdapat {{ $errors->count() }} kesalahan input:</h3>
            <ul class="mt-1 text-sm text-rose-700 list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Form Tambah Catatan --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md border-t-4 border-blue-600 p-6">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fa fa-plus-circle text-blue-600 mr-2"></i> Tambah Catatan
                    </h4>

                    <form action="{{ route('catatan.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Catatan</label>
                            <textarea name="catatan" rows="4"
                                class="block w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('catatan') border-rose-500 @enderror"
                                placeholder="Tulis catatan di sini...">{{ old('catatan') }}</textarea>
                            @error('catatan')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lampiran
                                (opsional)</label>
                            <input type="file" name="file"
                                accept="image/png, image/jpeg, image/webp, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('file') border-rose-500 @enderror">
                            @error('file')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_tl" value="1"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ old('is_tl')
                                ? 'checked' : '' }}>
                            Tandai sebagai Tindak Lanjut (TL)
                        </label>

                        <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-colors duration-200">
                            <i class="fa fa-save mr-2"></i> Simpan Catatan
                        </button>
                    </form>
                </div>
            </div>

            {{-- Daftar Catatan --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md border-t-4 border-blue-600 overflow-hidden">

                    <div
                        class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                        <h4 class="font-bold text-gray-800">Daftar Catatan</h4>

                        {{-- Filter Triwulan (GET, tidak perlu CSRF) --}}
                        <form method="GET" class="flex items-center gap-2">
                            <label for="filter-tw" class="text-sm text-gray-600 whitespace-nowrap">Triwulan:</label>
                            <select id="filter-tw" name="tw" onchange="this.form.submit()"
                                class="block w-36 pl-3 pr-8 py-2 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="semua" {{ (string) $filterTw==='semua' ? 'selected' : '' }}>Semua
                                    Triwulan</option>
                                @for ($i = 1; $i <= 4; $i++) <option value="{{ $i }}" {{ (string) $filterTw===(string)
                                    $i ? 'selected' : '' }}>Triwulan {{ $i }}</option>
                                    @endfor
                            </select>
                        </form>
                    </div>

                    <div class="p-6 space-y-4">
                        @forelse ($catatans as $catatan)
                        <div
                            class="flex flex-col sm:flex-row gap-4 p-4 border border-gray-100 rounded-xl hover:shadow-md transition-all duration-200 bg-white">

                            {{-- KONTEN LAMPIRAN (GAMBAR ATAU DOKUMEN) --}}
                            <div class="flex-shrink-0">
                                @if ($catatan->file_path)
                                @php
                                $ext = strtolower(pathinfo($catatan->file_path, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                @endphp

                                @if($isImage)
                                {{-- Tampilan Jika Berupa Gambar --}}
                                <a href="{{ asset('storage/'.$catatan->file_path) }}" target="_blank" rel="noopener"
                                    class="block group relative">
                                    <img src="{{ asset('storage/'.$catatan->file_path) }}" alt="Lampiran catatan"
                                        class="w-24 h-24 object-cover rounded-xl border border-gray-200 group-hover:opacity-95 transition">
                                    <div
                                        class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition rounded-xl flex items-center justify-center text-white text-xs font-medium gap-1">
                                        <i class="fa fa-search-plus"></i>
                                    </div>
                                </a>
                                @else
                                {{-- Tampilan Jika Berupa Dokumen (PDF, Word, Excel) --}}
                                <a href="{{ asset('storage/'.$catatan->file_path) }}" target="_blank" rel="noopener"
                                    class="w-24 h-24 rounded-xl border border-gray-200 bg-gray-50 flex flex-col items-center justify-center text-gray-500 gap-1 hover:bg-gray-100 transition group">
                                    <i
                                        class="fa fa-file-alt text-2xl text-blue-500 group-hover:scale-110 transition-transform"></i>
                                    <span
                                        class="text-[10px] uppercase font-bold tracking-wider px-1 truncate max-w-[80px]">{{
                                        $ext }}</span>
                                </a>
                                @endif
                                @else
                                {{-- Tampilan Jika Tanpa File --}}
                                <div
                                    class="w-24 h-24 rounded-xl border border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center text-gray-400 gap-1 select-none">
                                    <i class="fa fa-image text-lg opacity-40"></i>
                                    <span class="text-[10px] uppercase font-medium tracking-wider">Tanpa Lampiran</span>
                                </div>
                                @endif
                            </div>

                            {{-- KONTEN UTAMA CATATAN --}}
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                                <div>
                                    {{-- Header Badge & Tanggal --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                            TW {{ $catatan->tw }}
                                        </span>

                                        @if ($catatan->is_tl)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            <i class="fa fa-check-circle mr-1"></i> Sudah di-TL
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                            <i class="fa fa-clock mr-1"></i> Belum di-TL
                                        </span>
                                        @endif

                                        <span class="text-xs text-gray-400 ml-auto">
                                            {{ optional($catatan->created_at)->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    </div>

                                    {{-- Isi Teks Catatan --}}
                                    <p
                                        class="text-sm text-gray-700 whitespace-pre-line break-words leading-relaxed mb-3">
                                        {{ $catatan->catatan }}</p>
                                </div>

                                {{-- Footer Aksi (Tombol TL & Hapus Sejajar) --}}
                                <div class="flex items-center justify-between pt-3 border-t border-gray-50 mt-auto">
                                    <form action="{{ route('catatan.toggle-tl', $catatan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $catatan->is_tl ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            <i class="fa fa-sync-alt"></i>
                                            {{ $catatan->is_tl ? 'Tandai Belum di-TL' : 'Tandai Sudah di-TL' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('catatan.destroy', $catatan->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2.5 py-1.5 rounded-lg font-semibold inline-flex items-center gap-1 transition">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12 text-gray-400">
                            <i class="fa fa-inbox text-3xl mb-2"></i>
                            <p class="text-sm">Belum ada catatan untuk periode ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>