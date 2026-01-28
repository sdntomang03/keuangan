<x-app-layout>
    <x-slot name="header">
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">
                    {{ __('Manajemen Belanja') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Kelola data transaksi, rincian komponen, dan laporan pajak dalam
                    satu dasbor.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('belanja.export') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-emerald-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 transition shadow-md hover:shadow-lg gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </a>

                <a href="{{ route('belanja.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition shadow-md hover:shadow-lg gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Transaksi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">

                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-800 dark:bg-gray-900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-[10px] font-bold text-gray-300 uppercase tracking-widest">
                                        Info Transaksi</th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-bold text-gray-300 uppercase tracking-widest">
                                        Sub Total</th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-bold text-blue-400 uppercase tracking-widest">
                                        PPN</th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-bold text-red-400 uppercase tracking-widest">
                                        PPh</th>
                                    <th
                                        class="px-4 py-3 text-right text-[10px] font-bold text-green-400 uppercase tracking-widest">
                                        Nilai SPJ</th>
                                    <th
                                        class="px-4 py-3 text-center text-[10px] font-bold text-gray-300 uppercase tracking-widest">
                                        Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($belanjas as $belanja)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-4" x-data="{ expanded: false }">
                                        <div class="flex flex-col space-y-1">
                                            <div class="flex justify-between items-start mb-1">
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{
                                                        $belanja->no_bukti }}</div>
                                                    <div class="text-[10px] text-gray-500 uppercase">{{
                                                        \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F
                                                        Y') }}</div>
                                                </div>
                                                <span
                                                    class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md text-[9px] font-black italic uppercase">{{
                                                    $belanja->status }}</span>
                                            </div>

                                            <div class="space-y-0.5">
                                                <div class="text-[11px] text-indigo-600 font-bold uppercase">{{
                                                    $belanja->rekanan->nama_rekanan ?? 'Internal' }}</div>
                                                <div class="text-[11px] text-gray-700 leading-relaxed">{{
                                                    $belanja->uraian ?? '-' }}</div>
                                            </div>

                                            <button @click="expanded = !expanded"
                                                class="flex items-center text-[10px] font-bold text-gray-400 hover:text-indigo-500 mt-1 focus:outline-none">
                                                <svg class="w-3 h-3 mr-1 transition-transform duration-300"
                                                    :class="expanded ? 'rotate-180' : ''" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                                <span
                                                    x-text="expanded ? 'Sembunyikan Detail' : 'Lihat Detail Akun & Kegiatan'"></span>
                                            </button>

                                            <div x-show="expanded" x-collapse x-cloak>
                                                <div
                                                    class="mt-2 p-3 bg-gray-50 dark:bg-slate-800/50 border-l-4 border-indigo-500 rounded-r-xl space-y-3 shadow-inner">

                                                    <div>
                                                        <span
                                                            class="block text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Kode
                                                            Rekening</span>
                                                        @php
                                                        $korek = \DB::table('koreks')->where('id',
                                                        $belanja->kodeakun)->first();
                                                        @endphp
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="text-[10px] font-medium text-gray-600 uppercase">{{
                                                                $korek->ket ?? 'Nama Rekening Tidak Ditemukan' }}</span>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Kegiatan
                                                            (IDBL: {{ $belanja->idbl }})</span>
                                                        <p
                                                            class="text-[11px] font-bold text-gray-800 leading-tight uppercase">
                                                            @php
                                                            $kegiatan = \DB::table('kegiatans')->where('idbl',
                                                            $belanja->idbl)->first();
                                                            @endphp
                                                            {{ $kegiatan->namagiat ?? 'Data Kegiatan Tidak Ditemukan' }}
                                                        </p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        {{ number_format($belanja->subtotal, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-4 text-right text-sm text-blue-600 dark:text-blue-400 font-bold">
                                        {{ $belanja->ppn > 0 ? number_format($belanja->ppn, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-right text-sm text-red-600 dark:text-red-400 font-bold">
                                        {{ $belanja->pph > 0 ? number_format($belanja->pph, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">
                                            Rp {{ number_format($belanja->subtotal + $belanja->ppn, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <div class="flex justify-center items-center space-x-3">
                                            <a href="{{ route('belanja.show', $belanja->id) }}"
                                                class="text-gray-400 hover:text-blue-600 transition-colors"
                                                title="Lihat Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            @if($belanja->status == 'posted')
                                            <a href="{{ route('belanja.print', $belanja->id) }}"
                                                title="Cetak Dokumen Word"
                                                class="text-gray-400 hover:text-blue-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                            @if ($belanja->korek->singkat === 'NARASUMBER')

                                            {{-- TOMBOL KELOLA JURNAL KEGIATAN --}}
                                            <a href="{{ route('ekskul.manage_details', $belanja->id) }}"
                                                title="Input Jurnal & Foto Kegiatan"
                                                class="text-gray-400 hover:text-indigo-600 transition-colors mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                </svg>
                                            </a>
                                            @else
                                            {{-- TOMBOL SURAT STANDAR (Original) --}}
                                            <a href="{{ route('surat.index', $belanja->id) }}"
                                                title="Input & Kelola Surat"
                                                class="text-gray-400 hover:text-blue-600 transition-colors mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                            @endif
                                            @endif

                                            @if($belanja->status == 'draft')
                                            <a href="{{ route('belanja.edit', $belanja->id) }}"
                                                class="text-gray-400 hover:text-blue-600 transition-colors"
                                                title="Edit Transaksi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('belanja.destroy', $belanja->id) }}" method="POST"
                                                id="delete-form-{{ $belanja->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete('{{ $belanja->id }}')"
                                                    class="text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif

                                            @if($belanja->status == 'draft')
                                            <form action="{{ route('belanja.post', $belanja->id) }}" method="POST"
                                                class="inline-block">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('Posting belanja ini ke BKU? Data tidak akan bisa diedit setelah diposting.')"
                                                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold py-1.5 px-3 rounded-lg shadow-sm transition-all duration-200 active:scale-95">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8">
                                                        </path>
                                                    </svg>
                                                    Posting
                                                </button>
                                            </form>
                                            @else
                                            <div
                                                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg border border-emerald-100 text-[11px] font-bold">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Posted
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span class="text-gray-500 text-sm">Belum ada data transaksi yang
                                                dicatat.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 px-6 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $belanjas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Transaksi?',
            text: "Data rincian belanja ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626', // Warna merah (red-600)
            cancelButtonColor: '#6b7280',  // Warna abu-abu (gray-500)
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true, // Tombol batal di kiri, hapus di kanan
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading saat proses hapus
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghapus data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                // Submit form
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
    </script>
</x-app-layout>
