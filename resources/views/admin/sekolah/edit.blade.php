<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sekolah.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Profil Instansi: ') }} <span class="text-indigo-600">{{ $setting->nama_sekolah }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8">

                <form action="{{ route('admin.sekolah.update', $setting->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <x-input-label for="nama_sekolah" :value="__('Nama Sekolah')" />
                            <x-text-input name="nama_sekolah" type="text" class="mt-1 block w-full"
                                :value="old('nama_sekolah', $setting->nama_sekolah)" required />
                        </div>

                        <div>
                            <x-input-label for="npsn" :value="__('NPSN')" />
                            <x-text-input name="npsn" type="text" class="mt-1 block w-full font-mono"
                                :value="old('npsn', $setting->npsn)" />
                        </div>

                        <div>
                            <x-input-label for="anggaran_id_aktif" :value="__('Anggaran Aktif')" />
                            <select name="anggaran_id_aktif" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih Anggaran --</option>
                                @foreach($anggarans as $anggaran)
                                <option value="{{ $anggaran->id }}" {{ $anggaran->is_aktif ? 'selected' : '' }}>
                                    {{ $anggaran->tahun }} - {{ strtoupper($anggaran->singkatan) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 border-t pt-8">
                        <div>
                            <x-input-label for="email" :value="__('Email Sekolah')" />
                            <x-text-input name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $setting->email)" />
                        </div>
                        <div>
                            <x-input-label for="telp" :value="__('No. Telepon')" />
                            <x-text-input name="telp" type="text" class="mt-1 block w-full"
                                :value="old('telp', $setting->telp)" />
                        </div>
                        <div>
                            <x-input-label for="logo" :value="__('Logo Instansi (Kosongkan jika tidak diubah)')" />
                            <input type="file" name="logo"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @if($setting->logo)
                            <p class="mt-2 text-[10px] text-gray-400">Logo saat ini: <a
                                    href="{{ asset('storage/'.$setting->logo) }}" target="_blank"
                                    class="underline text-indigo-500">Lihat File</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                            <h3 class="text-sm font-bold text-blue-600 uppercase mb-4">Pejabat Kepala Sekolah</h3>
                            <div class="space-y-4">
                                <x-text-input name="nama_kepala_sekolah" placeholder="Nama Lengkap & Gelar"
                                    class="w-full text-sm"
                                    :value="old('nama_kepala_sekolah', $setting->nama_kepala_sekolah)" required />
                                <x-text-input name="nip_kepala_sekolah" placeholder="NIP" class="w-full text-sm"
                                    :value="old('nip_kepala_sekolah', $setting->nip_kepala_sekolah)" required />
                            </div>
                        </div>

                        <div class="p-6 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                            <h3 class="text-sm font-bold text-emerald-600 uppercase mb-4">Bendahara Pengeluaran</h3>
                            <div class="space-y-4">
                                <x-text-input name="nama_bendahara" placeholder="Nama Lengkap & Gelar"
                                    class="w-full text-sm" :value="old('nama_bendahara', $setting->nama_bendahara)"
                                    required />
                                <x-text-input name="nip_bendahara" placeholder="NIP" class="w-full text-sm"
                                    :value="old('nip_bendahara', $setting->nip_bendahara)" required />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t pt-6">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-12 rounded-xl transition shadow-lg flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Cek Session Success
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif

        // Cek Session Error (Manual dari Controller)
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        @endif

        // TAMBAHAN PENTING: Cek Error Validasi Form
        @if($errors->any())
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Terdapat kesalahan pengisian data. Silakan cek form kembali.',
            });
        @endif
    </script>
</x-app-layout>