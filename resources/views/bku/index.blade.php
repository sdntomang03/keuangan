<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    {{-- Container Utama dengan x-data --}}
    <div class="py-12 bg-gray-50" x-data="{
        // State untuk Modal Tambah (jika ada)
        openModal: false,

        // State untuk Modal Edit
        openEditModal: false,
        editData: { id: '', tanggal: '', no_bukti: '', uraian: '', nominal: '' },

        // State untuk Modal DETAIL (Preview Emerald)
        open: false,
        loading: false,
        data: null,

        // FUNGSI COPY TEXT (Support HTTP & HTTPS)
        copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                // Fallback untuk HTTP biasa (seperti keuangan.test)
                let textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                return new Promise((resolve, reject) => {
                    document.execCommand('copy') ? resolve() : reject();
                    textArea.remove();
                });
            }
        },

        // Fungsi Fetch untuk EDIT (Modal Biru)
        async fetchEdit(id) {
            try {
                const response = await axios.get(`${window.location.origin}/penerimaan/${id}/edit`);
                this.editData = response.data;
                this.openEditModal = true;
            } catch (error) {
                console.error(error);
                alert('Gagal mengambil data transaksi.');
            }
        },

        // Fungsi Fetch untuk DETAIL (Modal Emerald)
        fetchDetail(id) {
            this.loading = true;
            this.open = true;
            this.data = null;
            axios.get(`/belanja/${id}/json`)
                .then(res => {
                    this.data = res.data;
                    this.loading = false;
                })
                .catch(err => {
                    this.open = false;
                    alert('Gagal memuat detail');
                });
        }
    }" {{-- Listener agar tombol $dispatch bisa memicu fungsi detail --}}
        @open-modal-detail.window="fetchDetail($event.detail.id)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Alert Success Sederhana --}}
                @if (session('success'))
                <div class="mb-4 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl shadow-sm flex items-center justify-between"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-bold uppercase tracking-wide">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l18 18"></path>
                        </svg>
                    </button>
                </div>
                @endif

                {{-- Alert Error Sederhana --}}
                @if (session('error') || $errors->any())
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-bold uppercase tracking-wide">
                                {{ session('error') ?? 'Terjadi kesalahan pada input data.' }}
                            </span>
                        </div>
                        <button @click="show = false" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l18 18"></path>
                            </svg>
                        </button>
                    </div>
                    @if($errors->any())
                    <ul class="mt-2 ml-7 list-disc list-inside text-xs font-semibold uppercase tracking-tighter">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endif
                {{-- Header --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">Buku Kas Umum</h2>
                        <p class="text-sm text-gray-500">
                            Anggaran: <span class="font-bold text-indigo-600">{{ $anggaran->nama_anggaran }}</span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="GET" action="{{ route('bku.index') }}" class="w-full sm:w-auto">
                            <select name="tw"
                                class="w-full sm:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-sm font-medium text-gray-700 py-2.5"
                                onchange="this.form.submit()">

                                {{-- Value kosong untuk Semua Triwulan --}}
                                <option value="" {{ $filterTw=='' ? 'selected' : '' }}>-- Semua Triwulan --</option>

                                {{-- Opsi Triwulan --}}
                                <option value="1" {{ $filterTw=='1' ? 'selected' : '' }}>Triwulan 1</option>
                                <option value="2" {{ $filterTw=='2' ? 'selected' : '' }}>Triwulan 2</option>
                                <option value="3" {{ $filterTw=='3' ? 'selected' : '' }}>Triwulan 3</option>
                                <option value="4" {{ $filterTw=='4' ? 'selected' : '' }}>Triwulan 4</option>
                            </select>
                        </form>

                        {{-- Tombol Tambah & Setor (Tidak berubah) --}}
                        <button @click="openModal = true"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Tambah Penerimaan
                        </button>
                        <a href="{{ route('pajak.siap-setor') }}"
                            class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Setor Pajak
                        </a>
                    </div>
                </div>

                {{-- Tabel BKU --}}
                <div class="bg-white border border-gray-200 shadow-sm rounded-3xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest font-black text-gray-500">
                                <tr>
                                    <th class="px-6 py-4 text-center w-16">No</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">No. Bukti</th>
                                    <th class="px-6 py-4">Uraian</th>
                                    <th class="px-6 py-4 text-right">Masuk</th>
                                    <th class="px-6 py-4 text-right">Keluar</th>
                                    <th class="px-6 py-4 text-right bg-blue-50/50">Saldo</th>
                                    <th class="px-6 py-4 text-center bg-blue-50/50">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">

                                @forelse($bkus->reverse() as $item)
                                <tr
                                    class="hover:bg-gray-50 transition-colors {{ $item->kredit > 0 ? 'bg-red-50/30' : '' }} {{ $item->debit > 0 ? 'bg-green-50/30' : '' }}">
                                    <td class="px-6 py-4 text-center font-mono text-gray-400 italic">
                                        {{ str_pad($item->no_urut, 3, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap italic">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-blue-600 uppercase text-[11px] italic">
                                        {{ $item->no_bukti }}
                                    </td>

                                    {{-- ... (Isi Kolom Uraian dan BKU lainnya tetap sama seperti sebelumnya) ... --}}

                                    <td class="px-6 py-4 text-right text-emerald-600 font-bold italic">
                                        {{ $item->debit > 0 ? number_format($item->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-orange-600 font-bold italic">
                                        {{ $item->kredit > 0 ? number_format($item->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900 bg-gray-50/50 text-sm">
                                        {{ number_format($item->saldo_akhir ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center bg-gray-50/50">
                                        {{-- Tombol Aksi BKU --}}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-400 italic">
                                        @if(isset($saldoAwal) && $saldoAwal > 0)
                                        Belum ada transaksi di triwulan ini.
                                        @else
                                        Belum ada data transaksi.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse

                                {{-- ========================================================== --}}
                                {{-- BARIS SALDO AWAL (Dipindah ke Paling Bawah) --}}
                                {{-- ========================================================== --}}
                                @if(request('tw') && request('tw') > 1 && isset($saldoAwal))
                                <tr class="bg-gray-100/80 font-bold text-gray-600 border-t-2 border-gray-300">
                                    <td class="px-6 py-4 text-center">-</td>
                                    <td class="px-6 py-4 text-center">-</td>
                                    <td class="px-6 py-4 text-center">-</td>
                                    <td class="px-6 py-4 uppercase italic tracking-wide">
                                        Saldo S.D. Triwulan {{ request('tw') - 1 }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-400">-</td>
                                    <td class="px-6 py-4 text-right text-gray-400">-</td>
                                    <td class="px-6 py-4 text-right font-black text-gray-800 bg-blue-50/50">
                                        {{ number_format($saldoAwal, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 bg-blue-50/50"></td>
                                </tr>
                                @endif
                                {{-- ========================================================== --}}

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- MODAL TAMBAH --}}
            <div x-show="openModal" x-cloak class="fixed inset-0 z-[70] overflow-y-auto"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" @click="openModal = false">
                    </div>
                    <div
                        class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 overflow-hidden transform transition-all">
                        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight mb-6">Tambah Penerimaan
                        </h3>
                        <form action="{{ route('penerimaan.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-gray-400 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" required
                                        class="w-full border-gray-200 rounded-xl focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Nomor
                                        Bukti</label>
                                    <input type="text" name="no_bukti" required
                                        class="w-full border-gray-200 rounded-xl focus:ring-emerald-500 uppercase">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-gray-400 mb-1">Uraian</label>
                                    <textarea name="uraian" rows="2" required
                                        class="w-full border-gray-200 rounded-xl focus:ring-emerald-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Nominal
                                        (Rp)</label>
                                    <input type="number" name="nominal" required
                                        class="w-full border-gray-200 rounded-xl focus:ring-emerald-500 font-bold text-emerald-600 text-lg">
                                </div>
                            </div>
                            <div class="mt-8 flex gap-3">
                                <button type="button" @click="openModal = false"
                                    class="flex-1 px-4 py-3 border border-gray-200 text-gray-500 text-xs font-black uppercase rounded-xl hover:bg-gray-50 transition">Batal</button>
                                <button type="submit"
                                    class="flex-1 px-4 py-3 bg-emerald-600 text-white text-xs font-black uppercase rounded-xl hover:bg-emerald-700 shadow-lg transition">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- MODAL EDIT (Diletakkan sebelum penutup div x-data) --}}
            <template x-teleport="body">
                <div x-show="openEditModal" x-cloak class="fixed inset-0 z-[80] overflow-y-auto"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity"
                            @click="openEditModal = false"></div>
                        <div
                            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 overflow-hidden transform transition-all border border-gray-100">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-black text-blue-600 uppercase tracking-tight">Edit Penerimaan
                                </h3>
                                <button @click="openEditModal = false"
                                    class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                            </div>
                            <form :action="`${window.location.origin}/penerimaan/${editData.id}`" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase text-gray-400 mb-1">Tanggal</label>
                                        <input type="date" name="tanggal" x-model="editData.tanggal" required
                                            class="w-full border-gray-200 rounded-xl focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Nomor
                                            Bukti</label>
                                        <input type="text" name="no_bukti" x-model="editData.no_bukti" required
                                            class="w-full border-gray-200 rounded-xl focus:ring-blue-500 uppercase">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase text-gray-400 mb-1">Uraian</label>
                                        <textarea name="uraian" rows="2" x-model="editData.uraian" required
                                            class="w-full border-gray-200 rounded-xl focus:ring-blue-500"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Nominal
                                            (Rp)</label>
                                        <input type="number" name="nominal" x-model="editData.nominal" required
                                            class="w-full border-gray-200 rounded-xl focus:ring-blue-500 font-bold text-blue-600 text-lg">
                                    </div>
                                </div>
                                <div class="mt-8 flex gap-3">
                                    <button type="button" @click="openEditModal = false"
                                        class="flex-1 px-4 py-3 border border-gray-200 text-gray-500 text-xs font-black uppercase rounded-xl hover:bg-gray-50 transition">Batal</button>
                                    <button type="submit"
                                        class="flex-1 px-4 py-3 bg-blue-600 text-white text-xs font-black uppercase rounded-xl hover:bg-blue-700 shadow-lg transition">Simpan
                                        Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            {{-- MODAL PREVIEW DETAIL --}}
            <template x-teleport="body">
                <template x-if="open">
                    <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto"
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                        <div class="flex items-center justify-center min-h-screen px-4 py-6">
                            {{-- Backdrop --}}
                            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                                @click="open = false"></div>

                            {{-- Modal Content --}}
                            <div
                                class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden transform transition-all border border-gray-100">

                                {{-- Accent Line --}}
                                <div class="h-1.5 w-full bg-emerald-500"></div>

                                <div class="p-6">
                                    {{-- Header --}}
                                    <div class="flex justify-between items-start mb-5">
                                        <div>
                                            <h3 class="text-xl font-black text-gray-800 leading-none">Rincian Transaksi
                                            </h3>
                                            <p
                                                class="text-[10px] text-emerald-600 font-bold uppercase tracking-[0.2em] mt-1.5">
                                                Detail Belanja BKU
                                            </p>
                                        </div>
                                        <button @click="open = false"
                                            class="bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Loading State --}}
                                    <div x-show="loading" class="py-12 text-center">
                                        <div
                                            class="inline-block animate-spin rounded-full h-8 w-8 border-[3px] border-emerald-500 border-t-transparent mb-3">
                                        </div>
                                        <p
                                            class="text-xs font-bold text-gray-400 uppercase tracking-widest animate-pulse">
                                            Menarik data...</p>
                                    </div>

                                    {{-- Data Content --}}
                                    <template x-if="!loading && data">
                                        <div class="space-y-4">

                                            {{-- 1. Informasi Utama & Rekanan --}}
                                            <div class="bg-gray-50/80 rounded-xl border border-gray-100 p-4">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                                    {{-- Bukti Transfer --}}
                                                    <div class="md:col-span-2 space-y-0.5" x-data="{ copied: false }">
                                                        <label
                                                            class="text-[9px] font-black uppercase text-gray-400 tracking-wider">Bukti
                                                            Transfer</label>
                                                        <div class="flex items-center gap-2 group cursor-pointer w-fit"
                                                            @click="copyText(data.belanja.no_bukti).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                                            title="Klik untuk menyalin">
                                                            <p class="text-sm font-bold text-gray-800 group-hover:text-emerald-600 transition-colors"
                                                                x-text="data.belanja.no_bukti"></p>
                                                            <div
                                                                class="flex items-center text-gray-400 group-hover:text-emerald-500 transition-colors">
                                                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                                    </path>
                                                                </svg>
                                                                <div x-show="copied" x-cloak
                                                                    class="flex items-center gap-1 text-emerald-500">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                    <span
                                                                        class="text-[9px] font-black uppercase tracking-wider">Tersalin</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Uraian (Full Width) --}}
                                                    <div class="md:col-span-2 space-y-0.5" x-data="{ copied: false }">
                                                        <label
                                                            class="text-[9px] font-black uppercase text-gray-400 tracking-wider">Uraian
                                                            Transaksi</label>
                                                        <div class="flex items-start gap-2 group cursor-pointer w-fit"
                                                            @click="copyText('Dibayar ' + data.belanja.uraian + ' kepada ' + (data.belanja.rekanan?.nama_rekanan || 'Pihak Ketiga') + ' dari ' + data.sekolah.nama_sekolah).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                                            title="Klik untuk menyalin">
                                                            <p class="text-sm font-bold text-gray-800 leading-snug group-hover:text-emerald-600 transition-colors"
                                                                x-text="'Dibayar ' + data.belanja.uraian + ' kepada ' + (data.belanja.rekanan?.nama_rekanan || 'Pihak Ketiga') + ' dari ' + data.sekolah.nama_sekolah">
                                                            </p>
                                                            <div
                                                                class="mt-0.5 flex-shrink-0 flex items-center text-gray-400 group-hover:text-emerald-500 transition-colors">
                                                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                                    </path>
                                                                </svg>
                                                                <div x-show="copied" x-cloak
                                                                    class="flex items-center gap-1 text-emerald-500">
                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                    <span
                                                                        class="text-[9px] font-black uppercase tracking-wider">Tersalin</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="h-px bg-gray-200 md:col-span-2"></div>

                                                    {{-- Info Program & Rekening --}}
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label
                                                                class="block text-[8px] font-black uppercase text-emerald-500 mb-0.5">Program
                                                                / Kegiatan</label>
                                                            <p class="text-xs font-bold text-gray-600 uppercase italic leading-tight"
                                                                x-text="data.belanja.kegiatan.namagiat || '-'"></p>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-[8px] font-black uppercase text-emerald-500 mb-0.5">
                                                                Sub Kegiatan
                                                            </label>
                                                            <p class="text-xs font-bold text-gray-600 uppercase italic leading-tight"
                                                                x-html="(data.belanja.rkas.namasub || '-')
               .split(/Terverifikasi/i)[0]
               .replace(/&nbsp;/g, ' ')
               .replace(/<[^>]+>/g, '')
               .trim()">
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-[9px] font-black uppercase text-blue-500 mb-0.5">Kode
                                                                Rekening</label>
                                                            <p class="text-xs font-mono font-bold text-gray-700"
                                                                x-text="data.belanja.korek.ket || '-'"></p>
                                                        </div>
                                                    </div>

                                                    {{-- Info Rekanan & Bank --}}
                                                    <div
                                                        class="bg-white p-3 rounded-lg border border-gray-100 space-y-2">
                                                        <div>
                                                            <label
                                                                class="block text-[9px] font-black uppercase text-gray-400 mb-0.5">Penerima
                                                                / Rekanan</label>
                                                            <p class="font-black text-gray-800 uppercase text-xs"
                                                                x-text="data.belanja.rekanan?.nama_rekanan || '-'"></p>
                                                        </div>
                                                        <div class="pt-2 border-t border-dashed border-gray-200">
                                                            <label
                                                                class="block text-[9px] font-black uppercase text-gray-400 mb-0.5">Rekening
                                                                Bank</label>
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-black rounded"
                                                                    x-text="data.belanja.rekanan?.nama_bank || 'BANK'"></span>
                                                                <p class="font-bold text-gray-800 font-mono text-xs"
                                                                    x-text="data.belanja.rekanan?.no_rekening || '-'">
                                                                </p>
                                                                <p class="text-[9px] font-mono text-gray-500"
                                                                    x-text="'NPWP: ' + (data.belanja.rekanan?.npwp || '-')">
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 2. Potongan Pajak --}}
                                            <div class="bg-red-50/40 rounded-xl border border-red-100 p-4">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <div class="p-1 bg-red-500 text-white rounded md shadow-sm">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="3"
                                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <label
                                                        class="text-[10px] font-black uppercase text-red-600 tracking-widest">Informasi
                                                        Pajak</label>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                                    <template
                                                        x-if="!data.belanja.pajaks || data.belanja.pajaks.length === 0">
                                                        <div
                                                            class="col-span-full py-3 bg-white/50 rounded-lg border border-dashed border-red-200 text-center">
                                                            <p
                                                                class="text-[9px] text-red-400 font-black uppercase tracking-widest">
                                                                Nihil / Tidak ada potongan pajak</p>
                                                        </div>
                                                    </template>

                                                    <template x-for="pjk in data.belanja.pajaks"
                                                        :key="pjk.dasar_pajak_id">
                                                        <div x-data="{
    teksUraian: pjk.is_setor == 1
        ? `Disetor ${pjk.master_pajak?.nama_pajak || 'Pajak'} atas SPJ ${data.belanja?.uraian || ''}`
        : `Diterima ${pjk.master_pajak?.nama_pajak || 'Pajak'} atas SPJ ${data.belanja?.uraian || ''} dari ${data.belanja?.rekanan?.nama_rekanan || '-'}`
 }" class="flex justify-between items-center bg-white p-2.5 rounded-lg border border-red-50 shadow-sm">

                                                            <div class="flex flex-col pr-2">
                                                                <span
                                                                    class="font-black text-gray-800 text-[10px] uppercase"
                                                                    x-text="pjk.master_pajak?.nama_pajak || 'Pajak'"></span>

                                                                <div class="flex items-start gap-1.5 mt-0.5">
                                                                    <span
                                                                        class="text-[8px] text-gray-400 font-medium leading-tight"
                                                                        x-text="teksUraian"></span>

                                                                    <button type="button" x-data="{ copied: false }"
                                                                        @click="
        navigator.clipboard.writeText(teksUraian);
        copied = true;
        setTimeout(() => copied = false, 2000)
    " class="relative flex items-center justify-center p-1 rounded-md transition-all duration-200 shrink-0 group"
                                                                        :class="copied ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-500'">

                                                                        <svg x-show="!copied"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-3 h-3" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                                            stroke-width="2.5">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                                                        </svg>

                                                                        <svg x-show="copied"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            class="w-3 h-3" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                                            stroke-width="3">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M5 13l4 4L19 7" />
                                                                        </svg>

                                                                        <span x-show="!copied"
                                                                            class="absolute bottom-full mb-1 hidden group-hover:block bg-gray-800 text-white text-[8px] px-1.5 py-0.5 rounded shadow-sm whitespace-nowrap">
                                                                            Salin Uraian
                                                                        </span>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="text-right shrink-0">
                                                                <span class="font-black text-red-600 font-mono text-xs"
                                                                    x-text="new Intl.NumberFormat('id-ID').format(pjk.nominal)"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- 3. Table Rincian --}}
                                            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-xs">
                                                        <thead
                                                            class="bg-gray-800 text-[9px] font-black uppercase text-white tracking-wider">
                                                            <tr>
                                                                <th class="px-3 py-2.5 text-left">Komponen</th>
                                                                <th class="px-2 py-2.5 text-center">
                                                                    Vol & Pagu<br>
                                                                    <span
                                                                        class="text-[8px] text-gray-400 font-medium normal-case tracking-normal">TW
                                                                        / Setahun</span>
                                                                </th>
                                                                <th class="px-3 py-2.5 text-right">Harga Satuan</th>
                                                                <th class="px-3 py-2.5 text-right">Total Bruto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            <template x-for="item in data.belanja.rincis"
                                                                :key="item.id">
                                                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                                                    <td class="px-3 py-2.5">
                                                                        <div class="flex flex-col">
                                                                            <span
                                                                                class="font-bold text-gray-800 leading-tight"
                                                                                x-text="item.namakomponen"></span>
                                                                            <span
                                                                                class="text-[9px] text-gray-400 mt-0.5 italic"
                                                                                x-text="item.spek ? 'Spek: ' + item.spek : '-'"></span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-2 py-2 align-top">

                                                                        {{-- Volume Belanja --}}
                                                                        <div class="mb-1.5">
                                                                            <span
                                                                                class="text-[7px] uppercase text-gray-400 tracking-wide">Vol
                                                                                Belanja</span>
                                                                            <p class="leading-tight">
                                                                                <span x-text="item.volume"
                                                                                    class="inline-block bg-emerald-600 text-white text-[10px] font-mono font-medium px-1.5 py-0.5 rounded"></span>
                                                                            </p>
                                                                        </div>

                                                                        <div class="w-full h-px bg-gray-100 mb-1.5">
                                                                        </div>

                                                                        {{-- Volume: TW / Setahun --}}
                                                                        <div class="mb-1">
                                                                            <span
                                                                                class="text-[7px] uppercase text-gray-400 tracking-wide">Vol</span>
                                                                            <p
                                                                                class="text-[9px] font-mono font-medium text-gray-800 leading-tight">
                                                                                <span
                                                                                    x-text="item.total_volume_akb ?? '-'"></span>
                                                                                <span class="text-gray-300"> / </span>
                                                                                <span
                                                                                    x-text="item.total_volume_setahun ?? '-'"></span>
                                                                            </p>
                                                                        </div>

                                                                        {{-- Pagu: TW / Setahun --}}
                                                                        <div>
                                                                            <span
                                                                                class="text-[7px] uppercase text-gray-400 tracking-wide">Pagu</span>
                                                                            <p
                                                                                class="text-[9px] font-mono font-medium text-gray-700 leading-tight">
                                                                                <span
                                                                                    x-text="item.pagu_dana !== undefined ? 'Rp '+new Intl.NumberFormat('id-ID').format(Math.floor(item.pagu_dana)) : '-'"></span>
                                                                                <span class="text-gray-300"> / </span>
                                                                                <span
                                                                                    x-text="item.pagu_setahun !== undefined ? 'Rp '+new Intl.NumberFormat('id-ID').format(Math.floor(item.pagu_setahun)) : '-'"></span>
                                                                            </p>
                                                                        </div>

                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-right">
                                                                        <div class="text-gray-600 font-medium"
                                                                            x-text="new Intl.NumberFormat('id-ID').format(item.harga_satuan)">
                                                                        </div>
                                                                        <div
                                                                            class="text-[9px] mt-0.5 flex flex-col items-end">
                                                                            <span class="text-gray-400 italic">Inc. 11%
                                                                                PPN:</span>
                                                                            <span
                                                                                class="font-bold text-emerald-600 tracking-wider"
                                                                                x-text="Math.round(parseFloat(item.harga_satuan || 0) * 1.11)"></span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-right font-black text-emerald-600 font-mono"
                                                                        x-text="new Intl.NumberFormat('id-ID').format(item.total_bruto)">
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="px-3 py-2.5 text-right text-[9px] font-black uppercase text-gray-500 tracking-wider">
                                                                    Total Bruto (A)</td>
                                                                <td
                                                                    class="px-3 py-2.5 text-right font-bold text-gray-800 font-mono text-sm">
                                                                    <span
                                                                        x-text="new Intl.NumberFormat('id-ID').format(data.belanja.rincis.reduce((acc, item) => acc + parseFloat(item.total_bruto || 0), 0))"></span>
                                                                </td>
                                                            </tr>
                                                            <template
                                                                x-if="data.belanja.pajaks && data.belanja.pajaks.length > 0">
                                                                <tr class="bg-red-50/20 border-t border-red-100">
                                                                    <td colspan="3"
                                                                        class="px-3 py-2 text-right text-[9px] font-black uppercase text-red-400 tracking-wider">
                                                                        Total Potongan Pajak (B)</td>
                                                                    <td
                                                                        class="px-3 py-2 text-right font-bold text-red-500 font-mono text-xs">
                                                                        - <span
                                                                            x-text="new Intl.NumberFormat('id-ID').format(data.belanja.pajaks.reduce((acc, pjk) => acc + parseFloat(pjk.nominal || 0), 0))"></span>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                            <tr class="bg-emerald-50/60 border-t border-emerald-100">
                                                                <td colspan="3"
                                                                    class="px-3 py-3 text-right text-[10px] font-black uppercase text-emerald-700 tracking-widest">
                                                                    Total Netto Diterima (A - B)</td>
                                                                <td
                                                                    class="px-3 py-3 text-right font-black text-base text-emerald-600 font-mono border-l-2 border-emerald-400">
                                                                    <span x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(
                                                            data.belanja.rincis.reduce((acc, item) => acc + parseFloat(item.total_bruto || 0), 0) -
                                                            (data.belanja.pajaks ? data.belanja.pajaks.reduce((acc, pjk) => acc + parseFloat(pjk.nominal || 0), 0) : 0)
                                                        )"></span>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Footer Button --}}
                                            <div class="flex justify-end pt-2">
                                                <button type="button" @click="open = false"
                                                    class="px-6 py-2.5 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-emerald-600 shadow-md shadow-gray-200 transition-all active:scale-95">
                                                    Tutup Detail
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>

            {{-- Akhir modal --}}
        </div>

</x-app-layout>