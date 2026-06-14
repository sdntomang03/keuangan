<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Daftar Seluruh Dokumen Surat — <span class="text-indigo-600 font-black">Triwulan {{ $triwulanAktif }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50"
        x-data="{ openEditModal: false, editId: '', editTanggal: '', editBast: '', jenisSurat: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Success / Error --}}
            @if(session('success'))
            <div
                class="mb-4 bg-white border-l-4 border-green-500 p-4 shadow-sm flex items-center text-green-700 font-bold text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div
                class="mb-4 bg-white border-l-4 border-red-500 p-4 shadow-sm flex items-center text-red-700 font-bold text-sm">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-800 text-white uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-5 text-left">Nomor Surat</th>
                                <th class="px-6 py-5 text-left">Tanggal Surat</th>
                                <th class="px-6 py-5 text-left">Jenis</th>
                                <th class="px-6 py-5 text-left">Keterangan</th>
                                <th class="px-6 py-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($listSurat as $surat)
                            <tr class="hover:bg-indigo-50/50 transition duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <div class="font-black text-indigo-900">{{ $surat->nomor_surat }}</div>
                                </td>
                                <td class="px-6 py-4 text-left font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span
                                        class="px-2 py-1 rounded bg-indigo-100 text-indigo-700 font-bold text-xs uppercase">
                                        {{ $surat->jenis_surat }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-left text-gray-500 text-xs">
                                    {{ $surat->keterangan ?? '-' }}
                                    @if($surat->jenis_surat === 'BAPB')
                                    <br><span class="text-indigo-400 font-bold tracking-tight">BAST: {{ $surat->no_bast
                                        ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center flex justify-center gap-2">

                                    {{-- Tombol Edit (Membuka Modal) --}}
                                    <button type="button" @click="openEditModal = true;
            editId = '{{ $surat->id }}';
            editTanggal = '{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('Y-m-d') }}';
            editBast = '{{ $surat->no_bast }}';
            jenisSurat = '{{ $surat->jenis_surat }}';"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-md font-bold text-xs transition">
                                        Edit
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('surat.destroy', $surat->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-md font-bold text-xs transition">
                                            Hapus
                                        </button>
                                    </form>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5"
                                    class="px-6 py-12 text-center text-gray-400 font-bold italic uppercase tracking-widest text-xs">
                                    Tidak ada data surat yang ditemukan pada triwulan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-white border-t border-gray-100">
                    {{ $listSurat->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL EDIT SURAT (Alpine JS) --}}
        <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900 opacity-75" @click="openEditModal = false"></div>
                </div>

                <div
                    class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form :action="'/surat/update/' + editId" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-black text-gray-900 mb-6 uppercase tracking-widest border-b pb-2">
                                Edit Data Surat</h3>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Tanggal
                                    Surat</label>
                                <input type="date" name="tanggal_surat" x-model="editTanggal" required
                                    class="mt-2 block w-full border-gray-300 rounded-lg shadow-sm font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Form BAST hanya muncul jika jenis suratnya BAPB --}}
                            <div class="mb-4" x-show="jenisSurat === 'BAPB'" style="display: none;">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Nomor
                                    BAST</label>
                                <input type="text" name="nomor_bast" x-model="editBast"
                                    class="mt-2 block w-full border-gray-300 rounded-lg shadow-sm font-bold text-gray-800 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent px-6 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none shadow-md transition">
                                Simpan Perubahan
                            </button>
                            <button type="button" @click="openEditModal = false"
                                class="inline-flex justify-center rounded-lg border border-gray-300 px-6 py-2 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none shadow-sm transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>