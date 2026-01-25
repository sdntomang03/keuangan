<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen" x-data="{
        showModal: false,
        activeTab: 'surat',
        confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Surat?',
                text: 'Data rincian barang di dalamnya akan ikut terhapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-surat-' + id).submit();
                }
            })
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ALERT SUCCESS --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show"
                class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex justify-between items-center">
                <div class="flex items-center text-emerald-700">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500">&times;</button>
            </div>
            @endif

            {{-- HEADER UTAMA --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-4 bg-indigo-600 rounded-2xl text-white shadow-lg shadow-indigo-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-800 tracking-tight leading-none uppercase">
                                Manajemen SPJ</h2>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">{{ $belanja->uraian }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        {{-- 1. TOMBOL GENERATE AWAL --}}
                        <form action="{{ route('surat.generate', $belanja->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-blue-600 text-white px-5 py-2.5 rounded-2xl hover:bg-blue-700 text-xs font-black shadow-xl shadow-blue-100 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                GENERATE NOMOR
                            </button>
                        </form>

                        {{-- 2. TOMBOL INPUT HARGA PENAWARAN --}}
                        <a href="{{ route('belanja.edit_penawaran', $belanja->id) }}"
                            class="group bg-white border-2 border-emerald-500 text-emerald-600 px-5 py-2.5 rounded-2xl hover:bg-emerald-50 text-xs font-black transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            HARGA PENAWARAN
                        </a>

                        {{-- 3. TOMBOL TAMBAH PARSIAL --}}
                        <button @click="showModal = true"
                            class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl hover:bg-indigo-700 text-xs font-black shadow-xl shadow-indigo-100 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            TAMBAH PARSIAL
                        </button>

                        {{-- 4. TOMBOL CETAK DOKUMEN --}}
                        @if($belanja->surats->count() > 0)
                        <a href="{{ route('belanja.print', $belanja->id) }}" target="_blank"
                            class="bg-gray-800 text-white px-5 py-2.5 rounded-2xl hover:bg-gray-900 text-xs font-black transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            CETAK SPJ
                        </a>
                        @endif
                    </div>
                </div>

                {{-- TAB NAVIGATION --}}
                <div class="flex gap-8 mt-10 border-b border-gray-100">
                    <button @click="activeTab = 'surat'"
                        :class="activeTab === 'surat' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400'"
                        class="pb-4 border-b-4 font-black text-xs uppercase tracking-widest transition-all">Administrasi
                        Surat</button>
                    <button @click="activeTab = 'barang'"
                        :class="activeTab === 'barang' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400'"
                        class="pb-4 border-b-4 font-black text-xs uppercase tracking-widest transition-all">Rincian
                        Barang</button>
                    <button @click="activeTab = 'foto'"
                        :class="activeTab === 'foto' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400'"
                        class="pb-4 border-b-4 font-black text-xs uppercase tracking-widest transition-all">Dokumentasi
                        Fisik</button>
                </div>
            </div>

            {{-- CONTENT SECTION --}}
            <div class="mt-4">

                {{-- TAB 1: SURAT --}}
                <div x-show="activeTab === 'surat'" x-transition:enter="transition ease-out duration-300"
                    class="space-y-4">
                    @foreach($belanja->surats as $surat)
                    {{-- START LOOP SURAT --}}
                    <div x-data="{ showModalItems: false }" class="mb-4">

                        {{-- KARTU SURAT --}}
                        <div
                            class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 transition-shadow hover:shadow-md group flex flex-col md:flex-row justify-between items-center gap-4">

                            {{-- Kiri: Ikon & Info --}}
                            <div class="flex items-center gap-5 w-full md:w-auto">
                                <div class="relative">
                                    <div
                                        class="w-14 h-14 flex items-center justify-center rounded-2xl {{ $surat->is_parsial ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }} font-black text-sm">
                                        {{ $surat->jenis_surat }}
                                    </div>
                                    @if($surat->is_parsial)
                                    <span
                                        class="absolute -top-2 -right-2 bg-purple-600 text-white text-[8px] px-2 py-1 rounded-full font-black border-2 border-white uppercase">
                                        {{ $surat->keterangan }}
                                    </span>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <div class="flex flex-col">
                                        <input type="text" value="{{ $surat->nomor_surat }}" readonly
                                            class="p-0 border-none font-mono text-base font-bold text-gray-800 bg-transparent w-full md:w-80">
                                        <div class="text-xs text-gray-500 font-medium mt-1">
                                            {{ $surat->tanggal_surat->translatedFormat('d F Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kanan: Tombol Aksi --}}
                            {{-- Kanan: Tombol Aksi --}}
                            <div class="flex items-center gap-2">

                                {{-- 1. TOMBOL RINCIAN BARANG --}}
                                <button @click="showModalItems = true" type="button"
                                    class="px-3 py-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-100 transition-colors text-xs font-bold flex items-center gap-2"
                                    title="Lihat Rincian Barang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <span class="hidden md:inline">Rincian</span>
                                </button>

                                {{-- 2. TOMBOL CETAK SP (Hanya Muncul Jika SP) --}}
                                @if($surat->jenis_surat == 'SP')
                                <a href="{{ route('surat.cetak_sp', $surat->id) }}" target="_blank"
                                    class="px-3 py-2 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-100 transition-colors text-xs font-bold flex items-center gap-2"
                                    title="Cetak Surat Pesanan Ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    <span class="hidden md:inline">Cetak</span>
                                </a>
                                @endif

                                {{-- 3. TOMBOL CETAK BAPB (Hanya Muncul Jika BAPB) --}}
                                @if($surat->jenis_surat == 'BAPB')
                                <a href="{{ route('surat.cetak_bapb', $surat->id) }}" target="_blank"
                                    class="px-3 py-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors text-xs font-bold flex items-center gap-2"
                                    title="Cetak BAPB Ini">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="hidden md:inline">Cetak BAPB</span>
                                </a>
                                @endif

                                {{-- Separator Kecil --}}
                                <div class="h-6 w-px bg-gray-200 mx-1"></div>

                                {{-- 3. TOMBOL SIMPAN & HAPUS (Hidden by Default) --}}
                                <div
                                    class="flex items-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200">

                                    {{-- Form Update --}}
                                    <form id="form-{{ $surat->id }}" action="{{ route('surat.update', $surat->id) }}"
                                        method="POST">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                            class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors shadow-sm"
                                            title="Simpan Perubahan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Form Hapus --}}
                                    <form action="{{ route('surat.destroy', $surat->id) }}" method="POST"
                                        id="delete-surat-{{ $surat->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="confirmDelete('{{ $surat->id }}')"
                                            class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition-colors shadow-sm"
                                            title="Hapus Surat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>

                        {{-- MODAL RINCIAN ITEM (Ditaruh di DALAM Loop) --}}
                        <div x-show="showModalItems" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                            <div
                                class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div @click="showModalItems = false"
                                    class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                <div
                                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                                    <div
                                        class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                        <div>
                                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">
                                                Daftar Barang</h3>
                                            <p class="text-[10px] text-gray-400 font-bold">Surat:
                                                {{ $surat->nomor_surat }}</p>
                                        </div>
                                        <button @click="showModalItems = false"
                                            class="text-gray-400 hover:text-gray-600 bg-white p-1 rounded-full shadow-sm hover:shadow-md transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-6 py-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                        @php
                                        $items = $surat->is_parsial ? $surat->rincis : $belanja->rincis;
                                        @endphp
                                        @if($items->count() > 0)
                                        <ul class="space-y-3">
                                            @foreach($items as $item)
                                            <li
                                                class="flex justify-between items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition-colors">
                                                <div class="flex items-start gap-3">
                                                    {{-- Dot Indikator --}}
                                                    <div
                                                        class="mt-1.5 w-2 h-2 rounded-full {{ $surat->is_parsial ? 'bg-purple-500' : 'bg-blue-500' }} flex-shrink-0">
                                                    </div>

                                                    {{-- Nama & Spek --}}
                                                    <div>
                                                        <p class="text-xs font-bold text-gray-700 leading-tight">
                                                            {{ $item->namakomponen ?? $item->nama_komponen }}
                                                        </p>
                                                        @if($item->spek)
                                                        <p class="text-[10px] text-gray-400 mt-0.5 italic">
                                                            {{ $item->spek }}
                                                        </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Badge Volume (YANG DIPERBAIKI) --}}
                                                <span
                                                    class="bg-white border border-gray-200 text-gray-600 text-[10px] font-black px-2 py-1 rounded-lg shadow-sm whitespace-nowrap">
                                                    {{ $surat->is_parsial ? $item->pivot->volume : $item->volume }} {{
                                                    $item->satuan }}
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <div
                                            class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                                            <p class="text-xs font-bold text-gray-400">Tidak ada item terdaftar.</p>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="bg-gray-50 px-6 py-3 flex justify-end">
                                        <button @click="showModalItems = false" type="button"
                                            class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- END LOOP SURAT --}}
                    @endforeach
                </div>

                {{-- TAB 2: BARANG --}}
                <div x-show="activeTab === 'barang'" x-transition style="display: none;">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr class="text-gray-400 uppercase text-[10px] font-black tracking-widest">
                                    <th class="px-8 py-5 text-left">Komponen / Spek</th>
                                    <th class="px-8 py-5 text-center">Volume</th>
                                    <th class="px-8 py-5 text-right">Harga Satuan</th>
                                    <th class="px-8 py-5 text-right">Harga Penawaran</th>
                                    <th class="px-8 py-5 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($belanja->rincis as $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-8 py-5">
                                        <p class="font-bold text-gray-800">{{ $item->namakomponen ??
                                            $item->nama_komponen }}</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5 italic">{{ $item->spek }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span
                                            class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full font-black text-xs">
                                            {{ number_format($item->volume, 0) }} {{ $item->satuan }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right font-medium text-gray-500">Rp {{
                                        number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="px-8 py-5 text-right font-medium text-gray-500">Rp {{
                                        number_format($item->harga_penawaran, 0, ',', '.') }}</td>
                                    <td class="px-8 py-5 text-right font-black text-gray-900">Rp {{
                                        number_format($item->volume * $item->harga_satuan, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 3: FOTO --}}
                <div x-show="activeTab === 'foto'" x-transition x-data="{
        loading: false,
        lat: '',
        lng: '',
        previewUrl: null,
        fileChosen(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.previewUrl = URL.createObjectURL(file);
        },
        getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(p => {
                    this.lat = p.coords.latitude;
                    this.lng = p.coords.longitude;
                });
            }
        }
     }" x-init="getLocation()">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="col-span-1 md:col-span-2">
                            <form action="{{ route('belanja.upload_foto', $belanja->id) }}" method="POST"
                                enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="latitude" x-model="lat">
                                <input type="hidden" name="longitude" x-model="lng">

                                <div class="relative group">
                                    <label
                                        class="bg-white p-8 rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center hover:border-indigo-500 transition-all cursor-pointer min-h-[300px] overflow-hidden">
                                        <template x-if="previewUrl">
                                            <div class="absolute inset-0 z-0">
                                                <img :src="previewUrl" class="w-full h-full object-contain bg-gray-100">
                                            </div>
                                        </template>
                                        <div
                                            class="relative z-10 bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-sm">
                                            <div
                                                class="w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs font-black text-gray-700 uppercase"
                                                x-text="previewUrl ? 'Ganti Foto' : 'Pilih Foto SPJ'"></span>
                                        </div>
                                        <input type="file" name="foto" class="hidden" accept="image/*" capture="camera"
                                            @change="fileChosen">
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pukul Berapa
                                        Foto Diambil?</label>
                                    <input type="time" name="waktu_foto" value="{{ old('waktu_foto', '09:00') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 focus:ring-indigo-500 text-sm font-bold">
                                </div>
                                <template x-if="previewUrl">
                                    <button type="submit" @click="loading = true"
                                        class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest flex items-center justify-center gap-3">
                                        <span x-show="!loading">Upload & Beri Watermark</span>
                                        <span x-show="loading" class="animate-spin text-xl">&#9696;</span>
                                    </button>
                                </template>
                            </form>
                        </div>

                        <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4">
                            @forelse($belanja->fotos as $foto)
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden aspect-square relative group">
                                <img src="{{ asset('storage/' . $foto->path) }}" class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all flex items-start justify-end p-2">
                                    <form action="{{ route('surat.delete_foto', $foto->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus foto ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 text-white p-2 rounded-xl shadow-lg hover:bg-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <div
                                class="col-span-2 py-12 text-center bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Belum ada
                                    dokumentasi fisik</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL PARSIAL (SETUP AWAL) --}}
        {{-- MODAL PARSIAL --}}
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div @click="showModal = false"
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl relative overflow-hidden transition-all transform"
                    x-data="{
                jenisSurat: 'SP',
                selectAll: true,
                tgl_bast: '',
                tgl_bapb: '',

                toggleAll() {
                    const checkboxes = document.querySelectorAll('.item-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.selectAll);
                },
                syncTanggal() {
                    this.tgl_bapb = this.tgl_bast;
                }
             }">

                    <form action="{{ route('surat.store_parsial', $belanja->id) }}" method="POST">
                        @csrf
                        <div class="p-8">
                            <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tight mb-6">Setup Parsial
                            </h3>

                            <div class="space-y-6">
                                {{-- 1. UMUM: NAMA TAHAPAN & JENIS SURAT --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] block mb-2">Nama
                                            Tahapan</label>
                                        <input type="text" name="keterangan" placeholder="Contoh: Pengiriman Tahap I"
                                            required
                                            class="w-full bg-gray-50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] block mb-2">Jenis
                                            Surat</label>
                                        <div class="relative">
                                            <select name="jenis_surat" x-model="jenisSurat"
                                                class="w-full bg-indigo-50 border-none rounded-xl p-3 text-sm font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500 appearance-none cursor-pointer">
                                                <option value="SP">Buat Surat Pesanan (SP)</option>
                                                <option value="BAPB">Buat BAPB & BAST</option>
                                            </select>
                                            <div
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-indigo-600">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. TAMPILAN JIKA MEMILIH SP --}}
                                <div x-show="jenisSurat === 'SP'" x-transition>
                                    <div class="p-5 bg-blue-50/50 rounded-3xl border border-blue-100">
                                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-3">
                                            Detail Surat Pesanan</p>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-[10px] font-bold text-gray-400 mb-1 block">Nomor
                                                    SP</label>
                                                <input type="text" name="nomor_sp" placeholder="Nomor SP"
                                                    :required="jenisSurat === 'SP'"
                                                    class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-blue-300">
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-gray-400 mb-1 block">Tanggal
                                                    SP</label>
                                                <input type="date" name="tanggal_sp" :required="jenisSurat === 'SP'"
                                                    class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-blue-300">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. TAMPILAN JIKA MEMILIH BAPB --}}
                                <div x-show="jenisSurat === 'BAPB'" x-transition style="display: none;">
                                    <div class="space-y-4">

                                        <div
                                            class="p-5 bg-orange-50/40 rounded-3xl border border-orange-100 relative overflow-hidden">
                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-200"></div>
                                            <p
                                                class="text-[10px] font-black text-orange-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                <span>Referensi BAST / Surat Jalan</span>
                                                <span
                                                    class="bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded text-[8px]">Wajib</span>
                                            </p>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-[10px] font-bold text-gray-400 mb-1 block">Nomor
                                                        BAST/SJ</label>
                                                    <input type="text" name="no_bast" placeholder="No. BAST dari Toko"
                                                        class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-orange-300">
                                                </div>
                                                <div>
                                                    <label
                                                        class="text-[10px] font-bold text-gray-400 mb-1 block">Tanggal
                                                        BAST</label>
                                                    <input type="date" name="tanggal_bast" x-model="tgl_bast"
                                                        @input="syncTanggal()"
                                                        class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-orange-300">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- B. DETAIL BAPB --}}
                                        <div class="p-5 bg-emerald-50/50 rounded-3xl border border-emerald-100">
                                            <p
                                                class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-3">
                                                Detail Berita Acara (BAPB)</p>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-[10px] font-bold text-gray-400 mb-1 block">Nomor
                                                        BAPB</label>
                                                    <input type="text" name="nomor_bapb" placeholder="Nomor BAPB"
                                                        :required="jenisSurat === 'BAPB'"
                                                        class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-emerald-300">
                                                </div>
                                                <div>
                                                    <label
                                                        class="text-[10px] font-bold text-gray-400 mb-1 block">Tanggal
                                                        BAPB</label>
                                                    <input type="date" name="tanggal_bapb" x-model="tgl_bapb"
                                                        :required="jenisSurat === 'BAPB'"
                                                        class="w-full text-xs border-none rounded-xl p-3 font-bold focus:ring-1 focus:ring-emerald-300 bg-emerald-100/50">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- C. DETAIL BAST --}}

                                    </div>
                                </div>

                                {{-- 4. PILIH BARANG (TETAP SAMA) --}}
                                <div class="bg-gray-50 rounded-3xl p-5">
                                    <div class="flex justify-between items-center mb-4">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih
                                            Barang & Volume</p>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                class="rounded text-indigo-600 focus:ring-0 w-4 h-4">
                                            <span class="text-[10px] font-bold text-gray-500">Pilih Semua</span>
                                        </label>
                                    </div>
                                    <div class="max-h-52 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                        @foreach($belanja->rincis as $item)
                                        <div
                                            class="flex items-center justify-between p-3 bg-white rounded-2xl border border-gray-100 hover:border-indigo-200 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1"
                                                    checked
                                                    class="item-checkbox rounded text-indigo-600 focus:ring-indigo-500">
                                                <div class="text-xs">
                                                    <p class="font-bold text-gray-700 leading-none">{{
                                                        $item->namakomponen }}</p>
                                                    <p class="text-[9px] text-gray-400 mt-1">Total: {{
                                                        number_format($item->volume, 0) }} {{ $item->satuan }}</p>
                                                </div>
                                            </div>
                                            <input type="number" name="items[{{ $item->id }}][volume]"
                                                value="{{ $item->volume }}" max="{{ $item->volume }}"
                                                class="w-16 bg-gray-50 border-none rounded-lg p-1 text-xs text-right font-black text-indigo-600 focus:ring-1 focus:ring-indigo-400">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gray-50 flex justify-end gap-3">
                                <button @click="showModal = false" type="button"
                                    class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">Batal</button>
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-8 py-3 rounded-2xl text-xs font-black shadow-lg shadow-indigo-100 uppercase tracking-widest hover:bg-indigo-700 transition-all">Simpan
                                    Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>