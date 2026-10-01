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
                    @if ($anggaran)
                    <span class="mx-1">&middot;</span> Anggaran aktif: {{ $anggaran->nama_anggaran }} {{ $anggaran->tahun }}
                    @endif
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
                    @if ($anggarans->isEmpty())
                    <p class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                        Belum ada tahun anggaran untuk sekolah ini. Tambahkan anggaran sebelum membuat catatan.
                    </p>
                    @endif

                    <form action="{{ route('catatan.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf
                        <div>
                            <label for="anggaran_id" class="block text-sm font-semibold text-gray-700 mb-1">Tahun anggaran</label>
                            <select id="anggaran_id" name="anggaran_id" required
                                class="block w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($anggarans as $pilihanAnggaran)
                                <option value="{{ $pilihanAnggaran->id }}" {{ (string) old('anggaran_id', $anggaran?->id) === (string) $pilihanAnggaran->id ? 'selected' : '' }}>
                                    {{ $pilihanAnggaran->nama_anggaran }} · {{ $pilihanAnggaran->tahun }}
                                </option>
                                @endforeach
                            </select>
                            @error('anggaran_id')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lampiran (opsional, maks. 10 berkas)</label>
                            <input type="file" name="files[]" multiple
                                accept="image/png, image/jpeg, image/webp, application/pdf"
                                class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('file') border-rose-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">PDF atau gambar JPG, PNG, WEBP; maksimal 5 MB per berkas.</p>
                            @error('files')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                            @error('files.*')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_tl" value="1"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ old('is_tl')
                                ? 'checked' : '' }}>
                            Tandai sebagai Tindak Lanjut (TL)
                        </label>

                        <button type="submit" {{ $anggarans->isEmpty() ? 'disabled' : '' }}
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

                        {{-- Filter tahun dan triwulan catatan --}}
                        <form method="GET" class="flex flex-wrap items-center gap-2">
                            <label for="filter-anggaran" class="text-sm text-gray-600 whitespace-nowrap">Tahun:</label>
                            <select id="filter-anggaran" name="anggaran" onchange="this.form.submit()"
                                class="block w-44 pl-3 pr-8 py-2 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="semua" {{ $filterAnggaran === 'semua' ? 'selected' : '' }}>Semua tahun</option>
                                @foreach ($anggarans as $pilihanAnggaran)
                                <option value="{{ $pilihanAnggaran->id }}" {{ $filterAnggaran === (string) $pilihanAnggaran->id ? 'selected' : '' }}>
                                    {{ $pilihanAnggaran->nama_anggaran }} · {{ $pilihanAnggaran->tahun }}
                                </option>
                                @endforeach
                            </select>
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
                        <div x-data="{ editOpen: false }"
                            class="flex flex-col sm:flex-row gap-4 p-4 border border-gray-100 rounded-xl hover:shadow-md transition-all duration-200 bg-white">

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
                                    <p class="mb-2 text-xs font-semibold text-indigo-700">
                                        {{ $catatan->anggaran?->nama_anggaran }} · {{ $catatan->anggaran?->tahun }}
                                    </p>

                                    {{-- Isi Teks Catatan --}}
                                    <p
                                        class="text-sm text-gray-700 whitespace-pre-line break-words leading-relaxed mb-3">
                                        {{ $catatan->catatan }}</p>

                                    @php
                                    $lampiranPaths = $catatan->lampirans->pluck('file_path');
                                    if ($catatan->file_path) {
                                        $lampiranPaths->prepend($catatan->file_path);
                                    }
                                    @endphp
                                    @if ($lampiranPaths->isNotEmpty())
                                    <div class="mb-3 flex flex-wrap gap-3">
                                        @foreach ($lampiranPaths as $path)
                                        @php
                                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                        @endphp
                                        <a href="{{ asset('storage/'.$path) }}" target="_blank" rel="noopener"
                                            class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                            @if ($isImage)
                                            <img src="{{ asset('storage/'.$path) }}" alt="Lampiran catatan"
                                                class="h-full w-full object-cover">
                                            @else
                                            <span class="flex flex-col items-center gap-1 text-xs font-bold uppercase text-blue-600">
                                                <i class="fa fa-file-pdf text-2xl"></i>{{ $ext }}
                                            </span>
                                            @endif
                                        </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                {{-- Footer Aksi (Tombol TL & Hapus Sejajar) --}}
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-gray-50 mt-auto">
                                    <form action="{{ route('catatan.toggle-tl', $catatan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition {{ $catatan->is_tl ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            <i class="fa fa-sync-alt"></i>
                                            {{ $catatan->is_tl ? 'Tandai Belum di-TL' : 'Tandai Sudah di-TL' }}
                                        </button>
                                    </form>

                                    <button type="button" @click="editOpen = true"
                                        class="text-xs font-semibold text-blue-700 hover:text-blue-900">
                                        <i class="fa fa-edit mr-1"></i> Edit
                                    </button>

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

                            <div x-show="editOpen" x-cloak
                                @keydown.escape.window="editOpen = false"
                                class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
                                aria-labelledby="edit-catatan-title-{{ $catatan->id }}">
                                <div class="flex min-h-screen items-center justify-center px-4 py-8">
                                    <div class="fixed inset-0 bg-gray-900/60" @click="editOpen = false"></div>
                                    <div class="relative z-10 w-full max-w-2xl rounded-xl bg-white p-6 shadow-2xl">
                                        <div class="mb-5 flex items-center justify-between">
                                            <h3 id="edit-catatan-title-{{ $catatan->id }}" class="text-lg font-bold text-gray-900">
                                                Edit Catatan
                                            </h3>
                                            <button type="button" @click="editOpen = false"
                                                class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                                                aria-label="Tutup modal">✕</button>
                                        </div>
                                        @if ($catatan->file_path || $catatan->lampirans->isNotEmpty())
                                        <div class="mb-4 space-y-2">
                                            <h4 class="text-sm font-semibold text-gray-700">Lampiran saat ini</h4>
                                            @if ($catatan->file_path)
                                            <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                                                <a href="{{ asset('storage/'.$catatan->file_path) }}" target="_blank" rel="noopener"
                                                    class="truncate text-sm text-blue-700 hover:underline">
                                                    {{ basename($catatan->file_path) }}
                                                </a>
                                                <form action="{{ route('catatan.lampiran-lama.destroy', $catatan) }}" method="POST"
                                                    onsubmit="return confirm('Hapus lampiran ini? Berkas akan dihapus permanen.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                        <i class="fa fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                            @foreach ($catatan->lampirans as $lampiran)
                                            <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                                                <a href="{{ asset('storage/'.$lampiran->file_path) }}" target="_blank" rel="noopener"
                                                    class="truncate text-sm text-blue-700 hover:underline">
                                                    {{ basename($lampiran->file_path) }}
                                                </a>
                                                <form action="{{ route('catatan.lampiran.destroy', [$catatan, $lampiran]) }}" method="POST"
                                                    onsubmit="return confirm('Hapus lampiran ini? Berkas akan dihapus permanen.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                        <i class="fa fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        <form action="{{ route('catatan.update', $catatan) }}" method="POST"
                                            enctype="multipart/form-data" class="space-y-4">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label class="mb-1 block text-sm font-semibold text-gray-700">Tahun anggaran</label>
                                                <select name="anggaran_id" required class="block w-full rounded-lg border-gray-300 text-sm">
                                                    @foreach ($anggarans as $pilihanAnggaran)
                                                    <option value="{{ $pilihanAnggaran->id }}" {{ (int) $catatan->anggaran_id === (int) $pilihanAnggaran->id ? 'selected' : '' }}>
                                                        {{ $pilihanAnggaran->nama_anggaran }} · {{ $pilihanAnggaran->tahun }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-semibold text-gray-700">Isi catatan</label>
                                                <textarea name="catatan" rows="4" required
                                                    class="block w-full rounded-lg border-gray-300 text-sm">{{ $catatan->catatan }}</textarea>
                                            </div>
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="is_tl" value="1" {{ $catatan->is_tl ? 'checked' : '' }}>
                                                Tandai sebagai Tindak Lanjut
                                            </label>
                                            <div>
                                                <label class="mb-1 block text-sm font-semibold text-gray-700">Tambah lampiran</label>
                                                <input type="file" name="files[]" multiple accept="image/png,image/jpeg,image/webp,application/pdf"
                                                    class="block w-full text-sm text-gray-600">
                                                <p class="mt-1 text-xs text-gray-500">PDF atau gambar JPG, PNG, WEBP; maksimal 5 MB per berkas.</p>
                                                @error('files.*')
                                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="flex justify-end gap-3 border-t pt-4">
                                                <button type="button" @click="editOpen = false"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                                    Batal
                                                </button>
                                                <button type="submit"
                                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
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