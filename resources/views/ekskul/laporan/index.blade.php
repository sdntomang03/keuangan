<x-app-layout>
    {{-- Inisialisasi state utama halaman menggunakan Alpine.js --}}
    <div x-data="{
        // State untuk input dinamis multiple foto/pertemuan di dalam modal
        pertemuanList: [{ id: Date.now() }],
        tambahBaris() { this.pertemuanList.push({ id: Date.now() }); },
        hapusBaris(index) { if(this.pertemuanList.length > 1) this.pertemuanList.splice(index, 1); },

        // State untuk menangkap konteks ekskul yang dipilih secara otomatis (Tanpa Select Dropdown)
        selectedEkskul: { id: '', nama: '', periode: '' },

        // State untuk Fitur Lightbox Gallery (Pop-up Foto)
        lightboxOpen: false,
        lightboxImages: [],
        lightboxIndex: 0,

        bukaLightbox(urls, index) {
            this.lightboxImages = urls;
            this.lightboxIndex = index;
            this.lightboxOpen = true;
        },
        fotoNext() {
            if (this.lightboxIndex < this.lightboxImages.length - 1) {
                this.lightboxIndex++;
            } else {
                this.lightboxIndex = 0;
            }
        },
        fotoPrev() {
            if (this.lightboxIndex > 0) {
                this.lightboxIndex--;
            } else {
                this.lightboxIndex = this.lightboxImages.length - 1;
            }
        }
    }" @keydown.window.escape="lightboxOpen = false" @keydown.window.arrow-right="if(lightboxOpen) fotoNext()"
        @keydown.window.arrow-left="if(lightboxOpen) fotoPrev()">

        <x-slot name="header">
            <div>
                <h2 class="font-black text-xl text-gray-800 leading-tight italic uppercase tracking-tight">
                    {{ __('Laporan Kegiatan Ekskul') }}
                </h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Daftar cabang ekskul aktif dan pelaporan dokumentasi
                    latihan per pertemuan</p>
            </div>
        </x-slot>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- ALERTS --}}
                @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded-r-lg shadow-sm flex justify-between items-center"
                    x-data="{ show: true }" x-show="show">
                    <span class="flex items-center">✅ {{ session('success') }}</span>
                    <button @click="show = false"
                        class="text-emerald-500 hover:text-emerald-700 font-black text-lg">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div
                    class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-lg shadow-sm">
                    <div class="font-black mb-1">⚠️ Gagal Validasi:</div>
                    <ul class="list-disc pl-5 text-xs font-semibold space-y-0.5">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
                @endif

                {{-- LOOPING UTAMA DATA EKSKUL BERDASARKAN USER_ID --}}
                <div class="space-y-8">
                    @forelse($ekskuls as $ekskul)
                    <div class="bg-white shadow-sm rounded-2xl border border-gray-200/70 overflow-hidden">

                        {{-- Header Kartu Ekskul --}}
                        <div
                            class="p-4 sm:p-5 bg-indigo-50/30 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <span
                                    class="bg-indigo-600 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-wider">
                                    Ekskul</span>
                                <h3 class="font-black text-gray-900 text-lg mt-1 leading-tight">{{ $ekskul->nama_ekskul
                                    }}</h3>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-gray-500 font-bold">
                                    <span>📅 Periode: <span class="text-indigo-600">{{ $ekskul->periode ?? 'Semua Waktu'
                                            }}</span></span>
                                    <span>🏫 Sekolah: <span class="text-gray-700">{{ $ekskul->sekolah->nama_sekolah ??
                                            'Pusat' }}</span></span>
                                    <span>👨‍💼 Pelatih: <span class="text-gray-700">{{ $ekskul->user->name ?? 'Pelatih'
                                            }}</span></span>
                                </div>
                            </div>

                            {{-- Aksi khusus untuk Ekskul ini --}}
                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                {{-- Tombol Tambah Latihan Langsung Mengunci ID Ekskul --}}
                                <button @click="
                                    selectedEkskul = { id: '{{ $ekskul->id }}', nama: '{{ addslashes($ekskul->nama_ekskul) }}', periode: '{{ $ekskul->periode }}' };
                                    pertemuanList = [{ id: Date.now() }];
                                    $dispatch('open-modal', 'add-ekskul-laporan-modal');"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-xl transition shadow-sm flex items-center justify-center border border-indigo-500 active:scale-95 duration-150">
                                    + Tambah Pertemuan
                                </button>

                                @can('akses-admin-pusat')
                                <button @click="
                                    selectedEkskul = { id: '{{ $ekskul->id }}', nama: '{{ addslashes($ekskul->nama_ekskul) }}' };
                                    $dispatch('open-modal', 'confirm-ekskul-deletion-modal');"
                                    class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition shadow-sm"
                                    title="Hapus Kelompok Ekskul">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Isi Konten Grid Foto Pertemuan --}}
                        <div class="p-4 sm:p-6 bg-white">
                            <div class="space-y-4">
                                @forelse($ekskul->laporans as $laporan)
                                <div class="bg-gray-50/60 p-4 rounded-xl border border-gray-100 shadow-inner">
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/60 pb-2 mb-3">
                                        <div class="text-xs font-black text-gray-800 uppercase tracking-wide">📌 Materi:
                                            {{ $laporan->materi }}</div>
                                        <div
                                            class="text-[11px] font-mono font-bold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md w-fit">
                                            📅 {{ \Carbon\Carbon::parse($laporan->tanggal_kegiatan)->translatedFormat('d
                                            F Y') }}
                                        </div>
                                    </div>

                                    @if($laporan->catatan)
                                    <p class="text-xs text-gray-500 italic mb-3 font-medium">Catatan: "{{
                                        $laporan->catatan }}"</p>
                                    @endif

                                    @php
                                    $arrayUrlFoto = $laporan->fotos->map(function($f) {
                                    return asset('storage/' . $f->path_foto);
                                    })->toArray();
                                    @endphp

                                    {{-- Grid Foto Dokumentasi --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                        @foreach($laporan->fotos as $key => $foto)
                                        <div class="relative rounded-xl overflow-hidden shadow-sm border border-gray-200 group bg-gray-100 cursor-pointer"
                                            @click="bukaLightbox({{ json_encode($arrayUrlFoto) }}, {{ $key }})">

                                            <img src="{{ asset('storage/' . $foto->path_foto) }}" alt="Bukti Kegiatan"
                                                class="w-full h-24 sm:h-32 object-cover hover:scale-105 transition duration-300">

                                            <div
                                                class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">
                                                Buka Foto 🔍
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @empty
                                <div
                                    class="text-center py-6 text-xs font-bold text-gray-400 italic bg-gray-50/30 rounded-xl border border-dashed">
                                    Belum ada rekaman laporan pertemuan untuk cabang ekskul ini. Silakan klik "+ Tambah
                                    Pertemuan".
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                    @empty
                    <div
                        class="bg-white p-12 text-center text-sm font-bold text-gray-400 italic rounded-2xl border border-dashed border-gray-300">
                        Belum ada kelompok cabang ekskul resmi yang terdaftar untuk akun Anda.
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP LIGHTBOX (PREV - NEXT DOKUMENTASI FOTO) --}}
        {{-- ========================================================================= --}}
        <div x-show="lightboxOpen" x-transition.opacity.duration.300ms
            class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black/90 p-4 backdrop-blur-md"
            style="display: none;">

            <button @click="lightboxOpen = false"
                class="absolute top-4 right-4 text-white/70 hover:text-white text-3xl font-black p-2 z-[110] transition-colors focus:outline-none">&times;</button>

            <div class="absolute top-5 text-white/60 text-xs font-mono tracking-widest font-bold z-[110]">
                <span x-text="lightboxIndex + 1"></span> / <span x-text="lightboxImages.length"></span>
            </div>

            <div class="relative w-full max-w-4xl h-full flex items-center justify-center p-2">
                <button @click="fotoPrev()" x-show="lightboxImages.length > 1"
                    class="absolute left-2 sm:left-4 z-[110] bg-white/10 hover:bg-white/20 text-white rounded-full p-3 sm:p-4 hover:scale-105 transition active:scale-95 text-lg font-bold focus:outline-none select-none">
                    &#10094;
                </button>

                <div class="w-full max-h-[75vh] flex items-center justify-center select-none">
                    <img :src="lightboxImages[lightboxIndex]"
                        class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl border border-white/10"
                        alt="Pratinjau Foto Laporan">
                </div>

                <button @click="fotoNext()" x-show="lightboxImages.length > 1"
                    class="absolute right-2 sm:right-4 z-[110] bg-white/10 hover:bg-white/20 text-white rounded-full p-3 sm:p-4 hover:scale-105 transition active:scale-95 text-lg font-bold focus:outline-none select-none">
                    &#10095;
                </button>
            </div>

            <div class="hidden sm:block text-white/30 text-[10px] font-medium uppercase tracking-widest mt-2">
                Gunakan tombol arah panah ◄ ► pada keyboard atau klik tombol di layar
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL INPUT LAPORAN (SISTEM DIKUNCI OTOMATIS TANPA MENGETIK / SELECT) --}}
        {{-- ========================================================================= --}}
        <x-modal name="add-ekskul-laporan-modal" max-width="2xl" focusable>
            <form action="{{ route('ekskul.laporan.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 sm:p-8 text-left max-h-[90vh] overflow-y-auto custom-scroll">
                @csrf

                {{-- KUNCI UTAMA BACKEND: Menyimpan ID Ekskul secara tersembunyi (Hidden Input) --}}
                <input type="hidden" name="ekskul_id" :value="selectedEkskul.id">

                <div class="border-b pb-3 mb-5">
                    <h2 class="text-lg font-extrabold text-gray-900">Upload Laporan Pertemuan Baru</h2>
                    <div class="mt-1 flex flex-wrap gap-2 items-center text-xs">
                        <span class="bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-md font-black text-[11px]"
                            x-text="selectedEkskul.nama"></span>
                        <span class="text-gray-400 font-bold"
                            x-text="'Periode: ' + (selectedEkskul.periode ? selectedEkskul.periode : 'Semua Waktu')"></span>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Detail Baris Pertemuan
                    </h3>
                    <button type="button" @click="tambahBaris()"
                        class="text-[10px] font-black bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2.5 py-1.5 rounded-lg transition">
                        + Tambah Baris
                    </button>
                </div>

                {{-- LOOP CONTAINER BARIS MASUKAN PERTEMUAN --}}
                <div class="space-y-4">
                    <template x-for="(baris, index) in pertemuanList" :key="baris.id">
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl relative shadow-inner">
                            <button type="button" @click="hapusBaris(index)" x-show="pertemuanList.length > 1"
                                class="absolute top-3 right-3 text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-2 py-0.5 rounded border border-red-100">&times;</button>

                            <div class="grid grid-cols-1 gap-3 pt-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tanggal</label>
                                        <input type="date" :name="`pertemuan[${index}][tanggal_kegiatan]`" required
                                            class="w-full text-xs font-bold border-gray-200 rounded-xl">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Materi
                                            Latihan</label>
                                        <input type="text" :name="`pertemuan[${index}][materi]`" required
                                            placeholder="Contoh: Latihan Dasar Passing"
                                            class="w-full text-xs font-bold border-gray-200 rounded-xl">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Pilih
                                        Gambar Dokumentasi (Bisa pilih lebih dari 1 foto)</label>
                                    <input type="file" :name="`pertemuan[${index}][fotos][]`" multiple required
                                        accept="image/*"
                                        class="w-full text-xs border border-gray-200 rounded-xl bg-white p-1 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-700">
                                </div>
                                <div>
                                    <input type="text" :name="`pertemuan[${index}][catatan]`"
                                        placeholder="Catatan/absensi singkat pertemuan (Opsional)"
                                        class="w-full text-[11px] font-medium text-gray-500 border-gray-100 rounded-xl bg-white/50">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600 shadow-md">Simpan Laporan</x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- MODAL HAPUS (HANYA UNTUK AKSES ADMIN PUSAT) --}}
        <x-modal name="confirm-ekskul-deletion-modal" focusable>
            <form :action="'{{ url('ekskul-laporan') }}/' + selectedEkskul.id" method="post" class="p-6 text-center">
                @csrf @method('delete')
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-md font-bold text-gray-900">Hapus Seluruh Kelompok Laporan <span
                        class="text-red-600 uppercase" x-text="selectedEkskul.nama"></span>?</h2>
                <p class="text-xs text-gray-400 mt-1 font-semibold leading-relaxed">Tindakan ini menghapus data induk,
                    sub-data latihan, beserta seluruh berkas foto fisik di server secara permanen.</p>
                <div class="mt-6 flex justify-center gap-3">
                    <button type="button" x-on:click="$dispatch('close')"
                        class="px-4 py-2 text-xs font-bold text-gray-500 uppercase rounded-xl hover:bg-gray-100 transition">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase rounded-xl shadow-md transition">Ya,
                        Hapus</button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>