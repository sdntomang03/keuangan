<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Daftar SPJ Honor Ekstrakurikuler') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Anggaran Aktif: <span class="font-bold text-indigo-600">{{ $anggaran->nama_anggaran }}</span>
                </p>
            </div>
            <a href="{{ route('ekskul.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                + Input Honor Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Sukses/Error --}}
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Tgl / No. Bukti</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Uraian Transaksi</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Penerima (Pelatih)</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Nominal (Netto)</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Kelengkapan</th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($belanjas as $belanja)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                    {{-- Tanggal & No Bukti --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $belanja->no_bukti }}
                                        </div>
                                        {{-- Badge Status --}}
                                        @if($belanja->status == 'posted')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                            BKU Posted
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                            Draft
                                        </span>
                                        @endif
                                    </td>

                                    {{-- Uraian --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                            {{ $belanja->uraian }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Illuminate\Support\Str::limit($belanja->rincian, 50) }}
                                        </div>
                                    </td>

                                    {{-- Penerima --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $belanja->rekanan->nama_rekanan ?? '-' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $belanja->rekanan->npwp ? 'NPWP: Ada' : 'Non-NPWP' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Nominal --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        {{-- Ambil Netto dari SPJ Ekskul jika ada, jika tidak hitung manual --}}
                                        @php
                                        $netto = $belanja->spjEkskul->total_netto ?? ($belanja->subtotal -
                                        $belanja->pph);
                                        @endphp
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($netto, 0, ',', '.') }}
                                        </div>
                                        @if($belanja->pph > 0)
                                        <div class="text-xs text-red-500">
                                            (PPh: {{ number_format($belanja->pph, 0, ',', '.') }})
                                        </div>
                                        @endif
                                    </td>

                                    {{-- Status Kelengkapan (Jurnal) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($belanja->spjEkskul)
                                        @php
                                        $jmlInput = $belanja->spjEkskul->details->count();
                                        $target = $belanja->spjEkskul->jumlah_pertemuan;
                                        $persen = $target > 0 ? ($jmlInput / $target) * 100 : 0;
                                        $color = $jmlInput >= $target ? 'bg-green-500' : 'bg-orange-400';
                                        @endphp
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                            <div class="{{ $color }} h-2.5 rounded-full" style="width: {{ $persen }}%">
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $jmlInput }} / {{ $target }} Pertemuan
                                        </div>
                                        @else
                                        <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">

                                            {{-- 1. INPUT JURNAL (DETAIL) --}}
                                            <a href="{{ route('ekskul.manage_details', $belanja->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded hover:bg-indigo-100 transition"
                                                title="Input Jurnal & Foto">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                </svg>
                                            </a>

                                            {{-- 2. CETAK KWITANSI --}}
                                            @if($belanja->spjEkskul)

                                            {{-- 1. TOMBOL CETAK KWITANSI (Abu-abu) --}}
                                            <a href="{{ route('ekskul.cetak', $belanja->spjEkskul->id) }}"
                                                target="_blank"
                                                class="text-gray-600 hover:text-gray-900 bg-gray-100 p-2 rounded hover:bg-gray-200 transition"
                                                title="Cetak Kwitansi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>

                                            {{-- 2. TOMBOL CETAK ABSENSI & DOKUMENTASI (Biru) --}}
                                            <a href="{{ route('ekskul.cetak_absensi', $belanja->spjEkskul->id) }}"
                                                target="_blank"
                                                class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition ml-2"
                                                title="Cetak Absensi & Dokumentasi">
                                                {{-- Ikon Clipboard List (Absensi) --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                            </a>

                                            @endif

                                            {{-- 3. EDIT (Jika belum diposting) --}}
                                            @if($belanja->status != 'posted')
                                            <a href="{{ route('belanja.edit', $belanja->id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 p-2 rounded hover:bg-yellow-100 transition"
                                                title="Edit Transaksi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            @endif

                                            {{-- 4. HAPUS --}}
                                            @if($belanja->status != 'posted')
                                            <form action="{{ route('ekskul.destroy', $belanja->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus data ini? Data jurnal dan foto juga akan terhapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded hover:bg-red-100 transition"
                                                    title="Hapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        Belum ada data Honor Ekskul untuk anggaran ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $belanjas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
