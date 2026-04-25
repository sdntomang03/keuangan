<x-manual-layout>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white uppercase tracking-tight">
                Import Master Komponen (JSON)
            </h2>
            <p class="text-sm text-slate-500 font-medium mt-1">Sistem akan otomatis mencocokkan Uraian Rekening dengan
                database Korek Anda.</p>
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
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <form action="{{ route('komponen.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6 md:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Unit
                                Sekolah</label>
                            <input type="hidden" name="school_id"
                                value="{{ auth()->user()->sekolah_id ?? auth()->user()->school_id }}">
                            <input type="text"
                                value="{{ auth()->user()->sekolah->nama_sekolah ?? 'Nama sekolah tidak ditemukan' }}"
                                readonly
                                class="block w-full rounded-lg border-slate-300 bg-slate-100 text-slate-500 font-semibold cursor-not-allowed dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400 shadow-sm text-sm">
                        </div>

                        <div class="space-y-2">
                            <label for="file_json"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                File Data Komponen JSON (Bisa Pilih Banyak File) <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" name="file_json[]" id="file_json" accept=".json,application/json"
                                multiple required
                                class="block w-full text-sm text-slate-600 dark:text-slate-300
                file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                file:text-sm file:font-semibold file:transition-colors
                file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100
                dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:hover:file:bg-indigo-500/20
                cursor-pointer border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 shadow-sm">
                        </div>
                    </div>

                    <div
                        class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-5 border border-slate-200 dark:border-slate-700">
                        <h4
                            class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            Struktur JSON yang Diharapkan
                        </h4>

                        <div
                            class="rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-800 p-4">
                            <pre class="text-xs text-amber-400 font-mono whitespace-pre overflow-x-auto">
{
    "kode_rekening": "5.1.02.01.01.0012",
    "uraian_rekening": "Belanja Bahan-Bahan Lainnya",
    "daftar_komponen": [
        {
            "id_komponen": "2",
            "nama": "Pasir",
            "spesifikasi": "Abu Batu",
            "satuan": "M3",
            "harga": "261100"
        }
    ]
}
</pre>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 italic">* Catatan: Anda juga bisa mengunggah Array JSON
                            <code>[ { ... }, { ... } ]</code> yang berisi banyak kode rekening sekaligus.
                        </p>
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 transition-colors">
                        Mulai Import Komponen
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-manual-layout>