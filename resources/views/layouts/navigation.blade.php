<nav x-data="{ open: false }"
    class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @unlessrole('admin')

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Anggaran') }}</div>
                                    <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('rkas.index')">{{ __('RKAS') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('akb.index')">{{ __('AKB') }}</x-dropdown-link>
                                <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                <div class="block px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                    Rincian & Format</div>
                                <x-dropdown-link :href="route('rkas.anggaran')">{{ __('Rincian Per Korek') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('akb.rincian')">{{ __('Rincian Per Komponen') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('akb.satuan')">{{ __('Format Excel') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('arkas.index')">{{ __('Format Arkas') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Transaksi') }}</div>
                                    <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('belanja.index')">{{ __('Input Belanja') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('talangan.create')">{{ __('Input Talangan') }}
                                </x-dropdown-link>
                                <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                <div class="block px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                    Ekstrakurikuler</div>
                                <x-dropdown-link :href="route('ekskul.index')">{{ __('Input Ekskul') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('ekskul.ref.index')">{{ __('Data Pelatih') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Pembukuan') }}</div>
                                    <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('bku.index')">{{ __('Buku Kas Umum (BKU)') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('npd.index')">{{ __('Nota Pencairan Dana (NPD)') }}
                                </x-dropdown-link>
                                <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                <div class="block px-4 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                    Realisasi Anggaran</div>
                                <x-dropdown-link :href="route('realisasi.komponen')">{{ __('Realisasi Per Komponen') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('realisasi.korek')">{{ __('Realisasi Per Korek') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Cetak Dokumen') }}</div>
                                    <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('surat.daftar')">{{ __('Persuratan SPJ') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('surat.cover_lpj.create')">{{ __('Cetak Cover LPJ') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('cetak.kop')" target="_blank">{{ __('Cetak Kop Surat') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('surat.rekap_triwulan')" target="_blank">{{ __('Rekap
                                    Surat Triwulan') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('realisasi.rekanan')">{{ __('Rekap Belanja Rekanan') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-600 dark:text-gray-300 hover:text-indigo-600 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Master Data') }}</div>
                                    <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('setting.rekanan.index')">{{ __('Data Rekanan') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('setting.kegiatan.index')">{{ __('Data Kegiatan') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endunlessrole
                    @role('admin')
                    <div class="hidden sm:flex sm:items-center border-l pl-2 dark:border-gray-700">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                    <div>{{ __('Admin') }}</div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.sekolah.index')">{{ __('Kelola Sekolah') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.users.index')">{{ __('Kelola Users') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endrole
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">

                @if(isset($anggaranAktif) && $anggaranAktif)
                <div
                    class="flex items-center bg-gray-100 dark:bg-gray-900 rounded-lg p-1 shadow-inner border border-gray-200 dark:border-gray-700">
                    <span class="text-[10px] font-bold uppercase px-2 text-gray-400 dark:text-gray-500">
                        {{ $anggaranAktif->tahun }}
                    </span>
                    <div class="flex space-x-1">
                        @foreach(['bos', 'bop'] as $item)
                        <form method="POST" action="{{ route('anggaran.switch') }}">
                            @csrf
                            <input type="hidden" name="singkatan" value="{{ $item }}">
                            <input type="hidden" name="tahun" value="{{ $anggaranAktif->tahun }}">
                            <button type="submit" @disabled($anggaranAktif->singkatan == $item)
                                class="px-3 py-1 text-[11px] font-extrabold rounded-md transition-all duration-200 {{
                                $anggaranAktif->singkatan == $item
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                                {{ strtoupper($item) }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center px-3 py-2 border border-gray-200 dark:border-gray-700 text-sm leading-4 font-semibold rounded-full text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                            <div
                                class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-2 font-bold text-xs">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-2 fill-current h-4 w-4 text-gray-400" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <x-dropdown-link :href="route('sekolah.index')">{{ __('Pengaturan Anggaran') }}
                        </x-dropdown-link>
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-red-600 hover:text-red-700">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden max-h-[80vh] overflow-y-auto">

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-gray-800 uppercase tracking-wider">
                Anggaran</div>
            <x-responsive-nav-link :href="route('rkas.index')">{{ __('RKAS') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('akb.index')">{{ __('AKB') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rkas.anggaran')">{{ __('Rincian Per Korek') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('akb.rincian')">{{ __('Rincian Per Komponen') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('arkas.index')">{{ __('Format Arkas') }}</x-responsive-nav-link>

            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-gray-800 uppercase tracking-wider">
                Transaksi & Ekskul</div>
            <x-responsive-nav-link :href="route('belanja.index')">{{ __('Input Belanja') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('talangan.create')">{{ __('Input Talangan') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ekskul.index')">{{ __('Input Ekskul') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ekskul.ref.index')">{{ __('Data Pelatih') }}</x-responsive-nav-link>

            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-gray-800 uppercase tracking-wider">
                Pembukuan & Realisasi</div>
            <x-responsive-nav-link :href="route('bku.index')">{{ __('Buku Kas Umum (BKU)') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('npd.index')">{{ __('Nota Pencairan Dana (NPD)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('realisasi.komponen')">{{ __('Realisasi Per Komponen') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('realisasi.korek')">{{ __('Realisasi Per Korek') }}
            </x-responsive-nav-link>

            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-gray-800 uppercase tracking-wider">
                Cetak Dokumen</div>
            <x-responsive-nav-link :href="route('surat.cover_lpj.create')">{{ __('Cetak Cover LPJ') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('realisasi.rekanan')">{{ __('Rekap Belanja Rekanan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cetak.kop')">{{ __('Cetak Kop Surat') }}</x-responsive-nav-link>

            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-gray-800 uppercase tracking-wider">
                Master Data</div>
            <x-responsive-nav-link :href="route('setting.rekanan.index')">{{ __('Data Rekanan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('setting.kegiatan.index')">{{ __('Data Kegiatan') }}
            </x-responsive-nav-link>

            @role('admin')
            <div
                class="block px-4 py-2 mt-2 text-xs font-black text-red-600 bg-red-50 dark:bg-red-900 uppercase tracking-wider">
                Admin Area</div>
            <x-responsive-nav-link :href="route('admin.sekolah.index')">{{ __('Kelola Sekolah') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.users.index')">{{ __('Kelola Users') }}</x-responsive-nav-link>
            @endrole
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email ?? '' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sekolah.index')">
                    {{ __('Pengaturan Anggaran') }}
                </x-responsive-nav-link>

                @if(isset($anggaranAktif) && $anggaranAktif)
                <div class="px-4 py-3 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-2">Ganti Anggaran ({{ $anggaranAktif->tahun }}):</p>
                    <div class="flex space-x-2">
                        @foreach(['bos', 'bop'] as $item)
                        <form method="POST" action="{{ route('anggaran.switch') }}" class="w-full">
                            @csrf
                            <input type="hidden" name="singkatan" value="{{ $item }}">
                            <input type="hidden" name="tahun" value="{{ $anggaranAktif->tahun }}">
                            <button type="submit" @disabled($anggaranAktif->singkatan == $item)
                                class="w-full py-2 text-xs font-bold rounded-md border {{
                                $anggaranAktif->singkatan == $item
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-gray-600 border-gray-300' }}">
                                {{ strtoupper($item) }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 font-bold">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
