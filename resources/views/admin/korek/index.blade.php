<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight tracking-tight uppercase">
                    Master Kode Rekening
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    Kelola data kode rekening dan jenis belanja (Operasional, Mesin, Aset Lainnya).
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Menampilkan Pesan Sukses / Error --}}
            @if (session('success'))
            <div
                class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-4 rounded-xl font-medium flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="mb-4 bg-red-50 text-red-800 border border-red-200 p-4 rounded-xl font-medium flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            {{-- PANEL IMPORT UPDATE EXCEL --}}
            <div
                class="bg-indigo-50 border border-indigo-100 p-5 rounded-xl shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="font-black text-indigo-900 text-lg">Update Jenis Belanja Masal</h3>
                    <p class="text-xs text-indigo-700 mt-1 font-medium">Unggah file Excel berisi kolom
                        <strong>kode</strong> dan <strong>jenis_belanja</strong> untuk memperbarui data secara otomatis.
                    </p>
                </div>

                {{-- Pastikan route ini sesuai dengan yang Anda daftarkan di web.php --}}
                <form action="{{ route('admin.korek.import_update') }}" method="POST" enctype="multipart/form-data"
                    class="flex w-full md:w-auto items-center gap-3">
                    @csrf
                    <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-white file:text-indigo-700 hover:file:bg-gray-50 cursor-pointer border border-indigo-200 rounded-lg bg-white shadow-sm">

                    <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-indigo-700 transition shadow-md whitespace-nowrap flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Upload
                    </button>
                </form>
            </div>

            {{-- PANEL PENCARIAN & TAMBAH DATA --}}
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <form action="{{ route('admin.korek.index') }}" method="GET" class="w-full md:w-1/2 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Cari kode rekening, uraian, atau keterangan...">
                    <button type="submit" class="hidden">Cari</button>
                </form>

                <div class="w-full md:w-auto flex justify-end">
                    <a href="{{ route('admin.korek.create') }}"
                        class="bg-gray-800 text-white px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-black transition shadow-md w-full md:w-auto text-center flex justify-center items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Tambah Rekening
                    </a>
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-[12px]">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-center font-bold w-12">No</th>
                                <th class="px-4 py-3 text-left font-bold w-48">Kode Rekening</th>
                                <th class="px-4 py-3 text-left font-bold">Uraian / Keterangan</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Singkatan</th>
                                <th class="px-4 py-3 text-center font-bold w-36">Jenis Belanja</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($koreks as $index => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center text-gray-500 font-medium">
                                    {{ $koreks->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-mono font-bold text-indigo-700 text-[13px]">{{ $item->kode }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-800">{{ $item->uraian_singkat ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Ket: {{ $item->ket ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-gray-600">
                                    {{ $item->singkat ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->jenis_belanja)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase
                                                {{ $item->jenis_belanja == 'operasional' ? 'bg-blue-100 text-blue-800' :
                                                  ($item->jenis_belanja == 'mesin' ? 'bg-orange-100 text-orange-800' :
                                                  'bg-emerald-100 text-emerald-800') }}">
                                        {{ $item->jenis_belanja }}
                                    </span>
                                    @else
                                    <span class="text-gray-400 italic text-[11px]">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="{{ route('admin.korek.edit', $item->id) }}"
                                            class="text-blue-600 hover:text-blue-800 font-bold" title="Edit">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.korek.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus Kode Rekening ini? Jika kode ini sudah dipakai di Transaksi/RKAS, maka penghapusan akan ditolak otomatis oleh sistem.');"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-bold"
                                                title="Hapus">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="text-gray-400 font-bold text-lg uppercase tracking-widest mb-1">Data
                                        Tidak Ditemukan</div>
                                    <p class="text-gray-500 text-xs">Silakan tambahkan data kode rekening baru atau
                                        perbaiki kata kunci pencarian Anda.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4">
                {{ $koreks->links() }}
            </div>

        </div>
    </div>
</x-app-layout>