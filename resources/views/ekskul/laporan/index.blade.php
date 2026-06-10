<x-app-layout>
    <div x-data="{
        pertemuanList: [{ id: Date.now() }],
        tambahBaris() { this.pertemuanList.push({ id: Date.now() }); },
        hapusBaris(index) { if(this.pertemuanList.length > 1) this.pertemuanList.splice(index, 1); },
        selectedEkskul: { id: '', nama: '' }
    }">

        <x-slot name="header">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-black text-xl text-gray-800 leading-tight italic uppercase tracking-tight">
                        {{ __('Laporan Kegiatan Ekskul') }}
                    </h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Pencatatan materi latihan dan multi-upload foto
                        dokumentasi</p>
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
        @dd($ekskuls)
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
                                    <th class="p-4">Nama Ekskul & Periode</th>
                                    <th class="p-4">Pelatih / Sekolah</th>
                                    <th class="p-4 text-center">Jumlah Kegiatan</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($ekskuls as $ekskul)
                                <tr class="hover:bg-indigo-50/10 transition duration-150 font-semibold"
                                    x-data="{ expanded: false }">
                                    <td class="p-4 text-sm text-gray-800">
                                        <div class="flex items-center gap-2">
                                            <button @click="expanded = !expanded"
                                                class="text-gray-400 hover:text-indigo-600 p-1 rounded-lg hover:bg-gray-100 transition">
                                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                                    :class="expanded ? 'rotate-180' : ''" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div>
                                                <span class="font-bold text-gray-900 block">{{ $ekskul->nama_ekskul
                                                    }}</span>
                                                <span class="text-[11px] text-gray-400 font-medium block mt-0.5">{{
                                                    $ekskul->periode ?? 'Semua Periode' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-gray-700">
                                        <div class="text-gray-800 font-bold leading-tight">{{ $ekskul->user->name ??
                                            'Pelatih Luar' }}</div>
                                        <div class="text-[11px] text-indigo-500 font-medium tracking-wide mt-0.5">{{
                                            $ekskul->sekolah->nama_sekolah ?? 'Pusat' }}</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span
                                            class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-xl text-xs font-black">
                                            {{ $ekskul->laporans->count() }} Pertemuan
                                        </span>
                                    </td>
                                    <td class="p-4 text-right flex justify-end items-center gap-2">
                                        <button @click="expanded = !expanded"
                                            :class="expanded ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600'"
                                            class="px-3 py-1.5 rounded-lg text-xs transition font-bold shadow-sm">
                                            Rincian
                                        </button>
                                        <button @click="
                                            selectedEkskul = { id: '{{ $ekskul->id }}', nama: '{{ addslashes($ekskul->nama_ekskul) }}' };
                                            $dispatch('open-modal', 'confirm-ekskul-deletion-modal');"
                                            class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                {{-- EXPANDABLE GRID (RESPONSIF UNTUK GALERI) --}}
                                <tr x-show="expanded" x-transition.opacity style="display: none;" class="bg-gray-50/40">
                                    <td colspan="4" class="p-4 sm:p-6 border-t border-b border-gray-100">
                                        <div class="space-y-6">
                                            @forelse($ekskul->laporans as $laporan)
                                            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-sm">
                                                <div
                                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b pb-2 mb-3">
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

                                                {{-- GRID FOTO RESPONSIVE --}}
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5
                                                    gap-3">
                                                    @foreach($laporan->fotos as $foto)
                                                    <div
                                                        class="relative rounded-xl overflow-hidden shadow-sm border border-gray-100 group bg-gray-50">
                                                        <img src="{{ asset('storage/' . $foto->path_foto) }}"
                                                            alt="Bukti" class="w-full h-24 sm:h-32 object-cover">
                                                        <a href="{{ asset('storage/' . $foto->path_foto) }}"
                                                            target="_blank"
                                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-bold">
                                                            Lihat Penuh ↗
                                                        </a>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @empty
                                            <div class="text-center py-2 text-xs font-bold text-gray-400 italic">Belum
                                                ada rincian pertemuan.</div>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-sm font-bold text-gray-400 italic">Belum
                                        ada rekaman laporan kegiatan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- MODAL INPUT DATA: AMAN DARI ERROR KARENA MENGGUNAKAN DEFAULT 2XL DENGAN INTERIOR LAYOUT RESPONSIVE --}}
        <x-modal name="add-ekskul-laporan-modal" max-width="2xl" focusable>
            <form action="{{ route('ekskul.laporan.store') }}" method="POST" enctype="multipart/form-data"
                class="p-5 sm:p-8 text-left max-h-[90vh] overflow-y-auto custom-scroll">
                @csrf
                <div class="border-b pb-3 mb-5">
                    <h2 class="text-lg font-extrabold text-gray-900">Form Laporan Ekskul Baru</h2>
                    <p class="text-[10px] text-indigo-500 font-black uppercase tracking-wider mt-0.5">Mendukung Banyak
                        Foto Per Pertemuan</p>
                </div>

                {{-- RESPONSIVE FORM GRID --}}
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 bg-indigo-50/40 p-4 rounded-xl border border-indigo-100">
                    <div>
                        <x-input-label for="nama_ekskul" value="Nama Cabang Ekskul"
                            class="text-[10px] font-bold uppercase text-gray-400" />
                        <x-text-input id="nama_ekskul" name="nama_ekskul" type="text"
                            class="mt-1 block w-full rounded-xl border-gray-200 py-2.5 text-xs font-bold"
                            placeholder="Contoh: Futsal, Tari Tradisional" required />
                    </div>
                    <div>
                        <x-input-label for="periode" value="Periode Waktu Laporan"
                            class="text-[10px] font-bold uppercase text-gray-400" />
                        <x-text-input id="periode" name="periode" type="text"
                            class="mt-1 block w-full rounded-xl border-gray-200 py-2.5 text-xs font-bold"
                            placeholder="Contoh: Bulan Januari 2026" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="keterangan" value="Keterangan Tambahan (Opsional)"
                            class="text-[10px] font-bold uppercase text-gray-400" />
                        <textarea id="keterangan" name="keterangan" rows="2"
                            class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-xs font-semibold"
                            placeholder="Tuliskan catatan tambahan jadwal latihan..."></textarea>
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

                {{-- DINAMIS CONTAINER BARIS --}}
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
                                            placeholder="Contoh: Latihan Dasar Fisik"
                                            class="w-full text-xs font-bold border-gray-200 rounded-xl">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Pilih
                                        Gambar Dokumentasi (Bisa pilih lebih dari 1 foto)</label>
                                    {{-- KUNCI UTAMA MULTIPLE UPLOAD BERBAU ARRAY: name="...[fotos][]" multiple --}}
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