<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <nav class="flex mb-4 text-xs font-semibold uppercase tracking-widest text-slate-400">
            <a href="{{ route('setting.kegiatan.index') }}" class="hover:text-indigo-600 transition-colors">Daftar
                Kegiatan</a>
            <span class="mx-2">/</span>
            <span class="text-slate-600 dark:text-slate-300">Import JSON</span>
        </nav>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white uppercase tracking-tight">Import Master
                Kegiatan</h2>
            <p class="text-sm text-slate-500 font-medium mt-1">Unggah file JSON untuk memecah data ke dalam Standar
                Pendidikan, Sub Program, dan Uraian.</p>
        </div>

        @if (session('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-medium text-emerald-800 dark:text-emerald-300 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-lg text-sm font-medium text-rose-800 shadow-sm">
            <div class="flex items-center mb-2 font-bold">
                <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Terjadi Kesalahan:
            </div>
            <ul class="list-disc ml-7 space-y-1">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 lg:p-8 h-fit">
                <form action="{{ route('setting.kegiatan.store_import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6 text-center">
                        <div
                            class="mx-auto w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-4 border border-indigo-100 dark:border-indigo-800/50">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Upload File JSON</h3>
                        <p class="text-xs text-slate-500 mt-1">Maksimal ukuran file 10MB.</p>
                    </div>

                    <div class="mb-6">
                        <label for="file_json" class="sr-only">Pilih File JSON</label>
                        <input type="file" name="file_json" id="file_json" accept=".json" required
                            class="block w-full text-sm text-slate-500 dark:text-slate-400
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                dark:file:bg-indigo-900/30 dark:file:text-indigo-400
                                hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50
                                border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-900/50">
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-colors flex justify-center items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Mulai Proses Import
                    </button>

                    <a href="{{ route('setting.kegiatan.index') }}"
                        class="block text-center w-full mt-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                        Batal & Kembali
                    </a>
                </form>
            </div>

            <div
                class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 lg:p-8">
                <h3
                    class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Petunjuk Format JSON
                </h3>

                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-height-relaxed">
                    Pastikan file JSON yang diunggah memiliki struktur <i>array of objects</i> dengan <i>keys</i> yang
                    sama persis seperti contoh di bawah ini:
                </p>

                <div class="bg-slate-900 rounded-lg p-4 overflow-x-auto relative shadow-inner">
                    <div class="absolute top-2 right-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Contoh.json</div>
                    <pre class="text-xs text-emerald-400 font-mono leading-relaxed">
[
  {
    <span class="text-sky-300">"program"</span>: <span class="text-amber-300">"Pengembangan standar pembiayaan"</span>,
    <span class="text-sky-300">"sub_program"</span>: <span class="text-amber-300">"Pelaksanaan Administrasi Sekolah"</span>,
    <span class="text-sky-300">"id_kegiatan"</span>: <span class="text-amber-300">"04.3.01.6.001"</span>,
    <span class="text-sky-300">"uraian"</span>: <span class="text-amber-300">"Pencetakan Ijazah"</span>
  },
  {
    <span class="text-sky-300">"program"</span>: <span class="text-amber-300">"Standar Sarana dan Prasarana"</span>,
    <span class="text-sky-300">"sub_program"</span>: <span class="text-amber-300">"Pengembangan Perpustakaan"</span>,
    <span class="text-sky-300">"id_kegiatan"</span>: <span class="text-amber-300">"05.09.01"</span>,
    <span class="text-sky-300">"uraian"</span>: <span class="text-amber-300">"Pemeliharaan buku perpustakaan"</span>
  }
]
</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>