<x-app-layout>
    {{-- Tambahkan AlpineJS untuk menangani Modal (Biasanya sudah ada di Breeze/Jetstream) --}}
    {{-- Jika belum ada, tambahkan: <script src="//unpkg.com/alpinejs" defer></script> --}}

    <div class="py-12" x-data="{ showModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ALERT --}}
            {{-- ALERT SUKSES (BISA DICLOSE) --}}
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-start">

                {{-- Pesan --}}
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>

                {{-- Tombol Close --}}
                <button @click="show = false" type="button"
                    class="text-green-500 hover:text-green-800 focus:outline-none transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">

                {{-- HEADER & TOMBOL --}}
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Manajemen Surat</h2>
                        <p class="text-sm text-gray-500">{{ $belanja->uraian }}</p>
                    </div>

                    <div class="flex gap-2">

                        {{-- 1. TOMBOL INPUT HARGA PENAWARAN (BARU) --}}
                        <a href="{{ route('belanja.edit_penawaran', $belanja->id) }}"
                            class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm font-bold shadow-md transition flex items-center gap-2">
                            {{-- Icon Dollar/Money --}}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Input Harga Penawaran
                        </a>

                        {{-- 2. Tombol Generate Standar --}}
                        <form action="{{ route('surat.generate', $belanja->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-bold transition shadow-sm border border-gray-300">
                                Generate Full
                            </button>
                        </form>

                        {{-- 3. TOMBOL BUKA MODAL PARSIAL --}}
                        <button @click="showModal = true"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-bold shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            + Tambah Parsial
                        </button>
                    </div>
                </div>

                {{-- TABEL SURAT --}}
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3">Jenis Surat</th>
                                <th class="px-6 py-3">Nomor Surat</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($belanja->surats as $surat)
                            <tr class="bg-white hover:bg-gray-50 transition">

                                {{-- KOLOM JENIS & BADGE PARSIAL --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900">{{ $surat->jenis_surat }}</span>

                                        {{-- TANDAI JIKA PARSIAL --}}
                                        @if($surat->is_parsial)
                                        <span
                                            class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-purple-200">
                                            {{ $surat->keterangan ?? 'Parsial' }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 block mt-1">
                                        @if($surat->jenis_surat == 'PH') Permintaan Harga
                                        @elseif($surat->jenis_surat == 'SP') Surat Pesanan
                                        @elseif($surat->jenis_surat == 'BAPB') Berita Acara
                                        @endif
                                    </span>
                                </td>

                                {{-- Kolom Nomor & Tanggal (Editable Form) --}}
                                <td class="px-6 py-4">
                                    <input type="text" name="nomor_surat" value="{{ $surat->nomor_surat }}"
                                        form="form-update-{{ $surat->id }}"
                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 font-mono">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="date" name="tanggal_surat"
                                        value="{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('Y-m-d') : '' }}"
                                        form="form-update-{{ $surat->id }}"
                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500">
                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center gap-2">

                                        {{-- 1. Simpan Edit --}}
                                        <form id="form-update-{{ $surat->id }}"
                                            action="{{ route('surat.update', $surat->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button type="submit" title="Simpan Perubahan"
                                                class="text-green-600 hover:bg-green-50 p-2 rounded transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- 2. Cetak --}}
                                        {{-- Anda bisa memfilter cetak per ID jika mau, tapi saat ini cetak global per
                                        belanja --}}
                                        {{-- <a href="{{ route('cetak.dokumen_lengkap', $belanja->id) }}"
                                            target="_blank" title="Cetak"
                                            class="text-blue-600 hover:bg-blue-50 p-2 rounded transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a> --}}

                                        {{-- 3. HAPUS SURAT (BARU) --}}
                                        <form action="{{ route('surat.destroy', $surat->id) }}" method="POST"
                                            id="delete-surat-{{ $surat->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDeleteSurat('{{ $surat->id }}')"
                                                title="Hapus Surat"
                                                class="text-red-500 hover:bg-red-50 p-2 rounded transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL PARSIAL (Hidden by default, shown by AlpineJS) --}}
            <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">

                {{-- Backdrop --}}
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div @click="showModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    {{-- Modal Panel --}}
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                        <form action="{{ route('surat.store_parsial', $belanja->id) }}" method="POST">
                            @csrf

                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                    Buat Surat Parsial (SP & BAPB)
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    {{-- Keterangan Tahap --}}
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700">Nama Tahapan</label>
                                        <input type="text" name="keterangan"
                                            placeholder="Contoh: Tahap 1, Pengiriman Awal" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    {{-- Data SP --}}
                                    <div class="bg-blue-50 p-3 rounded border border-blue-100">
                                        <p class="font-bold text-blue-800 mb-2 text-sm">1. Data Surat Pesanan (SP)</p>
                                        <div class="mb-2">
                                            <label class="text-xs text-gray-600">Nomor SP</label>
                                            <input type="text" name="nomor_sp" required
                                                class="w-full text-sm border-gray-300 rounded">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-600">Tanggal SP (H-1)</label>
                                            <input type="date" name="tanggal_sp" required
                                                class="w-full text-sm border-gray-300 rounded">
                                        </div>
                                    </div>

                                    {{-- Data BAPB --}}
                                    <div class="bg-green-50 p-3 rounded border border-green-100">
                                        <p class="font-bold text-green-800 mb-2 text-sm">2. Data Berita Acara (BAPB)</p>
                                        <div class="mb-2">
                                            <label class="text-xs text-gray-600">Nomor BAPB</label>
                                            <input type="text" name="nomor_bapb" required
                                                class="w-full text-sm border-gray-300 rounded">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-600">Tanggal BAPB (Hari H)</label>
                                            <input type="date" name="tanggal_bapb" required
                                                class="w-full text-sm border-gray-300 rounded">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pilih Barang --}}
                                <div class="bg-gray-50 p-4 rounded border border-gray-200 max-h-60 overflow-y-auto">
                                    <p class="font-bold text-gray-700 mb-2 text-sm">3. Pilih Barang & Jumlah Kirim</p>
                                    @foreach($belanja->rincis as $item)
                                    <div
                                        class="flex items-center justify-between p-2 bg-white mb-1 rounded border border-gray-200">
                                        <div class="flex items-center flex-1">
                                            <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1"
                                                checked class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-700">{{ $item->namakomponen ??
                                                    $item->nama_komponen }}</span>
                                                <span class="text-xs text-gray-500">Max: {{ $item->volume }} {{
                                                    $item->satuan }}</span>
                                            </div>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" name="items[{{ $item->id }}][volume]"
                                                value="{{ $item->volume }}" max="{{ $item->volume }}"
                                                class="w-full text-sm border-gray-300 rounded text-right">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                    Simpan Paket Parsial
                                </button>
                                <button @click="showModal = false" type="button"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- SCRIPT DELETE CONFIRMATION --}}
    <script>
        function confirmDeleteSurat(id) {
            Swal.fire({
                title: 'Hapus Surat Ini?',
                text: "Surat beserta rincian barang di dalamnya akan dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-xl',
                    confirmButton: 'rounded-lg px-4 py-2 font-bold',
                    cancelButton: 'rounded-lg px-4 py-2 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-surat-' + id).submit();
                }
            })
        }
    </script>
</x-app-layout>