<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Halaman AKB') }}
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

                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">
                                Mode Kerja Aktif:
                                <strong class="text-blue-700 dark:text-blue-300">
                                    {{ $anggaranAktif->nama_anggaran ?? 'Belum Diatur' }}
                                    ({{ strtoupper($anggaranAktif->singkatan ?? '-') }} Tahun {{ $anggaranAktif->tahun ?? '-' }})
                                </strong>
                            </span>
                        </div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 ml-7 italic">
                            * Semua data yang diimpor atau digenerate akan masuk ke anggaran ini.
                        </p>
                    </div>

                    <form action="{{ route('akb.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Upload File JSON AKB</label>
                            <input type="file" name="json_files[]" multiple required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>

                        <div class="flex flex-wrap gap-4 mt-6 items-center">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                Simpan Data
                            </button>

                            <a href="{{ route('akb.generate') }}"
                               onclick="return confirm('Apakah Anda yakin ingin menjalankan proses generate rincian untuk {{ $anggaranAktif->singkatan ?? 'anggaran' }} {{ $anggaranAktif->tahun ?? '' }}? Proses ini akan menghapus rincian lama pada anggaran tersebut.')"
                               class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Generate Rincian Bulanan
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
