<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Halaman RKAS') }}
            </h2>
            <div class="flex items-center space-x-2">
                <span
                    class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full dark:bg-indigo-900 dark:text-indigo-200 shadow-sm border border-indigo-200">
                    {{ strtoupper($anggaranAktif->singkatan ?? '-') }} {{ $anggaranAktif->tahun ?? '' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ========================================================= --}}
            {{-- AREA PESAN ALERT (SUCCESS, ERROR, WARNING) --}}
            {{-- ========================================================= --}}
            <div class="mb-6">
                {{-- Alert Success --}}
                @if (session('success'))
                <div
                    class="bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex justify-between items-start">
                    <div class="flex">
                        <svg class="w-6 h-6 text-emerald-500 mr-3 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3
                                class="text-emerald-800 dark:text-emerald-200 font-bold text-sm uppercase tracking-wider">
                                Sukses</h3>
                            <div class="text-emerald-700 dark:text-emerald-300 text-sm mt-1">{!! session('success') !!}
                            </div>
                        </div>
                    </div>
                    <button onclick="this.parentElement.style.display='none'"
                        class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @endif

                {{-- Alert Error --}}
                @if (session('error'))
                <div
                    class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex justify-between items-start">
                    <div class="flex">
                        <svg class="w-6 h-6 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-red-800 dark:text-red-200 font-bold text-sm uppercase tracking-wider">
                                Kesalahan (Error)</h3>
                            <div class="text-red-700 dark:text-red-300 text-sm mt-1 leading-relaxed">{!!
                                session('error') !!}</div>
                        </div>
                    </div>
                    <button onclick="this.parentElement.style.display='none'"
                        class="text-red-500 hover:text-red-700 dark:hover:text-red-300 flex-shrink-0 ml-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @endif

                {{-- Alert Warning (Kuning) --}}
                @if (session('warning'))
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded-r-lg shadow-sm flex justify-between items-start">
                    <div class="flex">
                        <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <div>
                            <h3 class="text-yellow-800 dark:text-yellow-200 font-bold text-sm uppercase tracking-wider">
                                Perhatian Peringatan</h3>
                            <div class="text-yellow-700 dark:text-yellow-300 text-sm mt-1 leading-relaxed">{!!
                                session('warning') !!}</div>
                        </div>
                    </div>
                    <button onclick="this.parentElement.style.display='none'"
                        class="text-yellow-600 hover:text-yellow-800 dark:hover:text-yellow-300 flex-shrink-0 ml-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div
                        class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium">
                                Target Import RKAS:
                                <strong class="text-emerald-700 dark:text-emerald-300">
                                    {{ $anggaranAktif->nama_anggaran ?? 'Belum Diatur' }}
                                    ({{ strtoupper($anggaranAktif->singkatan ?? '-') }} Tahun {{ $anggaranAktif->tahun
                                    ?? '-' }})
                                </strong>
                            </span>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 ml-7 italic">
                            * Pastikan file JSON yang Anda upload sesuai dengan sumber dana {{ $anggaranAktif->singkatan
                            ?? 'yang dipilih' }}.
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
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
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