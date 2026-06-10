<x-app-layout>
    {{-- SCOPE ALPINE JS (Membungkus seluruh halaman, tabel, dan modal) --}}
    <div x-data="{
        // State untuk input dinamis multiple pertemuan di modal
        pertemuanList: [{ id: Date.now(), tanggal: '', materi: '', catatan: '' }],

        tambahBaris() {
            this.pertemuanList.push({ id: Date.now(), tanggal: '', materi: '', catatan: '' });
        },
        hapusBaris(index) {
            if(this.pertemuanList.length > 1) {
                this.pertemuanList.splice(index, 1);
            }
        },

        // State untuk konfirmasi hapus ekskul
        selectedEkskul: { id: '', nama: '' }
    }">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight italic uppercase tracking-tight">
                        {{ __('Laporan Kegiatan Ekskul') }}
                    </h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">
                        Pencatatan materi latihan dan dokumentasi foto pelatih per sekolah
                    </p>
                </div>

                <button x-on:click="$dispatch('open-modal', 'add-ekskul-laporan-modal')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition shadow-md flex items-center border border-indigo-500 active:scale-95 duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Laporan Baru
                </button>
            </div>
        </x-slot>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                {{-- ALERT MESSAGES --}}
                @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-bold rounded-r-lg shadow-sm flex justify-between items-center"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center">
                        <span class="mr-2">✅</span> {{ session('success') }}
                    </div>
                    <button @click="show = false"
                        class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div
                    class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-lg shadow-sm">
                    <div class="font-bold mb-1">⚠️ Terjadi Kesalahan Validasi:</div>
                    <ul class="list-disc pl-5 text-xs font-semibold space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- MAIN DATA TABLE --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                                <th class="p-4 w-1/4">Nama Ekskul & Periode</th>
                                <th class="p-4 w-1/4">Pelatih / Asal Sekolah</th>
                                <th class="p-4 w-1/4 text-center">Jumlah Pertemuan</th>
                                <th class="p-4 w-1/4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ekskuls as $ekskul)
                            {{-- Baris Induk --}}
                            <tr class="bg-white hover:bg-indigo-50/20 transition duration-150 font-semibold"
                                x-data="{ expanded: false }">
                                <td class="p-4 text-sm text-gray-800">
                                    <div class="flex items-center gap-2">
                                        <button @click="expanded = !expanded"
                                            class="text-gray-400 hover:text-indigo-600 transition p-1 rounded-lg hover:bg-gray-100">
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div>
                                            <span class="font-bold text-gray-900">{{ $ekskul->nama_ekskul }}</span>
                                            <div class="text-[11px] text-gray-400 font-medium">{{ $ekskul->periode ??
                                                'Semua Periode' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-gray-700">
                                    <div class="text-gray-800 font-bold">{{ $ekskul->user->name ?? 'Pelatih Luar' }}
                                    </div>
                                    <div class="text-[11px] text-indigo-500 font-medium tracking-wide">{{
                                        $ekskul->sekolah->nama_sekolah ?? 'Pusat' }}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <span
                                        class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-xl text-xs font-black">
                                        {{ $ekskul->laporans->count() }} Kali Latihan
                                    </span>
                                </td>
                                <td class="p-4 text-right flex justify-end items-center gap-2">
                                    {{-- Tombol Toggle Detil --}}
                                    <button @click="expanded = !expanded"
                                        :class="expanded ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600'"
                                        class="px-3 py-1.5 rounded-lg text-xs transition shadow-sm font-bold">
                                        Laporan Rinci
                                    </button>
                                    {{-- Tombol Hapus Ekskul --}}
                                    <button @click="
                                        selectedEkskul = { id: '{{ $ekskul->id }}', nama: '{{ addslashes($ekskul->nama_ekskul) }}' };
                                        $dispatch('open-modal', 'confirm-ekskul-deletion-modal');"
                                        class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm"
                                        title="Hapus Laporan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- Sub-Tabel/Grid Expand Gambar Pertemuan --}}
                            <tr x-show="expanded" x-transition.opacity style="display: none;" class="bg-gray-50/50">
                                <td colspan="4" class="p-6 border-t border-b border-gray-100">
                                    <div class="mb-2 text-xs font-black text-gray-400 uppercase tracking-widest">Galeri
                                        Dokumentasi Pertemuan:</div>

                                    @if($ekskul->laporans->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        @foreach($ekskul->laporans as $laporan)
                                        <div
                                            class="bg-white p-3 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                                            <div>
                                                <div
                                                    class="relative rounded-xl overflow-hidden mb-2 group shadow-inner bg-gray-100">
                                                    <img src="{{ asset('storage/' . $laporan->path_gambar) }}"
                                                        alt="Bukti Latihan" class="w-full h-32 object-cover">
                                                    <div
                                                        class="absolute top-2 left-2 bg-gray-900/70 backdrop-blur-sm text-white font-mono text-[9px] px-2 py-0.5 rounded font-bold">
                                                        {{
                                                        \Carbon\Carbon::parse($laporan->tanggal_kegiatan)->translatedFormat('d
                                                        M Y') }}
                                                    </div>
                                                </div>
                                                <div class="text-xs font-black text-gray-800 leading-tight">{{
                                                    $laporan->materi }}</div>
                                                @if($laporan->catatan)
                                                <p
                                                    class="text-[11px] text-gray-400 mt-1 font-normal italic leading-relaxed">
                                                    "{{ $laporan->catatan }}"</p>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="text-center py-4 text-xs font-bold text-gray-400 italic">Belum ada
                                        rincian foto pertemuan yang diunggah pelatih.</div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-sm font-bold text-gray-400 italic">
                                    Belum ada rekaman laporan ekskul yang tersimpan untuk instansi/akun Anda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if(method_exists($ekskuls, 'links'))
                    <div class="p-4 border-t bg-white">
                        {{ $ekskuls->links() }}
                    </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL 1: FORM INPUT DINAMIS LAPORAN BARU --}}
        {{-- ========================================================================= --}}
        <x-modal name="add-ekskul-laporan-modal" max-width="4xl" focusable>
            <form action="{{ route('ekskul.laporan.store') }}" method="POST" enctype="multipart/form-data"
                class="p-8 text-left">
                @csrf
                <div class="border-b pb-4 mb-6">
                    <h2 class="text-xl font-extrabold text-gray-900">Form Laporan & Dokumentasi Ekskul</h2>
                    <p class="text-xs text-indigo-500 font-bold mt-0.5 uppercase tracking-wider">Identitas Induk &
                        Unggah Multi-Pertemuan</p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6 bg-indigo-50/30 p-5 rounded-2xl border border-indigo-100/60">
                    <div>
                        <x-input-label for="nama_ekskul" :value="__('Nama Cabang Ekskul')"
                            class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                        <x-text-input id="nama_ekskul" name="nama_ekskul" type="text"
                            class="mt-1 block w-full rounded-xl border-gray-200 py-3 text-sm font-bold"
                            placeholder="Contoh: Futsal Putra, Pramuka Inti" required />
                    </div>
                    <div>
                        <x-input-label for="periode" :value="__('Periode Laporan / Keterangan Waktu')"
                            class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                        <x-text-input id="periode" name="periode" type="text"
                            class="mt-1 block w-full rounded-xl border-gray-200 py-3 text-sm font-bold"
                            placeholder="Contoh: Triwulan I (Jan-Mar)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="keterangan" :value="__('Catatan Tambahan Kelompok Ekskul (Opsional)')"
                            class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                        <textarea id="keterangan" name="keterangan" rows="2"
                            class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm font-semibold"
                            placeholder="Keterangan singkat jadwal latihan atau target kompetisi..."></textarea>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest">Detail Pertemuan & Foto
                        Bukti</h3>
                    <button type="button" @click="tambahBaris()"
                        class="text-[10px] font-black bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg transition active:scale-95 duration-100">
                        + Tambah Baris Pertemuan
                    </button>
                </div>

                <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scroll mb-6">
                    <template x-for="(baris, index) in pertemuanList" :key="baris.id">
                        <div
                            class="p-5 bg-gray-50 border border-gray-200 rounded-2xl relative shadow-sm transition-all">

                            {{-- Tombol Hapus Baris --}}
                            <button type="button" @click="hapusBaris(index)" x-show="pertemuanList.length > 1"
                                class="absolute top-4 right-4 bg-red-50 text-red-600 text-[10px] font-black px-2 py-1 rounded-md hover:bg-red-100 transition">
                                &times; Hapus Baris
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start pt-2">
                                <div class="md:col-span-3">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Tanggal
                                        Kegiatan</label>
                                    <input type="date" :name="`pertemuan[${index}][tanggal_kegiatan]`" required
                                        class="w-full text-xs font-bold border-gray-200 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <div class="md:col-span-5">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Materi /
                                        Bahasan Latihan</label>
                                    <input type="text" :name="`pertemuan[${index}][materi]`" required
                                        placeholder="Contoh: Teknik Passing Pendek"
                                        class="w-full text-xs font-bold border-gray-200 rounded-xl focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Unggah Foto
                                        Bukti (Max 5MB)</label>
                                    <input type="file" :name="`pertemuan[${index}][foto]`" required accept="image/*"
                                        class="w-full text-xs border border-gray-200 rounded-xl file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 bg-white p-1">
                                </div>

                                <div class="md:col-span-12">
                                    <input type="text" :name="`pertemuan[${index}][catatan]`"
                                        placeholder="Catatan tambahan pelatih, absensi, atau kendala lapangan (Opsional)"
                                        class="w-full text-[11px] font-medium text-gray-500 border-gray-100 rounded-xl focus:ring-1 focus:ring-indigo-500 bg-white/70">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="bg-indigo-600 shadow-md shadow-indigo-100">Simpan Semua Laporan
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- ========================================================================= --}}
        {{-- MODAL 2: KONFIRMASI HAPUS EKskul --}}
        {{-- ========================================================================= --}}
        <x-modal name="confirm-ekskul-deletion-modal" focusable>
            <form :action="'{{ url('ekskul-laporan') }}/' + selectedEkskul.id" method="post" class="p-8 text-center">
                @csrf
                @method('delete')

                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900">
                    Hapus Laporan <span class="text-red-600 uppercase" x-text="selectedEkskul.nama"></span>?
                </h2>
                <p class="mt-2 text-xs text-gray-400 font-semibold max-w-sm mx-auto leading-relaxed">
                    Tindakan ini akan menghapus seluruh data induk beserta seluruh file fisik foto bukti pertemuan yang
                    ada di storage secara permanen.
                </p>

                <div class="mt-8 flex justify-center gap-4">
                    <button type="button" x-on:click="$dispatch('close')"
                        class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase tracking-widest hover:bg-gray-100 rounded-xl transition">Batal</button>
                    <button type="submit"
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-red-200 transition active:scale-95">Ya,
                        Hapus Semua</button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>