<x-app-layout>
    {{-- Tambahkan state lightbox di dalam x-data utama --}}
    <div x-data="{
        // State untuk input dinamis multiple pertemuan di modal
        pertemuanList: [{ id: Date.now() }],
        tambahBaris() { this.pertemuanList.push({ id: Date.now() }); },
        hapusBaris(index) { if(this.pertemuanList.length > 1) this.pertemuanList.splice(index, 1); },
        selectedEkskul: { id: '', nama: '' },

        // State Baru untuk Fitur Lightbox (Navigasi Foto Pop-up)
        lightboxOpen: false,
        lightboxImages: [], // Menampung semua daftar url foto dalam satu pertemuan
        lightboxIndex: 0,   // Menampung index foto yang sedang aktif

 bukaLightbox(urls, index) {
            this.lightboxImages = urls;
            this.lightboxIndex = index;
            this.lightboxOpen = true;
        },
        fotoNext() {
            if (this.lightboxIndex < this.lightboxImages.length - 1) {
                this.lightboxIndex++;
            } else {
                this.lightboxIndex = 0; // Balik ke foto pertama jika sudah di ujung
            }
        },
        fotoPrev() {
            if (this.lightboxIndex > 0) {
                this.lightboxIndex--;
            } else {
                this.lightboxIndex = this.lightboxImages.length - 1; // Ke foto terakhir jika klik prev di foto pertama
            }
        }
    }" {{-- Fitur navigasi keyboard: Tombol panah kiri, kanan, dan esc untuk menutup --}}
        @keydown.window.escape="lightboxOpen = false" @keydown.window.arrow-right="if(lightboxOpen) fotoNext()"
        @keydown.window.arrow-left="if(lightboxOpen) fotoPrev()">

        <x-slot name="header">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-black text-xl text-gray-800 leading-tight italic uppercase tracking-tight">
                        {{ __('Laporan Kegiatan Ekskul') }}
                    </h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Pencatatan materi latihan dan dokumentasi foto
                        dengan fitur galeri pop-up interaktif</p>
                </div>
                <button x-on:click="$dispatch('open-modal', 'add-ekskul-laporan-modal')"
                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-3 px-5 rounded-xl transition shadow-md flex items-center justify-center border border-indigo-500 active:scale-95 duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Laporan Baru
                </button>
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

                {{-- RESPONSIVE TABLE WRAPPER --}}
                <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                    <th class="p-4 w-1/3">Nama Ekskul & Periode</th>
                                    <th class="p-4 w-1/3">Pelatih / Sekolah</th>
                                    <th class="p-4 text-right w-1/3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($ekskuls as $ekskul)
                                <tr class="bg-indigo-50/20 font-semibold border-t">
                                    <td class="p-4 text-sm text-gray-800">
                                        <div>
                                            <span class="font-black text-gray-900 text-base block">{{
                                                $ekskul->nama_ekskul }}</span>
                                            <span class="text-[11px] text-gray-400 font-bold block mt-0.5">🕒 {{
                                                $ekskul->periode ?? 'Semua Periode' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-gray-700">
                                        <div class="text-gray-800 font-bold leading-tight">{{ $ekskul->user->name ??
                                            'Pelatih' }}</div>
                                        <div class="text-[11px] text-indigo-500 font-black tracking-wide mt-0.5">🏫 {{
                                            $ekskul->sekolah->nama_sekolah ?? 'Pusat' }}</div>
                                    </td>
                                    <td class="p-4 text-right flex justify-end items-center gap-2 pt-6">
                                        <button @click="
                                            selectedEkskul = { id: '{{ $ekskul->id }}', nama: '{{ addslashes($ekskul->nama_ekskul) }}' };
                                            $dispatch('open-modal', 'confirm-ekskul-deletion-modal');"
                                            class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm"
                                            title="Hapus Semua Laporan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                {{-- AREA CONTAINER LIST PERTEMUAN --}}
                                <tr class="bg-white">
                                    <td colspan="3" class="p-4 sm:p-6 pb-8">
                                        <div class="space-y-4">
                                            @forelse($ekskul->laporans as $laporan)
                                            <div
                                                class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-inner">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/60 pb-2 mb-3">
                                                    <div
                                                        class="text-xs font-black text-gray-800 uppercase tracking-wide">
                                                        📌 Materi: {{ $laporan->materi }}</div>
                                                    <div
                                                        class="text-[11px] font-mono font-bold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md w-fit">
                                                        📅 {{
                                                        \Carbon\Carbon::parse($laporan->tanggal_kegiatan)->translatedFormat('d
                                                        F Y') }}
                                                    </div>
                                                </div>

                                                @if($laporan->catatan)
                                                <p class="text-xs text-gray-500 italic mb-3 font-medium">Catatan: "{{
                                                    $laporan->catatan }}"</p>
                                                @endif

                                                {{-- Buat array url foto murni menggunakan map php agar siap dibaca
                                                Alpine.js --}}
                                                @php
                                                $arrayUrlFoto = $laporan->fotos->map(function($f) {
                                                return asset('storage/' . $f->path_foto);
                                                })->toArray();
                                                @endphp

                                                {{-- GRID GAMBAR DOKUMENTASI --}}
                                                <div
                                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                                    @foreach($laporan->fotos as $key => $foto)
                                                    <div class="relative rounded-xl overflow-hidden shadow-sm border border-gray-200 group bg-gray-100 cursor-pointer"
                                                        {{-- Trigger Klik: Kirim seluruh list foto di pertemuan ini dan
                                                        index foto yang diklik --}}
                                                        @click="bukaLightbox({{ json_encode($arrayUrlFoto) }}, {{ $key }})">

                                                        <img src="{{ asset('storage/' . $foto->path_foto) }}"
                                                            alt="Bukti Kegiatan"
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
                                            <div class="text-center py-2 text-xs font-bold text-gray-400 italic">Belum
                                                ada rincian pertemuan untuk kelompok kegiatan ini.</div>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-sm font-bold text-gray-400 italic">Belum
                                        ada rekaman laporan kegiatan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                    <img :src="lightboxImages[lightboxIndex]" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
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

        {{-- MODAL INPUT DATA (MAX-WIDTH 2XL) --}}
        <x-modal name="add-ekskul-laporan-modal" max-width="2xl" focusable>
            <form action="{{ route('ekskul.laporan.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 sm:p-8 text-left max-h-[90vh] overflow-y-auto custom-scroll">
                @csrf
                <div class="border-b pb-3 mb-5">
                    <h2 class="text-lg font-extrabold text-gray-900">Upload Dokumentasi Latihan</h2>
                    <p class="text-[10px] text-indigo-500 font-black uppercase tracking-wider mt-0.5">Tambahkan
                        pertemuan baru ke dalam ekskul Anda</p>
                </div>

                {{-- MENGAMBIL DATA EKSKUL & PERIODE SESUAI USER_ID --}}
                <div class="mb-5 bg-indigo-50/40 p-4 rounded-xl border border-indigo-100 shadow-sm">
                    <x-input-label for="ekskul_id" value="Pilih Data Induk Ekskul Anda"
                        class="text-[10px] font-bold uppercase text-gray-400 mb-1" />

                    <select id="ekskul_id" name="ekskul_id"
                        class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm font-bold py-2.5 bg-white cursor-pointer"
                        required>
                        <option value="">-- Pilih Cabang Ekskul & Periode --</option>
                        @foreach($dropdownEkskuls as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->nama_ekskul }} | Periode: {{ $item->periode ?? 'Semua Waktu' }}
                            @can('akses-admin-pusat') - (Pelatih: {{ $item->user->name }}) @endcan
                        </option>
                        @endforeach
                    </select>

                    @if($dropdownEkskuls->isEmpty())
                    <p class="text-[10px] text-red-500 mt-2 font-bold italic">⚠️ Anda belum memiliki data Induk Ekskul.
                        Silakan hubungi Admin untuk dibuatkan data Induk terlebih dahulu.</p>
                    @else
                    <p class="text-[10px] text-gray-500 mt-2 font-medium italic">Nama ekskul dan periode dimuat otomatis
                        berdasarkan akun Anda.</p>
                    @endif
                </div>

                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Detail Baris Pertemuan
                    </h3>
                    <button type="button" @click="tambahBaris()"
                        class="text-[10px] font-black bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2.5 py-1.5 rounded-lg transition">
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(baris, index) in pertemuanList" :key="baris.id">
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl relative shadow-inner">
                            <button type="button" @click="hapusBaris(index)" x-show="pertemuanList.length > 1"
                                class="absolute top-3 right-3 text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-2 py-0.5 rounded border border-red-100">&times;</button>

                            <div class="grid grid-cols-1 gap-3 pt-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tanggal
                                            Kegiatan</label>
                                        <input type="date" :name="`pertemuan[${index}][tanggal_kegiatan]`" required
                                            class="w-full text-xs font-bold border-gray-200 rounded-xl">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Materi
                                            / Bahasan Latihan</label>
                                        <input type="text" :name="`pertemuan[${index}][materi]`" required
                                            placeholder="Contoh: Latihan Fisik Dasar"
                                            class="w-full text-xs font-bold border-gray-200 rounded-xl">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Unggah Foto
                                        (Bisa pilih banyak foto sekaligus)</label>
                                    <input type="file" :name="`pertemuan[${index}][fotos][]`" multiple required
                                        accept="image/*"
                                        class="w-full text-xs border border-gray-200 rounded-xl bg-white p-1 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-700">
                                </div>
                                <div>
                                    <input type="text" :name="`pertemuan[${index}][catatan]`"
                                        placeholder="Catatan opsional (misal: cuaca hujan, absensi...)"
                                        class="w-full text-[11px] font-medium text-gray-500 border-gray-100 rounded-xl bg-white/50">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600 shadow-md">Simpan Foto Latihan</x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- MODAL HAPUS --}}
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
                <h2 class="text-md font-bold text-gray-900">Hapus Seluruh Laporan <span class="text-red-600 uppercase"
                        x-text="selectedEkskul.nama"></span>?</h2>
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