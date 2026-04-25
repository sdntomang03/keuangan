<x-manual-layout>
    <div class="max-w-4xl mx-auto">

        <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white uppercase tracking-tight">
                    Import Master Kegiatan (JSON)
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-1">SI-KEUANGAN - Tarik Data Kegiatan Perencanaan</p>
            </div>
        </div>

        @if (session('success'))
        <div
            class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg flex items-start">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5 mr-3 shrink-0" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm font-medium text-emerald-800 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-lg">
            <ul class="list-disc ml-4 space-y-1 text-sm font-medium text-rose-800">
                @foreach ($errors->messages() as $field => $messages)
                @foreach ($messages as $message)
                <li>Field <b>{{ $field }}</b> bermasalah: {{ $message }}</li>
                @endforeach
                @endforeach
            </ul>
        </div>
        @endif

        <div
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <form action="{{ route('manual.import.kegiatan') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6 md:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="sumber_dana_id"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Sumber Dana <span class="text-rose-500">*</span>
                            </label>
                            <select name="sumber_dana_id" id="sumber_dana_id" required
                                class="block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-colors">
                                <option value="">-- Pilih Sumber Dana --</option>
                                @foreach($sumberDanas as $sd)
                                <option value="{{ $sd->id }}">{{ $sd->kode }} - {{ $sd->nama }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Data kegiatan yang diimpor akan otomatis menggunakan sumber dana ini.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="file_json"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                File Data JSON <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" name="file_json" id="file_json" accept=".json,application/json" required
                                class="block w-full text-sm text-slate-600 dark:text-slate-300
                   file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                   file:text-sm file:font-semibold file:transition-colors
                   file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100
                   dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:hover:file:bg-indigo-500/20
                   cursor-pointer border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 shadow-sm">
                        </div>
                    </div>

                    <div class="mt-8 space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Unit
                            Sekolah</label>
                        <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
                        <input type="text"
                            value="{{ auth()->user()->sekolah->nama_sekolah ?? 'Nama sekolah tidak ditemukan' }}"
                            readonly
                            class="block w-full rounded-lg border-slate-300 bg-slate-100 text-slate-500 font-semibold cursor-not-allowed dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400 shadow-sm text-sm">
                    </div>
                    <div
                        class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-5 border border-slate-200 dark:border-slate-700">
                        <h4
                            class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            Format JSON yang Diharapkan
                        </h4>

                        <div
                            class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-800 p-4">
                            <pre class="text-xs text-emerald-400 font-mono whitespace-pre overflow-x-auto">
[
    {
        "id_kegiatan": "622529",
        "standar_pendidikan": "Pengembangan sarana dan prasarana sekolah",
        "sumber_dana": "3.01 BOS Pusat",
        "nama_kegiatan": "04.3.01.6.001 05.09. Penyediaan Alat Multi Media Pembelajaran"
    },
    {
        "id_kegiatan": "625028",
        ...
    }
]
</pre>
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Mulai Import JSON
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-manual-layout>