<aside id="sidebar-manual"
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full md:translate-x-0 bg-white dark:bg-slate-800 border-r border-slate-200/60 dark:border-slate-700/60 flex flex-col"
    aria-label="Sidebar">

    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-700 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex flex-col">
            <span
                class="text-lg font-extrabold tracking-tight leading-none text-indigo-600 uppercase">SI-KEUANGAN</span>
            <span class="text-[10px] text-slate-500 font-medium tracking-widest uppercase mt-0.5">Modul
                Perencanaan</span>
        </a>
    </div>

    <div class="overflow-y-auto py-6 px-4 flex-1">
        <nav class="space-y-1">
            <p class="px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Master Data
            </p>

            <a href="{{ route('kegiatan.index') }}"
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('kegiatan.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('kegiatan.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-slate-500 dark:group-hover:text-slate-300' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Kegiatan
            </a>

            <a href="{{ route('komponen.import') }}"
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('komponen.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-white' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('komponen.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-slate-500 dark:group-hover:text-slate-300' }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Komponen
            </a>





        </nav>
    </div>

    <div class=" p-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
        <a href="{{ route('dashboard') }}"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400 transition-colors">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
            </svg>
            Aplikasi Inti
        </a>
    </div>
</aside>