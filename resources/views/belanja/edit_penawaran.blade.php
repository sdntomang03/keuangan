<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">

                {{-- Header --}}
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Input Data Penawaran & BAST</h2>
                        <p class="text-sm text-gray-500">Kegiatan: {{ $belanja->uraian }}</p>
                    </div>
                    <a href="{{ route('surat.index', $belanja->id) }}"
                        class="text-gray-500 hover:text-gray-700 text-sm font-bold">
                        &larr; Kembali
                    </a>
                </div>

                {{-- Form Update --}}
                <form action="{{ route('belanja.update_penawaran', $belanja->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- === 1. INPUT BAST (BARU) === --}}
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 mb-6">
                        <h3 class="text-sm font-bold text-indigo-800 mb-3 uppercase tracking-wide">Data Dokumen BAST /
                            Surat Jalan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Input No BAST --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor BAST /
                                    SJ</label>
                                <input type="text" name="no_bast" value="{{ old('no_bast', $belanja->no_bast) }}"
                                    placeholder="Contoh: 001/BAST/..."
                                    class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Input Tanggal BAST --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal BAST</label>
                                <input type="date" name="tanggal_bast"
                                    value="{{ old('tanggal_bast', $belanja->tanggal_bast) }}"
                                    class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                        </div>
                    </div>
                    {{-- === END INPUT BAST === --}}


                    {{-- === 2. TABEL RINCIAN === --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 w-10">No</th>
                                    <th class="px-4 py-3">Nama Barang</th>
                                    <th class="px-4 py-3 text-center">Volume</th>
                                    <th class="px-4 py-3 text-right">Harga Anggaran (Pagu)</th>
                                    <th class="px-4 py-3 text-right w-48 bg-blue-50">Harga Penawaran (Input)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($belanja->rincis as $index => $item)
                                <tr class="bg-white hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>

                                    <td class="px-4 py-3">
                                        <span class="font-bold text-gray-800 block">{{ $item->namakomponen ??
                                            $item->nama_komponen }}</span>
                                        <span class="text-xs text-gray-400">{{ $item->spek }}</span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        {{ $item->volume }} {{ $item->satuan }}
                                    </td>

                                    <td class="px-4 py-3 text-right text-gray-500">
                                        Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                    </td>

                                    {{-- KOLOM INPUT HARGA PENAWARAN --}}
                                    <td class="px-4 py-2 bg-blue-50">
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500 font-bold">Rp</span>
                                            <input type="number" name="items[{{ $item->id }}]"
                                                value="{{ $item->harga_penawaran ?? $item->harga_satuan }}"
                                                class="w-full pl-8 pr-2 py-1.5 text-right font-bold text-blue-800 border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                                placeholder="0">
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Actions --}}
                    <div
                        class="flex items-center justify-end bg-gray-50 p-4 -m-6 mt-0 rounded-b-xl border-t border-gray-100">
                        <div class="text-xs text-gray-500 mr-4">
                            *Data ini akan digunakan untuk dokumen BAPB dan Surat Pesanan.
                        </div>
                        <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-bold shadow-lg transform transition hover:scale-105">
                            Simpan Data
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>