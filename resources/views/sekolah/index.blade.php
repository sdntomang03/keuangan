<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Instansi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('sekolah.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                                    Sekolah</label>
                                <input type="text" name="nama_sekolah" value="{{ $setting->nama_sekolah ?? '' }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NPSN</label>
                                <input type="text" name="npsn" value="{{ $setting->npsn ?? '' }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anggaran &
                                    Tahun Aktif</label>
                                <select name="anggaran_id_aktif" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Pilih Anggaran --</option>
                                    @foreach($anggarans as $anggaran)
                                    <option value="{{ $anggaran->id }}" {{ $anggaran->is_aktif ? 'selected' : '' }}>
                                        {{ $anggaran->tahun }} - {{ strtoupper($anggaran->singkatan) }} ({{
                                        $anggaran->nama_anggaran }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email
                                    Sekolah</label>
                                <input type="email" name="email" value="{{ $setting->email ?? '' }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No.
                                    Telepon</label>
                                <input type="text" name="telp" value="{{ $setting->telp ?? '' }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo
                                    Sekolah</label>
                                <input type="file" name="logo"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>

                        <div
                            class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 mb-6 bg-gray-50 dark:bg-gray-800/30">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xs font-bold text-gray-400 uppercase">Detail Lokasi</h3>
                                <button type="button" onclick="getLocation()"
                                    class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-1 rounded-md hover:bg-indigo-600 hover:text-white transition-all font-bold">
                                    📍 AMBIL LOKASI GPS
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Baris 1: Alamat Jalan (Full Width) --}}
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Alamat
                                        Jalan</label>
                                    <input type="text" name="alamat" value="{{ $setting->alamat ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                </div>

                                {{-- Baris 2: Kelurahan & Kecamatan --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kelurahan</label>
                                    <input type="text" name="kelurahan" value="{{ $setting->kelurahan ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kecamatan</label>
                                    <input type="text" name="kecamatan" value="{{ $setting->kecamatan ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                </div>

                                {{-- Baris 3: Kota & Kode Pos --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kota</label>
                                    <input type="text" name="kota" value="{{ $setting->kota ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kode
                                        Pos</label>
                                    <input type="text" name="kodepos" value="{{ $setting->kodepos ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                                </div>

                                {{-- Baris 4: Latitude & Longitude --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Latitude</label>
                                    <input type="text" id="lat" name="latitude" value="{{ $setting->latitude ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm font-mono text-indigo-600">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Longitude</label>
                                    <input type="text" id="lng" name="longitude" value="{{ $setting->longitude ?? '' }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm font-mono text-indigo-600">
                                </div>

                                {{-- Baris 5: Triwulan Aktif (Full Width) --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                        Triwulan Aktif
                                    </label>
                                    <select name="triwulan_aktif" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @for($tw = 1; $tw <= 4; $tw++) <option value="{{ $tw }}" {{ ($setting->
                                            triwulan_aktif ?? '') == $tw ? 'selected' : '' }}>
                                            Triwulan {{ $tw }}
                                            </option>
                                            @endfor
                                    </select>
                                </div>

                                {{-- 2. Nomor Surat (Posisi di Samping Triwulan) --}}
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                                        Nomor Surat
                                    </label>
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <input type="number" name="nomor_surat"
                                            value="{{ $setting->nomor_surat ?? '001' }}" placeholder="001"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-gray-400 sm:text-xs">/UD.02.02 </span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-[10px] text-gray-400">Nomor awal surat triwulan aktif</p>
                                </div>

                            </div> {{-- Penutup Grid --}}
                        </div>
                </div>
            </div>



            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

                <div
                    class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shadow-sm hover:shadow-md transition">
                    <h3
                        class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase mb-4 flex items-center border-b border-gray-200 pb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Kepala Sekolah
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama
                                Lengkap</label>
                            <input type="text" name="nama_kepala_sekolah"
                                value="{{ $setting->nama_kepala_sekolah ?? '' }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                            <input type="text" name="nip_kepala_sekolah"
                                value="{{ $setting->nip_kepala_sekolah ?? '' }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <div
                    class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shadow-sm hover:shadow-md transition">
                    <h3
                        class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-4 flex items-center border-b border-gray-200 pb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Bendahara Sekolah
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama
                                Lengkap</label>
                            <input type="text" name="nama_bendahara" value="{{ $setting->nama_bendahara ?? '' }}"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                            <input type="text" name="nip_bendahara" value="{{ $setting->nip_bendahara ?? '' }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        </div>
                    </div>
                </div>

                <div
                    class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shadow-sm hover:shadow-md transition">
                    <h3
                        class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase mb-4 flex items-center border-b border-gray-200 pb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Pengurus Barang
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama
                                Lengkap</label>
                            <input type="text" name="nama_pengurus_barang"
                                value="{{ $setting->nama_pengurus_barang ?? '' }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                            <input type="text" name="nip_pengurus_barang"
                                value="{{ $setting->nip_pengurus_barang ?? '' }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-10 rounded-lg transition shadow-md flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Data
                </button>
            </div>
            </form>
        </div>
    </div>
    </div>
    </div>
    {{-- Script untuk ambil koordinat otomatis --}}
    <script>
        function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat').value = position.coords.latitude;
                document.getElementById('lng').value = position.coords.longitude;
            }, function(error) {
                alert("Gagal mengambil lokasi. Pastikan izin GPS aktif.");
            });
        } else {
            alert("Browser Anda tidak mendukung Geolocation.");
        }
    }
    </script>
</x-app-layout>