<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pengaturan Instansi') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('sekolah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div
                    class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl mb-6 border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            Identitas Sekolah
                        </h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div
                            class="lg:col-span-1 flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 text-center">
                                Logo Instansi
                            </label>

                            <div class="mb-4 flex justify-center w-full">
                                @if(isset($setting) && $setting->logo)
                                <img id="preview-logo" src="{{ asset('storage/' . $setting->logo) }}"
                                    alt="Logo Tersimpan"
                                    class="w-32 h-32 object-contain rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm bg-white p-2">
                                @else
                                <img id="preview-logo" src="#" alt="Preview Logo"
                                    class="hidden w-32 h-32 object-contain rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm bg-white p-2">
                                @endif
                            </div>

                            <input type="file" name="logo" id="logo-input" accept="image/*"
                                onchange="previewImage(this)"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer">
                        </div>

                        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah" value="{{ $setting->nama_sekolah ?? '' }}"
                                    required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NPSN</label>
                                <input type="text" name="npsn" value="{{ $setting->npsn ?? '' }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Suku Dinas
                                    (Sudin)</label>
                                <select name="sudin" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Suku Dinas --</option>
                                    @foreach($sudins as $sudin)
                                    <option value="{{ $sudin->id }}" {{ ($setting->sudin ?? '') == $sudin->id ?
                                        'selected' : '' }}>{{ $sudin->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email
                                    Sekolah</label>
                                <input type="email" name="email" value="{{ $setting->email ?? '' }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Telepon</label>
                                <input type="text" name="telp" value="{{ $setting->telp ?? '' }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl mb-6 border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div
                        class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase flex items-center">
                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Alamat & Koordinat
                        </h3>
                        <button type="button" onclick="getLocation()"
                            class="text-xs bg-red-50 text-red-600 px-3 py-1.5 rounded-md hover:bg-red-600 hover:text-white transition-all font-bold flex items-center shadow-sm border border-red-100">
                            📍 AMBIL LOKASI GPS
                        </button>
                    </div>

                    <div
                        class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 bg-gray-50/50 dark:bg-gray-800/30">
                        <div class="md:col-span-2 lg:col-span-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alamat Lengkap</label>
                            <input type="text" name="alamat" value="{{ $setting->alamat ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kelurahan</label>
                            <input type="text" name="kelurahan" value="{{ $setting->kelurahan ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ $setting->kecamatan ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kota/Kabupaten</label>
                            <input type="text" name="kota" value="{{ $setting->kota ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Pos</label>
                            <input type="text" name="kodepos" value="{{ $setting->kodepos ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-1 lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Latitude</label>
                            <input type="text" id="lat" name="latitude" value="{{ $setting->latitude ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 font-mono text-indigo-600 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-1 lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Longitude</label>
                            <input type="text" id="lng" name="longitude" value="{{ $setting->longitude ?? '' }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 font-mono text-indigo-600 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl mb-6 border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800/80 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase flex items-center">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Pengaturan Sistem
                        </h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Anggaran Aktif</label>
                            <select name="anggaran_id_aktif" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Pilih Anggaran --</option>
                                @foreach($anggarans as $anggaran)
                                <option value="{{ $anggaran->id }}" {{ $anggaran->is_aktif ? 'selected' : '' }}>
                                    {{ $anggaran->tahun }} - {{ strtoupper($anggaran->singkatan) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Triwulan Aktif</label>
                            <select name="triwulan_aktif" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                                @for($tw = 1; $tw <= 4; $tw++) <option value="{{ $tw }}" {{ ($setting->triwulan_aktif ??
                                    '') == $tw ? 'selected' : '' }}>Triwulan {{ $tw }}</option>
                                    @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Format Penomoran
                                Surat</label>
                            <div class="flex items-stretch shadow-sm">
                                <input type="text" name="nomor_surat" value="{{ $setting->nomor_surat ?? '001' }}"
                                    placeholder="001"
                                    class="w-16 rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 text-center font-mono border-r-0">
                                <div
                                    class="flex items-center bg-gray-100 dark:bg-gray-800 border-y border-gray-300 dark:border-gray-700 px-3 text-gray-500 font-mono">
                                    /</div>
                                <input type="text" name="kode_surat" value="{{ $setting->kode_surat ?? 'UD.02.02' }}"
                                    placeholder="UD.02.02"
                                    class="flex-1 w-full rounded-r-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div
                        class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition">
                        <h3
                            class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase mb-4 flex items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Kepala Sekolah
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_kepala_sekolah"
                                    value="{{ $setting->nama_kepala_sekolah ?? '' }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                                <input type="text" name="nip_kepala_sekolah"
                                    value="{{ $setting->nip_kepala_sekolah ?? '' }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition">
                        <h3
                            class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase mb-4 flex items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Bendahara
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_bendahara" value="{{ $setting->nama_bendahara ?? '' }}"
                                    required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                                    <input type="text" name="nip_bendahara" value="{{ $setting->nip_bendahara ?? '' }}"
                                        required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bank</label>
                                    <input type="text" name="bank_bendahara"
                                        value="{{ $setting->bank_bendahara ?? '' }}" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div class="col-span-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No.
                                        Rekening</label>
                                    <input type="text" name="no_rekening" value="{{ $setting->no_rekening ?? '' }}"
                                        required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition">
                        <h3
                            class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase mb-4 flex items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Pengurus Barang
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_pengurus_barang"
                                    value="{{ $setting->nama_pengurus_barang ?? '' }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">NIP</label>
                                <input type="text" name="nip_pengurus_barang"
                                    value="{{ $setting->nip_pengurus_barang ?? '' }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-blue-500 focus:border-blue-500 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end sticky bottom-6 z-10">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 text-white font-bold py-3 px-10 rounded-full transition-all shadow-lg flex items-center transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Pengaturan
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview-logo');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('lng').value = position.coords.longitude;
                    alert("Lokasi berhasil diambil!");
                }, function(error) {
                    alert("Gagal mengambil lokasi. Pastikan izin GPS aktif.");
                });
            } else {
                alert("Browser Anda tidak mendukung Geolocation.");
            }
        }
    </script>
</x-app-layout>
