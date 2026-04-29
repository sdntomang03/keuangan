<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Modern Minimalist Header with Breadcrumbs integrated --}}
        <div class="mb-10 pb-6 border-b border-slate-200 dark:border-slate-700">
            <div
                class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-600 dark:text-slate-400">Pengaturan Admin</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight flex items-center">
                        <span class="w-1.5 h-7 bg-indigo-600 rounded-full mr-3"></span>
                        Otomatisasi Anggaran Sekolah
                    </h2>
                    <p class="text-slate-500 font-medium mt-1 ml-4.5">Generate struktur BOS & BOP untuk semua sekolah
                        terdaftar secara massal.</p>
                </div>
            </div>
        </div>

        {{-- Sophisticated Alerts --}}
        @if (session('success') || session('info'))
        <div
            class="mb-8 p-6 bg-white dark:bg-slate-800 border {{ session('success') ? 'border-emerald-100' : 'border-sky-100' }} rounded-2xl shadow-sm overflow-hidden relative">
            {{-- Accent bar --}}
            <div class="absolute inset-y-0 left-0 w-1.5 {{ session('success') ? 'bg-emerald-500' : 'bg-sky-500' }}">
            </div>

            <div class="flex items-start pl-3">
                <div class="{{ session('success') ? 'text-emerald-500' : 'text-sky-500' }} shrink-0 mr-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="w-full">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                        {{ session('success') ?? session('info') }}
                    </h3>

                    @if(session('skipped_schools') && count(session('skipped_schools')) > 0)
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-wider font-bold mb-3 text-slate-500 dark:text-slate-400">
                            Sekolah yang dilewati (sudah memiliki anggaran):
                        </p>
                        <div class="max-h-36 overflow-y-auto pr-2 custom-scrollbar">
                            <div class="flex flex-wrap gap-2.5">
                                @foreach(session('skipped_schools') as $sekolah)
                                <span
                                    class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/50 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-inner">
                                    {{ $sekolah }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if ($errors->any() || session('error'))
        <div
            class="mb-8 p-6 bg-rose-50 border-2 border-rose-100 rounded-2xl text-sm font-medium text-rose-800 shadow-sm">
            <div class="flex items-center mb-3">
                <svg class="w-6 h-6 text-rose-500 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-base font-bold text-rose-900">Gagal Memproses Permintaan</h3>
            </div>
            <ul class="list-disc ml-8 space-y-1 text-rose-700">
                @if(session('error')) <li>{{ session('error') }}</li> @endif
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        {{-- Main Grid - 1/3 and 2/3 ratio for better balance --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Left: Configuration Panel (Span 1 column) --}}
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-7 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col">
                <form action="{{ route('admin.anggaran.generate') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="flex items-center gap-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center border border-indigo-100 dark:border-indigo-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Form Eksekusi</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Pengaturan tahun anggaran</p>
                        </div>
                    </div>

                    {{-- Scaled Down Input Year --}}
                    <div
                        class="p-5 bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-xl shadow-inner">
                        <label for="tahun" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2.5">
                            Pilih Tahun Anggaran
                        </label>
                        <div class="relative">
                            <input type="number" name="tahun" id="tahun" required placeholder="YYYY"
                                value="{{ date('Y') }}"
                                class="block w-full text-center text-3xl font-extrabold tracking-tight text-indigo-700 bg-white border border-slate-200 dark:border-slate-700 rounded-lg py-4 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 dark:bg-slate-900 dark:text-indigo-400 transition shadow-sm">
                            {{-- Visual cue --}}
                            <div class="absolute inset-y-0 right-3 flex items-center pr-1 text-slate-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin mengeksekusi generate anggaran massal? Proses ini mungkin memerlukan waktu.')"
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-base rounded-xl shadow-md transition transform hover:-translate-y-0.5 flex justify-center items-center group">
                        Eksekusi Massal
                        <svg class="w-5 h-5 ml-2.5 transform group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </button>
                    <p class="text-[11px] text-center text-slate-400 italic">Pengecekan duplikasi dilakukan otomatis</p>
                </form>
            </div>

            {{-- Right: Information Panel (Span 2 columns) --}}
            <div
                class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-7 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-between">
                <div>
                    <h3
                        class="text-xl font-bold text-slate-900 dark:text-white mb-8 pb-4 border-b border-slate-100 dark:border-slate-700 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ringkasan Mekanisme Sistem
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-7">
                        {{-- Mechanism 1 --}}
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 rounded-xl flex items-center justify-center mr-4 border border-sky-100 dark:border-sky-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Generate 2 Template
                                    Otomatis</h4>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Sistem membuatkan slot anggaran
                                    Dana BOS dan BOP untuk seluruh sekolah terdaftar.</p>
                            </div>
                        </div>

                        {{-- Mechanism 2 --}}
                        <div class="flex items-start">
                            <div
                                class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mr-4 border border-emerald-100 dark:border-emerald-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Proteksi Duplikasi
                                    Ganda</h4>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Jika sekolah sudah memiliki
                                    struktur anggaran di tahun tersebut, sistem akan otomatis melewatinya.</p>
                            </div>
                        </div>

                        {{-- Mechanism 3 --}}
                        <div class="flex items-start sm:col-span-2">
                            <div
                                class="w-10 h-10 bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mr-4 border border-amber-100 dark:border-amber-900 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Status Awal Non-Aktif
                                </h4>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Anggaran baru dibuat berstatus
                                    <code
                                        class="bg-slate-100 px-1.5 py-0.5 rounded text-xs text-rose-600 font-mono">is_aktif = false</code>.
                                    Admin sekolah masing-masing harus mengaktifkannya di menu pengaturan anggaran.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Visual summary at the bottom --}}
                <div
                    class="mt-10 p-4 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-center space-x-6 text-sm text-slate-600">
                    <span class="flex items-center"><span
                            class="w-2.5 h-2.5 bg-indigo-500 rounded-full mr-2"></span>Dana BOS</span>
                    <span class="text-slate-300">|</span>
                    <span class="flex items-center"><span class="w-2.5 h-2.5 bg-sky-500 rounded-full mr-2"></span>Dana
                        BOP</span>
                    <span class="text-slate-300">|</span>
                    <span class="flex items-center font-semibold text-slate-800"><svg
                            class="w-4 h-4 mr-1.5 text-emerald-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>Pengecekan Massal</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Tailored CSS for the specific scrollbar --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(203, 213, 225, 0.7);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(148, 163, 184, 0.9);
        }
    </style>
</x-app-layout>