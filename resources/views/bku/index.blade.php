<x-app-layout>
    {{-- Container Utama dengan x-data --}}
    <div class="py-12 bg-gray-50" x-data="{
        openModal: false,
        openEditModal: false,
        editData: { id: '', tanggal: '', no_bukti: '', uraian: '', nominal: '' },

        async fetchEdit(id) {
            try {
                const response = await axios.get(`${window.location.origin}/penerimaan/${id}/edit`);
                this.editData = response.data;
                this.openEditModal = true;
            } catch (error) {
                console.error(error);
                alert('Gagal mengambil data transaksi. Pastikan route sudah benar.');
            }
        }
    }">
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
                                                                        class="text-[8px] font-bold text-gray-400 uppercase">NPWP:</span>
                                                                    <span
                                                                        class="text-[9px] text-gray-600 font-medium">{{
                                                                        $item->belanja->rekanan->npwp ?? '-' }}</span>
                                                                </div>
                                                                <div class="flex items-center gap-1">
                                                                    <span
                                                                        class="text-[8px] font-bold text-gray-400 uppercase">Rek:</span>
                                                                    <span class="text-[9px] text-gray-600 font-medium">
                                                                        {{ $item->belanja->rekanan->nama_bank ?? '' }} |
                                                                        {{
                                                                        $item->belanja->rekanan->no_rekening ?? '-' }}
                                                                    </span>
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
                                            <form action="{{ route('bku.unpost', $item->belanja_id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit"
                                                    onclick="return confirm('Batalkan posting belanja ini?')"
                                                    class="text-[10px] font-extrabold text-red-600 hover:bg-red-50 px-2 py-1 rounded border border-red-100 uppercase transition">
                                                    Batal Post
                                                </button>
                                            </form>
                                            @elseif($item->penerimaan_id)
                                            <button @click="fetchEdit({{ $item->penerimaan_id }})"
                                                class="text-[10px] font-extrabold text-blue-600 hover:bg-blue-50 px-2 py-1 rounded border border-blue-100 uppercase transition">
                                                Edit
                                            </button>
                                            <form action="{{ route('bku.destroy', $item->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" onclick="return confirm('Hapus dana masuk ini?')"
                                                    class="text-[10px] font-extrabold text-orange-600 hover:bg-orange-50 px-2 py-1 rounded border border-orange-100 uppercase transition">
                                                    Hapus
                                                </button>
                                            </form>
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

        </div>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
</x-app-layout>