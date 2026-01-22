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
                {{ __('Tambah Profil Instansi Baru') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8">

                {{-- Form mengarah ke method STORE --}}
                <form action="{{ route('admin.sekolah.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <x-input-label for="nama_sekolah" :value="__('Nama Sekolah')" />
                            {{-- Value hanya mengambil old() karena data baru --}}
                            <x-text-input name="nama_sekolah" type="text" class="mt-1 block w-full"
                                :value="old('nama_sekolah')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('nama_sekolah')" />
                        </div>

                        <div>
                            <x-input-label for="npsn" :value="__('NPSN')" />
                            <x-text-input name="npsn" type="text" class="mt-1 block w-full font-mono"
                                :value="old('npsn')" />
                            <x-input-error class="mt-2" :messages="$errors->get('npsn')" />
                        </div>

                        <div>
                            <x-input-label for="tahun" :value="__('Tahun Anggaran')" />

                            {{-- Input type number agar user hanya bisa memasukkan angka --}}
                            <x-text-input name="tahun" type="number" class="mt-1 block w-full"
                                placeholder="Contoh: 2024" :value="old('tahun', date('Y'))" min="2000" max="2099"
                                step="1" required />

                            <x-input-error class="mt-2" :messages="$errors->get('tahun')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 border-t pt-8">
                        <div>
                            <x-input-label for="email" :value="__('Email Sekolah')" />
                            <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label for="telp" :value="__('No. Telepon')" />
                            <x-text-input name="telp" type="text" class="mt-1 block w-full" :value="old('telp')" />
                            <x-input-error class="mt-2" :messages="$errors->get('telp')" />
                        </div>
                        <div>
                            <x-input-label for="logo" :value="__('Logo Instansi')" />
                            <input type="file" name="logo"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, SVG (Max. 2MB)</p>
                            <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        {{-- Box Kepala Sekolah --}}
                        <div class="p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                            <h3 class="text-sm font-bold text-blue-600 uppercase mb-4">Pejabat Kepala Sekolah</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-text-input name="nama_kepala_sekolah" placeholder="Nama Lengkap & Gelar"
                                        class="w-full text-sm" :value="old('nama_kepala_sekolah')" required />
                                    <x-input-error class="mt-1" :messages="$errors->get('nama_kepala_sekolah')" />
                                </div>
                                <div>
                                    <x-text-input name="nip_kepala_sekolah" placeholder="NIP" class="w-full text-sm"
                                        :value="old('nip_kepala_sekolah')" required />
                                    <x-input-error class="mt-1" :messages="$errors->get('nip_kepala_sekolah')" />
                                </div>
                            </div>
                        </div>

                        {{-- Box Bendahara --}}
                        <div class="p-6 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                            <h3 class="text-sm font-bold text-emerald-600 uppercase mb-4">Bendahara Pengeluaran</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-text-input name="nama_bendahara" placeholder="Nama Lengkap & Gelar"
                                        class="w-full text-sm" :value="old('nama_bendahara')" required />
                                    <x-input-error class="mt-1" :messages="$errors->get('nama_bendahara')" />
                                </div>
                                <div>
                                    <x-text-input name="nip_bendahara" placeholder="NIP" class="w-full text-sm"
                                        :value="old('nip_bendahara')" required />
                                    <x-input-error class="mt-1" :messages="$errors->get('nip_bendahara')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t pt-6">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-12 rounded-xl transition shadow-lg flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path> {{-- Icon Plus --}}
                            </svg>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>