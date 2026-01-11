<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Anggaran') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight">
                                    {{ $setting->nama_sekolah ?? 'NAMA SEKOLAH BELUM DIATUR' }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Tahun Anggaran Aktif: <span class="ml-1 font-bold text-indigo-600 dark:text-indigo-400">{{ $setting->tahun_aktif ?? '-' }}</span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                            Pengaturan Profil
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 pt-8 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm mr-4">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kepala Sekolah</span>
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $setting->nama_kepala_sekolah ?? 'Belum Diisi' }}</h4>
                                <p class="text-xs text-gray-500">NIP. {{ $setting->nip_kepala_sekolah ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm mr-4">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Bendahara</span>
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $setting->nama_bendahara ?? 'Belum Diisi' }}</h4>
                                <p class="text-xs text-gray-500">NIP. {{ $setting->nip_bendahara ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Anggaran BOS</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $stats['total_bos'] }} <span class="text-sm font-normal text-gray-400">Komponen</span></h3>
            </div>
            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase">BOS</span>
        </div>
        
        <div class="space-y-2 border-t pt-4">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Total Harga</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format($stats['harga_bos'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Total Pajak</span>
                <span class="text-sm font-bold text-red-500">Rp {{ number_format($stats['pajak_bos'], 0, ',', '.') }}</span>
            </div>
              <a href="{{ route('akb.indexrincian', ['jenis_anggaran' => 'bos']) }}" class="hover:underline flex items-center">
                            Lihat Rincian <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Anggaran BOP</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $stats['total_bop'] }} <span class="text-sm font-normal text-gray-400">Komponen</span></h3>
            </div>
            <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-[10px] font-bold uppercase">BOP</span>
        </div>

        <div class="space-y-2 border-t pt-4">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Total Harga</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format($stats['harga_bop'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Total Pajak</span>
                <span class="text-sm font-bold text-red-500">Rp {{ number_format($stats['pajak_bop'], 0, ',', '.') }}</span>
            </div>
              <a href="{{ route('akb.indexrincian', ['jenis_anggaran' => 'bop']) }}" class="hover:underline flex items-center">
                            Lihat Rincian <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
        </div>
    </div>
</div>
        </div>
    </div>
</x-app-layout>