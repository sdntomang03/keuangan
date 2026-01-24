<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Rekanan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('setting.rekanan.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4">Identitas & Alamat</h3>

                                <div>
                                    <x-input-label for="nama_rekanan" :value="__('Nama Rekanan / Toko *')" />
                                    <x-text-input id="nama_rekanan" class="block mt-1 w-full" type="text"
                                        name="nama_rekanan" :value="old('nama_rekanan')" required autofocus />
                                    <x-input-error :messages="$errors->get('nama_rekanan')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="no_telp" :value="__('No. Telepon / HP')" />
                                    <x-text-input id="no_telp" class="block mt-1 w-full" type="text" name="no_telp"
                                        :value="old('no_telp')" />
                                </div>

                                <div>
                                    <x-input-label for="alamat" :value="__('Alamat Lengkap')" />
                                    <textarea id="alamat" name="alamat"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="2">{{ old('alamat') }}</textarea>
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="alamat2" :value="__('Alamat Lanjutan (Opsional)')" />
                                    <textarea id="alamat2" name="alamat2"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="2" placeholder="Kelurahan, Kecamatan">{{ old('alamat2') }}</textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="kota" :value="__('Kota / Kab')" />
                                        <x-text-input id="kota" class="block mt-1 w-full" type="text" name="kota"
                                            :value="old('kota')" />
                                    </div>
                                    <div>
                                        <x-input-label for="provinsi" :value="__('Provinsi')" />
                                        <x-text-input id="provinsi" class="block mt-1 w-full" type="text"
                                            name="provinsi" :value="old('provinsi')" />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4">Personalia & Keuangan</h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="pic" :value="__('Nama PIC / Kontak')" />
                                        <x-text-input id="pic" class="block mt-1 w-full" type="text" name="pic"
                                            :value="old('pic')" />
                                    </div>
                                    <div>
                                        <x-input-label for="nama_pimpinan" :value="__('Nama Pimpinan (TTD)')" />
                                        <x-text-input id="nama_pimpinan" class="block mt-1 w-full" type="text"
                                            name="nama_pimpinan" :value="old('nama_pimpinan')" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="nama_bank" :value="__('Nama Bank')" />
                                    <x-text-input id="nama_bank" class="block mt-1 w-full" type="text" name="nama_bank"
                                        :value="old('nama_bank')" placeholder="Contoh: BPD Jatim" />
                                </div>

                                <div>
                                    <x-input-label for="no_rekening" :value="__('Nomor Rekening')" />
                                    <x-text-input id="no_rekening" class="block mt-1 w-full" type="text"
                                        name="no_rekening" :value="old('no_rekening')" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="npwp" :value="__('NPWP')" />
                                        <x-text-input id="npwp" class="block mt-1 w-full" type="text" name="npwp"
                                            :value="old('npwp')" />
                                    </div>
                                    <div>
                                        <x-input-label for="pkp" :value="__('Status PKP')" />
                                        <select name="pkp"
                                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">- Pilih -</option>
                                            <option value="Ya" {{ old('pkp')=='Ya' ? 'selected' : '' }}>Ya (PKP)
                                            </option>
                                            <option value="Tidak" {{ old('pkp')=='Tidak' ? 'selected' : '' }}>Tidak
                                                (Non-PKP)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="mt-8 flex justify-end">
                            <a href="{{ route('setting.rekanan.index') }}"
                                class="mr-4 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan
                                Data</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>