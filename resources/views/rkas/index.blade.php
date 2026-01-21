<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Halaman RKAS') }}
            </h2>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full dark:bg-indigo-900 dark:text-indigo-200 shadow-sm border border-indigo-200">
                    {{ strtoupper($anggaranAktif->singkatan ?? '-') }} {{ $anggaranAktif->tahun ?? '' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">
                                Target Import RKAS:
                                <strong class="text-emerald-700 dark:text-emerald-300">
                                    {{ $anggaranAktif->nama_anggaran ?? 'Belum Diatur' }}
                                    ({{ strtoupper($anggaranAktif->singkatan ?? '-') }} Tahun {{ $anggaranAktif->tahun ?? '-' }})
                                </strong>
                            </span>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 ml-7 italic">
                            * Pastikan file JSON yang Anda upload sesuai dengan sumber dana {{ $anggaranAktif->singkatan ?? 'yang dipilih' }}.
                        </p>
                    </div>

                    <form action="{{ route('rkas.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Upload File JSON RKAS (Bisa multiple)</label>
                            <input type="file" name="json_files[]" multiple required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Simpan Data RKAS
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
