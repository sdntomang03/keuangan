<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Instansi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah" value="{{ $setting->nama_sekolah ?? '' }}" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Anggaran Aktif</label>
                                <select name="tahun_aktif" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @php $currentYear = date('Y'); @endphp
                                    @for($i = $currentYear - 1; $i <= $currentYear + 1; $i++)
                                        <option value="{{ $i }}" {{ ($setting->tahun_aktif ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Triwulan / Tahap Aktif</label>
                                <select name="triwulan_aktif" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @for($tw = 1; $tw <= 4; $tw++)
                                        <option value="{{ $tw }}" {{ ($setting->triwulan_aktif ?? '') == $tw ? 'selected' : '' }}>
                                            Triwulan {{ $tw }} (Tahap {{ $tw }})
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <h3 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Kepala Sekolah
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Nama Lengkap</label>
                                        <input type="text" name="nama_kepala_sekolah" value="{{ $setting->nama_kepala_sekolah ?? '' }}" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">NIP</label>
                                        <input type="text" name="nip_kepala_sekolah" value="{{ $setting->nip_kepala_sekolah ?? '' }}" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <h3 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Bendahara Sekolah
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Nama Lengkap</label>
                                        <input type="text" name="nama_bendahara" value="{{ $setting->nama_bendahara ?? '' }}" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">NIP</label>
                                        <input type="text" name="nip_bendahara" value="{{ $setting->nip_bendahara ?? '' }}" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sumber Anggaran Aktif</label>
                            <select name="anggaran_aktif" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="bos" {{ ($setting->anggaran_aktif ?? '') == 'bos' ? 'selected' : '' }}>DANA BOS (Bantuan Operasional Sekolah)</option>
                                <option value="bop" {{ ($setting->anggaran_aktif ?? '') == 'bop' ? 'selected' : '' }}>DANA BOP (Bantuan Operasional Penyelenggaraan)</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-10 rounded-lg transition shadow-md flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Seluruh Pengaturan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>