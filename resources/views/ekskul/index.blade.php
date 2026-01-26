<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Jurnal Kegiatan Ekskul') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Sumber Dana: <span class="text-indigo-600">{{ $belanja->uraian }}</span>
                </p>
            </div>

            <div class="flex gap-2">
                {{-- 1. Tombol Kembali --}}
                <a href="{{ route('belanja.index') }}"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm transition">
                    Kembali
                </a>

                {{-- 2. Tombol Edit SPJ (BARU) --}}
                <a href="{{ route('ekskul.edit', $spj->id) }}"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-bold flex items-center shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit SPJ
                </a>

                {{-- 3. Tombol Cetak Kwitansi --}}
                <a href="{{ route('ekskul.cetak', $spj->id) }}" target="_blank"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md text-sm font-bold flex items-center shadow transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak SPJ
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Message --}}
            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                {{ session('success') }}
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- KOLOM KIRI: Informasi Umum (Header SPJ) --}}
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b pb-2">Informasi Pelatih
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="text-gray-500 block">Nama Ekskul</label>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $spj->ekskul->nama
                                    }}</span>
                            </div>
                            <div>
                                <label class="text-gray-500 block">Nama Pelatih</label>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $spj->pelatih->nama
                                    }}</span>
                            </div>
                            <div>
                                <label class="text-gray-500 block">Triwulan (TW)</label>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">TW {{ $spj->tw }}</span>
                            </div>
                            <div>
                                <label class="text-gray-500 block">Status Pajak</label>
                                @if($spj->pph_persen == 5) {{-- Saya kembalikan ke 5% sesuai controller sebelumnya --}}
                                <span class="text-green-600 bg-green-100 px-2 py-1 rounded text-xs">NPWP (5%)</span>
                                @else
                                <span class="text-red-600 bg-red-100 px-2 py-1 rounded text-xs">Non-NPWP (6%)</span>
                                @endif
                            </div>
                            <hr>
                            <div>
                                <label class="text-gray-500 block">Total Honor Diterima</label>
                                <span class="font-bold text-xl text-indigo-600">Rp {{ number_format($spj->total_netto,
                                    0, ',', '.') }}</span>
                                <p class="text-xs text-gray-400 mt-1">({{ $spj->jumlah_pertemuan }} Pertemuan x Rp {{
                                    number_format($spj->honor, 0, ',', '.') }})</p>
                            </div>
                        </div>

                        {{-- Tombol Hapus Seluruh SPJ --}}
                        <div class="mt-6 pt-4 border-t">
                            <form action="{{ route('ekskul.destroy', $spj->id) }}" method="POST"
                                onsubmit="return confirm('Hapus seluruh data SPJ ini? Semua foto akan hilang.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full text-red-600 border border-red-600 hover:bg-red-50 py-2 rounded text-sm transition">
                                    Hapus SPJ Ini
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: Daftar Pertemuan (Detail) --}}
                <div class="md:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Pertemuan</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="py-3 px-4">No</th>
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-4">Materi Kegiatan</th>
                                        <th class="py-3 px-4 text-center">Dokumentasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($spj->details as $index => $detail)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="py-3 px-4">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($detail->tanggal_kegiatan)->translatedFormat('d F
                                            Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            {{ $detail->materi }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ asset('storage/'.$detail->foto_kegiatan) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$detail->foto_kegiatan) }}"
                                                    class="h-12 w-12 object-cover rounded border border-gray-300 mx-auto hover:scale-150 transition transform"
                                                    alt="Foto">
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($spj->details->isEmpty())
                        <p class="text-center text-gray-400 py-4">Belum ada data pertemuan.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>