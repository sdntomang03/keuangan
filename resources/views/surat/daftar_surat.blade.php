<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                Data Belanja
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- KOTAK FILTER --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6 overflow-x-auto">
                {{-- Ubah items-end jadi items-center, dan gap-4 jadi gap-3 agar lebih rapat --}}
                <form action="{{ route('surat.daftar') }}" method="GET"
                    class="flex flex-row items-center justify-between min-w-max gap-4">

                    {{-- BAGIAN KIRI: Filter (Label & Select) --}}
                    <div class="flex items-center gap-2">
                        <label class="whitespace-nowrap text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Filter:
                        </label>
                        {{-- Hapus flex-grow dan w-full pada select agar lebarnya menyesuaikan konten --}}
                        <select name="kode_rekening" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm w-48 sm:w-64">
                            <option value="">Semua Rekening</option>
                            @foreach($listKorek as $belanja)
                            <option value="{{ $belanja->korek->id }}" {{ request('kode_rekening')==$belanja->korek->id ?
                                'selected' : '' }}>
                                {{ $belanja->korek->singkat ?? 'Rincian Belanja' }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Tombol Terapkan nempel langsung dengan Filter --}}
                        <button type="submit"
                            class="bg-gray-800 hover:bg-gray-900 text-white px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                            Filter
                        </button>

                        @if(request('kode_rekening'))
                        <a href="{{ route('surat.daftar') }}"
                            class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                            X
                        </a>
                        @endif
                    </div>

                    {{-- BAGIAN KANAN: Kumpulan Tombol Aksi --}}
                    <div class="flex items-center gap-2 shrink-0 pl-4 border-l border-gray-200">

                        {{-- Tombol Rekap Surat --}}
                        <a href="{{ route('surat.rekap_triwulan') }}" target="_blank"
                            class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Rekap Surat</span>
                        </a>

                        {{-- Tombol Reset Nomor --}}
                        <a href="{{ route('surat.regenerate_all') }}"
                            onclick="return confirm('Apakah Anda yakin ingin mengurutkan ulang semua nomor surat? Tindakan ini tidak dapat dibatalkan.')"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Reset Nomor</span>
                        </a>
                        <a href="{{ route('surat.daftar_talangan_npd') }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Talangan-Npd</span>
                        </a>

                    </div>
                </form>
            </div>

            {{-- TABEL DATA BELANJA --}}
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead
                            class="bg-gray-100 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase text-[10px] font-bold tracking-widest">
                            <tr>
                                <th class="px-6 py-4 text-left">No</th>
                                <th class="px-6 py-4 text-left">Tanggal</th>
                                <th class="px-6 py-4 text-left">Uraian Belanja</th>
                                <th class="px-6 py-4 text-left">Kode Rekening</th>
                                <th class="px-6 py-4 text-right">Nilai (Rp)</th>
                                <th class="px-6 py-4 text-center">Dokumen Surat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($listBelanja as $belanja)

                            <tr class="hover:bg-indigo-50/50 dark:hover:bg-gray-700/50 transition duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">{{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">
                                    {{ \Carbon\Carbon::parse($belanja->tanggal)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $belanja->uraian }}</div>
                                    <div class="text-[10px] text-gray-400">Rekanan: {{ $belanja->rekanan->nama_rekanan
                                        ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $belanja->korek->singkat ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900 dark:text-gray-100">
                                    {{ number_format($belanja->subtotal + $belanja->ppn, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                    // Ambil data dan ubah ke huruf besar semua agar aman
                                    $singkatanKorek = strtoupper($belanja->korek->singkat ?? '');

                                    // Buat variabel boolean untuk kondisi-kondisi khusus
                                    $isNarasumber = ($singkatanKorek === 'NARASUMBER');
                                    $isTanpaSurat = in_array($singkatanKorek, ['INTERNET', 'TELEPON', 'LISTRIK']);
                                    @endphp

                                    @if($isTanpaSurat)
                                    {{-- KONDISI KHUSUS: Internet, Telepon, Listrik (Tombol Mati / Abu-abu) --}}
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-200 text-gray-500 border border-gray-300 rounded-md text-xs font-bold cursor-not-allowed shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                            </path>
                                        </svg>
                                        Tanpa Surat
                                    </span>

                                    @elseif($isNarasumber)
                                    {{-- Tombol Khusus Ekskul/Narasumber (Biru) --}}
                                    <a href="{{ route('ekskul.manage_details', $belanja->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white border border-transparent rounded-md text-xs font-bold transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                        Kelola Ekskul
                                    </a>

                                    @elseif($belanja->surats->count() > 0)
                                    {{-- Tombol Surat Sudah Ada (Hijau) --}}
                                    <a href="{{ route('surat.index', $belanja->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white border border-transparent rounded-md text-xs font-bold transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Kelola Surat ({{ $belanja->surats->count() }})
                                    </a>

                                    @else
                                    {{-- Tombol Belum Ada Surat (Oranye) --}}
                                    <a href="{{ route('surat.index', $belanja->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white border border-transparent rounded-md text-xs font-bold transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Belum Ada Surat
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center bg-white dark:bg-gray-800">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                        </path>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500">Tidak ada data belanja yang ditemukan.
                                    </p>
                                    @if(request('kode_rekening'))
                                    <p class="text-xs text-gray-400 mt-1">Coba pilih kode rekening lain atau reset
                                        filter.</p>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINASI --}}
                <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                    {{ $listBelanja->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
