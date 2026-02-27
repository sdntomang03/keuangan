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

                                {{-- ========================================================== --}}
                                {{-- BARIS SALDO AWAL (Hanya muncul jika filter TW > 1) --}}
                                {{-- ========================================================== --}}
                                @if(request('tw') && request('tw') > 1 && isset($saldoAwal))
                                <tr class="bg-gray-100/80 font-bold text-gray-600">
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

                                @forelse($bkus as $item)
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
                                    <td class="px-6 py-4" x-data="{ expanded: false }">
                                        <div class="flex flex-col">
                                            <div @click="expanded = !expanded"
                                                class="flex items-center cursor-pointer group">
                                                <span
                                                    :class="expanded ? 'text-blue-600 font-bold' : 'text-gray-700 font-medium'"
                                                    class="transition-all italic">
                                                    {{ $item->uraian }}
                                                </span>
                                                @if($item->belanja)
                                                <svg class="w-4 h-4 ml-2 text-gray-400 transition-transform duration-300"
                                                    :class="expanded ? 'rotate-180' : ''" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                                @endif
                                            </div>

                                            {{-- Detail Collapse Belanja --}}
                                            @if($item->belanja)
                                            <div x-show="expanded" x-cloak
                                                class="mt-3 overflow-hidden transition-all duration-300">
                                                <div
                                                    class="bg-gray-50 border-l-4 border-emerald-500 rounded-r-xl p-3 space-y-3 shadow-inner">
                                                    <div class="pb-2 border-b border-gray-200">
                                                        <span
                                                            class="block text-[9px] uppercase font-black text-emerald-600 tracking-widest">Nama
                                                            Kegiatan</span>
                                                        <span
                                                            class="text-[11px] font-bold text-gray-800 block uppercase mt-1">
                                                            {{ $item->belanja->kegiatan->namagiat ?? '⚠️ Data Tidak
                                                            Ditemukan' }}
                                                        </span>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                                        <div>
                                                            <span
                                                                class="block text-[9px] uppercase font-black text-blue-400 tracking-widest leading-none">Rekanan</span>
                                                            <span
                                                                class="text-gray-800 font-bold uppercase text-[10px] block leading-tight">
                                                                {{ $item->belanja->rekanan->nama_rekanan ?? 'Internal'
                                                                }}
                                                            </span>

                                                            @if(isset($item->belanja->rekanan))
                                                            <div class="flex flex-col mt-1 space-y-0.5">
                                                                <div class="flex items-center gap-1">
                                                                    <span
                                                                        class="text-[8px] font-bold text-gray-400 uppercase">Rek:</span>
                                                                    <span class="text-[9px] text-gray-600 font-medium">
                                                                        {{ $item->belanja->rekanan->nama_bank ?? '' }} |
                                                                        {{
                                                                        $item->belanja->rekanan->no_rekening ?? '-' }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex items-center gap-1">
                                                                    <span
                                                                        class="text-[8px] font-bold text-gray-400 uppercase">NPWP:</span>
                                                                    <span
                                                                        class="text-[9px] text-gray-600 font-medium">{{
                                                                        $item->belanja->rekanan->npwp ?? '-' }}</span>
                                                                </div>

                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
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
                                        <div class="flex items-center justify-center gap-2">
                                            @if($item->belanja_id)
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    @click="$dispatch('open-modal-detail', { id: {{ $item->belanja_id }} })"
                                                    title="Lihat Detail"
                                                    class="p-2 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-800 rounded-lg border border-emerald-100 transition-all duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>

                                                <form action="{{ route('bku.unpost', $item->belanja_id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                        onclick="return confirm('Batalkan posting belanja ini?')"
                                                        title="Batal Post (Kembalikan ke Draft)"
                                                        class="p-2 text-red-600 hover:bg-red-50 hover:text-red-800 rounded-lg border border-red-100 transition-all duration-200 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                            class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                        </svg>
                                                    </button>
                                                </form>

                                                @if($item->pajak_id)
                                                <form action="{{ route('pajak.hapus_setor', $item->pajak_id) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors"
                                                        onclick="return confirm('Hapus bukti setoran?')"
                                                        title="Hapus Setoran">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-1.123c0-.798-.65-1.462-1.462-1.462H10.87c-.812 0-1.462.664-1.462 1.462v1.123m4.5 0H9" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                            @elseif($item->penerimaan_id)
                                            <div class="flex items-center gap-2">
                                                <button @click="fetchEdit({{ $item->penerimaan_id }})" title="Edit Data"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-800 rounded-lg border border-blue-100 transition-all duration-200 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                </button>

                                                <form action="{{ route('bku.destroy', $item->id) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        onclick="return confirm('Hapus dana masuk ini?')"
                                                        title="Hapus Data"
                                                        class="p-2 text-orange-600 hover:bg-orange-50 hover:text-orange-800 rounded-lg border border-orange-100 transition-all duration-200 shadow-sm">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                            class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                            @else
                                            <span class="text-gray-300 italic text-[10px]">System</span>
                                            @endif
                                        </div>
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

                        <div class="flex items-center justify-center min-h-screen px-4 py-10">
                            {{-- Backdrop --}}
                            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                                @click="open = false"></div>

                            {{-- Modal Content --}}
                            <div
                                class="relative bg-white rounded-[2rem] shadow-2xl max-w-3xl w-full overflow-hidden transform transition-all border border-gray-100">

                                {{-- Accent Line --}}
                                <div class="h-2 w-full bg-emerald-500"></div>

                                <div class="p-8">
                                    {{-- Header --}}
                                    <div class="flex justify-between items-start mb-8">
                                        <div>
                                            <h3 class="text-2xl font-black text-gray-800 leading-none">Rincian Transaksi
                                            </h3>
                                            <p
                                                class="text-[11px] text-emerald-600 font-bold uppercase tracking-[0.2em] mt-2">
                                                Detail Belanja BKU</p>
                                        </div>
                                        <button @click="open = false"
                                            class="bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 p-2 rounded-xl transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Loading State --}}
                                    <div x-show="loading" class="py-20 text-center">
                                        <div
                                            class="inline-block animate-spin rounded-full h-10 w-10 border-[3px] border-emerald-500 border-t-transparent mb-4">
                                        </div>
                                        <p
                                            class="text-xs font-bold text-gray-400 uppercase tracking-widest animate-pulse">
                                            Menarik data dari server...</p>
                                    </div>

                                    {{-- Data Content --}}
                                    <div x-show="!loading && data" class="space-y-6">

                                        {{-- 1. Informasi Utama & Rekanan --}}
                                        <div class="bg-gray-50 rounded-[1.5rem] border border-gray-100 p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                {{-- Uraian (Full Width) --}}
                                                <div class="md:col-span-2 space-y-1">
                                                    <label
                                                        class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Uraian
                                                        Transaksi</label>
                                                    <p class="text-base font-bold text-gray-800 leading-relaxed"
                                                        x-text="'Dibayar ' + data.belanja.uraian + ' kepada ' + (data.belanja.rekanan?.nama_rekanan || 'Pihak Ketiga')">
                                                    </p>
                                                </div>

                                                <div class="h-px bg-gray-200 md:col-span-2"></div>

                                                {{-- Info Program & Rekening --}}
                                                <div class="space-y-4">
                                                    <div>
                                                        <label
                                                            class="block text-[9px] font-black uppercase text-emerald-500 mb-1">Program
                                                            / Kegiatan</label>
                                                        <p class="text-xs font-bold text-gray-600 uppercase italic leading-tight"
                                                            x-text="data.belanja.kegiatan.namagiat || '-'"></p>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-[9px] font-black uppercase text-blue-500 mb-1">Kode
                                                            Rekening</label>
                                                        <p class="text-sm font-mono font-bold text-gray-700"
                                                            x-text="data.belanja.korek.ket || '-'"></p>
                                                    </div>
                                                </div>

                                                {{-- Info Rekanan & Bank --}}
                                                <div
                                                    class="bg-white/70 p-4 rounded-2xl border border-gray-100 space-y-3">
                                                    <div>
                                                        <label
                                                            class="block text-[9px] font-black uppercase text-gray-400 mb-1">Penerima
                                                            / Rekanan</label>
                                                        <p class="font-black text-gray-800 uppercase text-sm"
                                                            x-text="data.belanja.rekanan?.nama_rekanan || '-'"></p>

                                                    </div>
                                                    <div class="pt-2 border-t border-dashed border-gray-200">
                                                        <label
                                                            class="block text-[9px] font-black uppercase text-gray-400 mb-1">Rekening
                                                            Bank</label>
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-black rounded"
                                                                x-text="data.belanja.rekanan?.nama_bank || 'BANK'"></span>
                                                            <p class="font-bold text-gray-800 font-mono text-sm"
                                                                x-text="data.belanja.rekanan?.no_rekening || '-'"></p>
                                                            <p class="text-[10px] font-mono text-gray-500"
                                                                x-text="'NPWP: ' + (data.belanja.rekanan?.npwp || '-')">
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Potongan Pajak --}}
                                        <div class="bg-red-50/50 rounded-[1.5rem] border border-red-100 p-5">
                                            <div class="flex items-center gap-2 mb-4">
                                                <div
                                                    class="p-1.5 bg-red-500 text-white rounded-lg shadow-sm shadow-red-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3"
                                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <label
                                                    class="text-[11px] font-black uppercase text-red-600 tracking-widest">Informasi
                                                    Potongan Pajak</label>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <template
                                                    x-if="!data.belanja.pajaks || data.belanja.pajaks.length === 0">
                                                    <div
                                                        class="col-span-full py-6 bg-white/40 rounded-2xl border border-dashed border-red-200 flex flex-col items-center">
                                                        <p
                                                            class="text-[10px] text-red-400 font-black uppercase tracking-widest">
                                                            Nihil / Tidak ada potongan pajak</p>
                                                    </div>
                                                </template>

                                                <template x-for="pjk in data.belanja.pajaks" :key="pjk.dasar_pajak_id">
                                                    <div
                                                        class="flex justify-between items-center bg-white p-4 rounded-2xl border border-red-50 shadow-sm transition hover:shadow-md">
                                                        <div class="flex flex-col">
                                                            <span class="font-black text-gray-800 text-xs uppercase"
                                                                x-text="pjk.master_pajak?.nama_pajak || 'Pajak'"></span>
                                                            <span class="text-[9px] text-gray-400 font-medium"
                                                                x-text="pjk.is_terima == 0 ? 'Diterima' : 'Disetor'"></span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="font-black text-red-600 font-mono text-sm"
                                                                x-text="new Intl.NumberFormat('id-ID').format(pjk.nominal)"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- 3. Table Rincian --}}
                                        <div class="overflow-hidden rounded-2xl border border-gray-100 shadow-sm">
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead
                                                        class="bg-gray-800 text-[10px] font-black uppercase text-white tracking-wider">
                                                        <tr>
                                                            <th class="px-4 py-4 text-left">Komponen</th>
                                                            <th class="px-4 py-4 text-center">Vol</th>
                                                            <th class="px-4 py-4 text-right">Harga Satuan</th>
                                                            <th class="px-4 py-4 text-right">Total Bruto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100">
                                                        <template x-for="item in data.belanja.rincis" :key="item.id">
                                                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                                                <td class="px-4 py-4">
                                                                    <div class="flex flex-col">
                                                                        <span
                                                                            class="font-bold text-gray-800 leading-tight"
                                                                            x-text="item.namakomponen"></span>
                                                                        <span
                                                                            class="text-[10px] text-gray-400 mt-1 italic"
                                                                            x-text="item.spek ? 'Spek: ' + item.spek : '-'"></span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-4 text-center font-mono font-bold text-gray-500"
                                                                    x-text="item.volume"></td>
                                                                <td class="px-4 py-4 text-right text-gray-600 font-medium"
                                                                    x-text="new Intl.NumberFormat('id-ID').format(item.harga_satuan)">
                                                                </td>
                                                                <td class="px-4 py-4 text-right font-black text-emerald-600 font-mono"
                                                                    x-text="new Intl.NumberFormat('id-ID').format(item.total_bruto)">
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                                        {{-- Baris Total Bruto --}}
                                                        <tr>
                                                            <td colspan="3"
                                                                class="px-4 py-3 text-right text-[10px] font-black uppercase text-gray-500 tracking-wider">
                                                                Total Bruto (A)
                                                            </td>
                                                            <td
                                                                class="px-4 py-3 text-right font-bold text-gray-800 font-mono">
                                                                <span
                                                                    x-text="new Intl.NumberFormat('id-ID').format(data.belanja.rincis.reduce((acc, item) => acc + parseFloat(item.total_bruto || 0), 0))"></span>
                                                            </td>
                                                        </tr>

                                                        {{-- Baris Total Pajak (Hanya muncul jika ada pajak) --}}
                                                        <template
                                                            x-if="data.belanja.pajaks && data.belanja.pajaks.length > 0">
                                                            <tr class="bg-red-50/20">
                                                                <td colspan="3"
                                                                    class="px-4 py-2 text-right text-[10px] font-black uppercase text-red-400 tracking-wider">
                                                                    Total Potongan Pajak (B)
                                                                </td>
                                                                <td
                                                                    class="px-4 py-2 text-right font-bold text-red-500 font-mono">
                                                                    - <span
                                                                        x-text="new Intl.NumberFormat('id-ID').format(data.belanja.pajaks.reduce((acc, pjk) => acc + parseFloat(pjk.nominal || 0), 0))"></span>
                                                                </td>
                                                            </tr>
                                                        </template>

                                                        {{-- Baris Total Netto / Diterima --}}
                                                        <tr class="bg-emerald-50/50">
                                                            <td colspan="3"
                                                                class="px-4 py-5 text-right text-[11px] font-black uppercase text-emerald-700 tracking-widest">
                                                                Total Netto Diterima (A - B)
                                                            </td>
                                                            <td
                                                                class="px-4 py-5 text-right font-black text-xl text-emerald-600 font-mono border-l-4 border-emerald-500">
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
                                        <div class="flex justify-end pt-4">
                                            <button type="button" @click="open = false"
                                                class="px-8 py-3 bg-gray-900 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 shadow-xl shadow-gray-200 transition-all active:scale-95">
                                                Tutup Detail
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>

            {{-- Akhir modal --}}
        </div>



</x-app-layout>