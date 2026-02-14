<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Anggaran') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('rkas.index')">{{ __('RKAS') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('akb.index')">{{ __('AKB') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('rkas.anggaran')">{{ __('Rincian Per Korek') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('akb.rincian')">{{ __('Rincian Per Komponen') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('akb.satuan')">{{ __('Format Excel') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('arkas.index')">{{ __('Format Arkas') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">

                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Transaksi') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('belanja.index')">
                                    {{ __('Input Belanja') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('bku.index')">
                                    {{ __('BKU') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('cetak.kop')">
                                    {{ __('Cetak Kop Surat') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('realisasi.rekanan')">
                                    {{ __('Rekap Belanja Rekanan') }}

                                </x-dropdown-link>

                                <div class="border-t border-gray-100 dark:border-gray-600"></div>

                                {{-- SUB MENU REALISASI --}}
                                <div class="relative" x-data="{ openSub: false }" @mouseenter="openSub = true"
                                    @mouseleave="openSub = false">
                                    <button
                                        class="w-full text-start flex justify-between items-center block w-full px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        <span>{{ __('Realisasi') }}</span>
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>

                                    <div x-show="openSub" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute left-full top-0 w-48 ml-2 origin-top-left rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">

                                        <div class="py-1">
                                            <x-dropdown-link :href="route('realisasi.komponen')">
                                                {{ __('Per Komponen') }}
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('realisasi.korek')">
                                                {{ __('Per Korek') }}
                                            </x-dropdown-link>
                                        </div>
                                    </div>
                                </div>

                                {{-- SUB MENU EKSKUL (BARU) --}}
                                <div class="relative" x-data="{ openSubEkskul: false }"
                                    @mouseenter="openSubEkskul = true" @mouseleave="openSubEkskul = false">
                                    <button
                                        class="w-full text-start flex justify-between items-center block w-full px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        <span>{{ __('Ekskul') }}</span>
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>

                                    <div x-show="openSubEkskul" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute left-full top-0 w-48 ml-2 origin-top-left rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">

                                        <div class="py-1">
                                            {{-- Ganti route ini sesuai route ekskul anda --}}
                                            <x-dropdown-link :href="route('ekskul.index')">
                                                {{ __('Input Ekskul') }}
                                            </x-dropdown-link>

                                            <x-dropdown-link :href="route('ekskul.ref.index')">
                                                {{ __('Pelatih Ekskul') }}
                                            </x-dropdown-link>
                                        </div>
                                    </div>
                                </div>
                                {{-- END SUB MENU EKSKUL --}}

                                <div class="border-t border-gray-100 dark:border-gray-600"></div>



                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">

                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Setting') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('setting.rekanan.index')">
                                    {{ __('Rekanan') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('setting.kegiatan.index')">
                                    {{ __('Kegiatan') }}
                                </x-dropdown-link>


                                <div class="border-t border-gray-100 dark:border-gray-600"></div>

                                <div class="relative" x-data="{ openSub: false }" @mouseenter="openSub = true"
                                    @mouseleave="openSub = false">

                                    <button
                                        class="w-full text-start flex justify-between items-center block w-full px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        <span>{{ __('Realisasi') }}</span>
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>

                                    <div x-show="openSub" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute left-full top-0 w-48 ml-2 origin-top-left rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">

                                        <div class="py-1">
                                            <x-dropdown-link :href="route('realisasi.komponen')">
                                                {{ __('Per Komponen') }}
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('realisasi.korek')">
                                                {{ __('Per Korek') }}
                                            </x-dropdown-link>

                                        </div>
                                    </div>

                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    @role('admin')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white dark:bg-gray-800 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Admin') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                {{-- Gunakan .index untuk membuka halaman daftar/tabel --}}
                                <x-dropdown-link :href="route('admin.sekolah.index')">
                                    {{ __('Sekolah') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('admin.users.index')">
                                    {{ __('Users') }}
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
                                {{ $item }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif

                <x-dropdown align="left" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <x-dropdown-link :href="route('sekolah.index')">{{ __('Pengaturan Anggaran') }}
                        </x-dropdown-link>
                        <hr class="dark:border-gray-700">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition duration-150 ease-in-out">
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
</nav>
