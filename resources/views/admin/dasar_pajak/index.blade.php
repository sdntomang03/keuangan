<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen"
        x-data="{ createModal: false, editModal: false, deleteModal: false, selectedData: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Success / Error --}}
            @if (session('success'))
            <div x-data="{ show: true }" x-show="show"
                class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-xl shadow-sm flex justify-between items-start">
                <div class="flex">
                    <svg class="h-5 w-5 text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-emerald-800">Berhasil!</h3>
                        <div class="mt-1 text-sm text-emerald-700">{{ session('success') }}</div>
                    </div>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
            @endif

            @if (session('error') || $errors->any())
            <div x-data="{ show: true }" x-show="show"
                class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm flex justify-between items-start">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Terjadi Kesalahan</h3>
                        <div class="mt-1 text-sm text-red-700">
                            {{ session('error') }}
                            @foreach ($errors->all() as $error) <p>- {{ $error }}</p> @endforeach
                        </div>
                    </div>
                </div>
                <button @click="show = false" class="text-red-500 hover:text-red-800">&times;</button>
            </div>
            @endif

            {{-- Header Title --}}
            <div
                class="mb-6 p-6 rounded-2xl shadow-lg bg-gradient-to-r from-gray-800 to-gray-700 text-white flex justify-between items-center">
                <div>
                    <p class="text-[10px] uppercase font-bold opacity-80 tracking-widest">Administrator</p>
                    <h2 class="text-2xl font-black uppercase tracking-tight">Master Data Pajak</h2>
                </div>
                <button @click="createModal = true"
                    class="bg-blue-500 hover:bg-blue-400 text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-md transition transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Pajak
                </button>
            </div>

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Nama Pajak</th>
                                <th class="px-6 py-4 text-center">Persentase</th>
                                <th class="px-6 py-4 text-center">Sifat</th>
                                <th class="px-6 py-4 text-center">Status Riwayat</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pajaks as $pajak)
                            @php
                            // Cek relasi apakah pajak sudah dipakai di BKU/Belanja
                            $isUsed = \App\Models\Pajak::where('dasar_pajak_id', $pajak->id)->exists();
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $pajak->nama_pajak }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg font-black">{{ (float)
                                        $pajak->persen }}%</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($pajak->jenis === 'penambah')
                                    <span
                                        class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-bold uppercase tracking-widest">Penambah
                                        (+)</span>
                                    @else
                                    <span
                                        class="text-[10px] bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-bold uppercase tracking-widest">Pengurang
                                        (-)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($isUsed)
                                    <span class="text-xs text-red-500 font-bold flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                        Terkunci (Dipakai)
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-400 font-medium">Bebas Edit</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="editModal = true; selectedData = {
                                            id: '{{ $pajak->id }}',
                                            nama: '{{ $pajak->nama_pajak }}',
                                            persen: '{{ (float) $pajak->persen }}',
                                            jenis: '{{ $pajak->jenis }}',
                                            isUsed: {{ $isUsed ? 'true' : 'false' }}
                                        }"
                                            class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-500 hover:text-white rounded-lg transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.dasar-pajak.destroy', $pajak->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus jenis pajak ini secara permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-600 bg-red-50 hover:bg-red-500 hover:text-white rounded-lg transition"
                                                {{ $isUsed ? 'disabled title="Tidak bisa dihapus"' : '' }}
                                                style="{{ $isUsed ? 'opacity:0.5; cursor:not-allowed;' : '' }}">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium italic">
                                    Belum ada master pajak yang ditambahkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- MODAL TAMBAH DATA --}}
        <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModal" x-transition.opacity
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="createModal = false">
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="createModal" x-transition
                    class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-black text-gray-900 uppercase">Tambah Pajak Baru</h3>
                        <button @click="createModal = false" class="text-gray-400 hover:text-gray-500"><svg
                                class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <form action="{{ route('admin.dasar-pajak.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pajak</label>
                                <input type="text" name="nama_pajak" required
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm"
                                    placeholder="Contoh: PPN 11% / PPh 21">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Persentase
                                        (%)</label>
                                    <input type="number" step="any" name="persen" required
                                        class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm"
                                        placeholder="Contoh: 11">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sifat
                                        Potongan</label>
                                    <select name="jenis" required
                                        class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm">
                                        <option value="pengurang">Pengurang (-)</option>
                                        <option value="penambah">Penambah (+)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan
                                Pajak</button>
                            <button type="button" @click="createModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT DATA --}}
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModal" x-transition.opacity
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="editModal" x-transition
                    class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-black text-gray-900 uppercase">Ubah Data Pajak</h3>
                        <button @click="editModal = false" class="text-gray-400 hover:text-gray-500"><svg
                                class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>

                    {{-- Warning Info Jika Dikunci --}}
                    <div x-show="selectedData.isUsed"
                        class="bg-amber-50 border-l-4 border-amber-500 p-3 mb-4 rounded-r-lg text-sm text-amber-800">
                        <span class="font-bold block mb-1">Terkunci Sebagian!</span>
                        Pajak ini sudah digunakan di transaksi sekolah. Anda hanya dapat mengubah <b>Nama Pajaknya</b>
                        saja.
                    </div>

                    <form :action="`{{ url('admin/dasar-pajak') }}/${selectedData.id}`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pajak</label>
                                <input type="text" name="nama_pajak" x-model="selectedData.nama" required
                                    class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Persentase
                                        (%)</label>
                                    <input type="number" step="any" name="persen" x-model="selectedData.persen"
                                        :readonly="selectedData.isUsed"
                                        :class="selectedData.isUsed ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                        class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sifat
                                        Potongan</label>
                                    <select name="jenis" x-model="selectedData.jenis" :disabled="selectedData.isUsed"
                                        :class="selectedData.isUsed ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                        class="w-full rounded-xl border-gray-200 focus:ring-blue-500 text-sm">
                                        <option value="pengurang">Pengurang (-)</option>
                                        <option value="penambah">Penambah (+)</option>
                                    </select>

                                    {{-- Hidden input wajib jika Select di-disabled agar datanya tetap terkirim saat
                                    form disubmit --}}
                                    <template x-if="selectedData.isUsed">
                                        <input type="hidden" name="jenis" :value="selectedData.jenis">
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan
                                Perubahan</button>
                            <button type="button" @click="editModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>