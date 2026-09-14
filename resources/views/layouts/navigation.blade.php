<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden"
    @click="sidebarOpen = false"></div>

<aside :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-0 lg:-translate-x-full'"
    class="fixed lg:static inset-y-0 left-0 z-50 flex flex-col h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out overflow-hidden flex-shrink-0 shadow-2xl lg:shadow-none">

    <div class="w-64 flex flex-col h-full">

        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                <span class="ml-3 font-black text-2xl text-gray-800 dark:text-white tracking-tight">KEUANGAN</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto custom-scroll p-4 space-y-2">

            {{-- ======================================================= --}}
            {{-- ZONA EKSTRAKURIKULER --}}
            {{-- ======================================================= --}}
            @can('input-ekskul')
            <div class="mb-4 space-y-1 pb-4 border-b border-gray-100 dark:border-gray-700">
                <p class="px-3 text-[10px] font-black tracking-wider text-indigo-400 uppercase mb-2">Panel Pelatih</p>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard Ekskul
                </a>
                <a href="{{ route('ekskul.laporan.index') }}"
                    class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('ekskul.laporan.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('ekskul.laporan.index') ? 'text-indigo-600' : 'text-gray-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Input Laporan
                </a>

            </div>
            @endcan

            {{-- ======================================================= --}}
            {{-- ZONA KEUANGAN & PEMBUKUAN (View & Kelola) --}}
            {{-- ======================================================= --}}
            @canany(['view-anggaran', 'kelola-anggaran'])

            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-400' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('barang.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('barang.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('barang.index') ? 'text-indigo-600' : 'text-gray-400' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                    </path>
                </svg>
                Cari Barang
            </a>

            {{-- Dropdown Anggaran --}}
            <div x-data="{ open: {{ request()->routeIs('rkas.*', 'akb.*', 'arkas.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-bold text-gray-600 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Anggaran
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;"
                    class="pl-11 pr-2 py-1 space-y-1">
                    <a href="{{ route('rkas.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('rkas.index') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">RKAS</a>
                    <a href="{{ route('akb.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('akb.index') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">AKB</a>
                    <a href="{{ route('rkas.anggaran') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('rkas.anggaran') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rincian
                        Per Korek</a>
                    <a href="{{ route('akb.rincian') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('akb.rincian') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rincian
                        Per Komponen</a>
                    <a href="{{ route('rkas.rekap') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('rkas.rekap') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rekap
                        RKAS</a>
                    <a href="{{ route('akb.satuan') }}"
                        class="block py-1.5 text-sm text-gray-500 hover:text-indigo-500">Format Excel</a>
                    <a href="{{ route('arkas.index') }}" target="_blank" rel="noopener noreferrer"
                        class="block py-1.5 text-sm text-gray-500 hover:text-indigo-500">Format Arkas ↗</a>
                    <a href="{{ route('rkas.cetak_laporan') }}" target="_blank" rel="noopener noreferrer"
                        class="block py-1.5 text-sm text-gray-500 hover:text-indigo-500">Cetak RKAS ↗</a>
                    <a href="{{ route('akb.perbandingan.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('akb.perbandingan.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Perbandingan
                        AKB</a>
                </div>
            </div>

            {{-- Dropdown Transaksi --}}
            @can('input-belanja')
            <div x-data="{ open: {{ request()->routeIs('belanja.*', 'talangan.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-bold text-gray-600 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Transaksi Belanja
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;"
                    class="pl-11 pr-2 py-1 space-y-1">

                    <a href="{{ route('belanja.index') }}"
                        class="block px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('belanja.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                        Input Belanja
                    </a>

                    <a href="{{ route('talangan.create') }}"
                        class="block px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('talangan.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                        Input Talangan
                    </a>

                    <a href="{{ route('ekskul.index') }}"
                        class="block px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('ekskul.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                        Kelola Ekskul
                    </a>

                    <a href="{{ route('ekskul.ref.index') }}"
                        class="block px-3 py-2.5 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('ekskul.ref.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                        Data Pelatih
                    </a>
                </div>
            </div>
            @endcan
            {{-- Dropdown Pembukuan --}}
            <div
                x-data="{ open: {{ request()->routeIs('bku.*', 'npd.*', 'realisasi.*', 'pajak.*', 'sts.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-bold text-gray-600 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                            </path>
                        </svg>
                        Pembukuan
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;"
                    class="pl-11 pr-2 py-1 space-y-1">
                    <a href="{{ route('bku.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('bku.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Buku
                        Kas Umum (BKU)</a>
                    <a href="{{ route('npd.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('npd.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Nota
                        Pencairan (NPD)</a>
                    <a href="{{ route('realisasi.komponen') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('realisasi.komponen') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Realisasi
                        Per Komponen</a>
                    <a href="{{ route('realisasi.korek') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('realisasi.korek') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Realisasi
                        Per Korek</a>
                    <a href="{{ route('realisasi.jenis-belanja') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('realisasi.jenis-belanja') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Realisasi
                        Per Jenis</a>
                    <a href="{{ route('pajak.rekap') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('pajak.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rekap
                        Pajak</a>
                    <a href="{{ route('sts.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('sts.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Surat
                        Tanda Setoran (STS)</a>
                    <a href="{{ route('realisasi.spj.view') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('realisasi.spj.view') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rekap
                        Belanja</a>
                </div>
            </div>

            {{-- Dropdown Cetak Dokumen --}}
            <div
                x-data="{ open: {{ request()->routeIs('surat.*', 'cetak.*', 'persediaan.*', 'realisasi.rekanan') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-bold text-gray-600 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak Dokumen
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;"
                    class="pl-11 pr-2 py-1 space-y-1">

                    <a href="{{ route('penomoran-surat.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('penomoran-surat.index') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Penomoran</a>
                    <a href="{{ route('surat.daftar') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('surat.daftar') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Persuratan
                        SPJ</a>
                    <a href="{{ route('surat.cover_lpj.create') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('surat.cover_lpj.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Cetak
                        Cover LPJ</a>
                    <a href="{{ route('cetak.kop') }}" target="_blank"
                        class="block py-1.5 text-sm text-gray-500 hover:text-indigo-500">Cetak Kop Surat ↗</a>
                    <a href="{{ route('surat.rekap_triwulan') }}" target="_blank"
                        class="block py-1.5 text-sm text-gray-500 hover:text-indigo-500">Rekap Triwulan ↗</a>
                    <a href="{{ route('realisasi.rekanan') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('realisasi.rekanan') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Rekap
                        Rekanan</a>
                    <a href="{{ route('persediaan.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('persediaan.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Persediaan</a>
                </div>
            </div>

            {{-- Perencanaan --}}
            <div class="mt-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('perencanaan.dashboard') }}"
                    class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg border transition-all duration-300 {{ request()->routeIs('perencanaan.*', 'kegiatan.*') ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm' : 'border-gray-200 text-gray-700 hover:border-indigo-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <div class="relative flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('perencanaan.*', 'kegiatan.*') ? 'text-indigo-600' : 'text-gray-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        @if(request()->routeIs('perencanaan.*', 'kegiatan.*'))
                        <span class="absolute -top-1 -right-1 flex h-2 w-2"><span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span><span
                                class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span></span>
                        @endif
                    </div>
                    Perencanaan Manual
                </a>
                <a href="{{ route('catatan.index') }}"
                    class="flex items-center px-3 py-2.5 text-sm font-bold rounded-lg border transition-all duration-300 {{ request()->routeIs('catatan.*') ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm' : 'border-gray-200 text-gray-700 hover:border-indigo-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <div class="relative flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('catatan.*') ? 'text-indigo-600' : 'text-gray-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        @if(request()->routeIs('catatan.*'))
                        <span class="absolute -top-1 -right-1 flex h-2 w-2"><span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span><span
                                class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span></span>
                        @endif
                    </div>
                    Catatan
                </a>
            </div>
            @endcanany


            {{-- ======================================================= --}}
            {{-- ZONA MANAJEMEN (Hanya Kelola Anggaran) --}}
            {{-- ======================================================= --}}
            @can('kelola-anggaran')
            {{-- Dropdown Master Data --}}
            <div
                x-data="{ open: {{ request()->routeIs('setting.rekanan.*', 'setting.kegiatan.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="flex items-center justify-between w-full px-3 py-2.5 text-sm font-bold text-gray-600 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        Master Data
                    </div>
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;"
                    class="pl-11 pr-2 py-1 space-y-1">
                    <a href="{{ route('setting.rekanan.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('setting.rekanan.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Data
                        Rekanan</a>
                    <a href="{{ route('setting.kegiatan.index') }}"
                        class="block py-1.5 text-sm {{ request()->routeIs('setting.kegiatan.*') ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-500' }}">Data
                        Kegiatan</a>
                </div>
            </div>
            @endcan


            {{-- ======================================================= --}}
            {{-- ZONA SUPER ADMIN --}}
            {{-- ======================================================= --}}
            @role('admin')
            <div class="mt-6 border-t border-red-100 dark:border-red-900/30 pt-5">

                <p class="px-4 text-[10px] font-black tracking-wider text-red-400 uppercase mb-3">Admin Area</p>

                {{-- Daftar Menu Langsung --}}
                <div class="px-2 space-y-1">
                    @php
                    $adminMenus = [
                    ['route' => 'admin.sekolah.index', 'label' => 'Kelola Sekolah', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2
                    2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011
                    1v5m-4 0h4'],
                    ['route' => 'admin.users.index', 'label' => 'Kelola Users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15
                    21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['route' => 'admin.korek.index', 'label' => 'Kode Rekening', 'icon' => 'M7 7h.01M7 3h5c.512 0
                    1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0
                    014-4z'],
                    ['route' => 'setting.kegiatan.importjson', 'label' => 'Import JSON', 'icon' => 'M4 16v1a3 3 0 003
                    3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                    ['route' => 'admin.anggaran.index', 'label' => 'Generate Anggaran', 'icon' => 'M9 7h6m0 10v-3m-3
                    3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0
                    00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['route' => 'admin.dasar-pajak.index', 'label' => 'Master Pajak', 'icon' => 'M9
                    14l6-6m-5.5.5h.01m4.9 4.9h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10
                    8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z'],
                    ['route' => 'admin.rkas.cleanup', 'label' => 'Hapus Anggaran', 'icon' => 'M19 7l-.867 12.142A2 2 0
                    0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4
                    7h16'],
                    ];
                    @endphp

                    @foreach($adminMenus as $menu)
                    <a href="{{ route($menu['route']) }}"
                        class="relative flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group
                      {{ request()->routeIs($menu['route'])
                         ? 'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-500/20 font-bold shadow-sm'
                         : 'text-gray-600 hover:text-red-600 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-500/10' }}">

                        {{-- Ikon Menu --}}
                        <svg class="w-5 h-5 {{ request()->routeIs($menu['route']) ? 'text-red-600 dark:text-red-400' : 'text-gray-400 group-hover:text-red-500' }} transition-colors"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $menu['icon'] }}"></path>
                        </svg>

                        {{ $menu['label'] }}
                    </a>
                    @endforeach

                </div>
            </div>
            @endrole

        </div>

        {{-- ======================================================= --}}
        {{-- TOMBOL GANTI ANGGARAN (GLOBAL) --}}
        {{-- ======================================================= --}}
        @if(isset($anggaranAktif) && $anggaranAktif)
        <div
            class="sm:hidden border-t border-gray-200 dark:border-gray-700 p-4 shrink-0 bg-gray-50 dark:bg-gray-800/80">
            <p class="text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wider">Ganti Anggaran ({{
                $anggaranAktif->tahun }})</p>
            <div class="flex space-x-2">
                @foreach(['bos', 'bop'] as $item)
                <form method="POST" action="{{ route('anggaran.switch') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="singkatan" value="{{ $item }}">
                    <input type="hidden" name="tahun" value="{{ $anggaranAktif->tahun }}">
                    <button type="submit" @disabled($anggaranAktif->singkatan == $item)
                        class="w-full py-2 text-xs font-bold rounded-md border transition {{
                        $anggaranAktif->singkatan == $item
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                        : 'bg-white text-gray-600 border-gray-300 dark:bg-gray-800 dark:border-gray-600
                        dark:text-gray-300' }}">
                        {{ strtoupper($item) }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</aside>