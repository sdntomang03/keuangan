<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Kegiatan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('setting.kegiatan.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="idbl" :value="__('IDBL (Wajib & Unik)')" />
                                <x-text-input id="idbl" class="block mt-1 w-full bg-yellow-50" type="text" name="idbl"
                                    :value="old('idbl')" required autofocus placeholder="Contoh: 1.2.3.4" />
                                <x-input-error :messages="$errors->get('idbl')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="snp" :value="__('Standar Nasional Pendidikan (SNP)')" />
                                <x-text-input id="snp" class="block mt-1 w-full" type="text" name="snp"
                                    :value="old('snp')" placeholder="Contoh: Standar Isi" />
                            </div>
                        </div>

                        <div class="border-t border-gray-100 my-4"></div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <x-input-label for="sumber_dana" :value="__('Sumber Dana')" />
                                <select name="sumber_dana" id="sumber_dana"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">- Pilih -</option>
                                    <option value="BOS Reguler">BOS Reguler</option>
                                    <option value="BOS Kinerja">BOS Kinerja</option>
                                    <option value="BOP">BOP</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="kodedana" :value="__('Kode Dana')" />
                                <x-text-input id="kodedana" class="block mt-1 w-full" type="text" name="kodedana"
                                    :value="old('kodedana')" />
                            </div>
                            <div>
                                <x-input-label for="namadana" :value="__('Nama Dana')" />
                                <x-text-input id="namadana" class="block mt-1 w-full" type="text" name="namadana"
                                    :value="old('namadana')" />
                            </div>
                        </div>

                        <div class="border-t border-gray-100 my-4"></div>

                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="col-span-1">
                                    <x-input-label for="kodegiat" :value="__('Kode Kegiatan')" />
                                    <x-text-input id="kodegiat" class="block mt-1 w-full" type="text" name="kodegiat"
                                        :value="old('kodegiat')" />
                                </div>
                                <div class="col-span-2">
                                    <x-input-label for="namagiat" :value="__('Nama Kegiatan')" />
                                    <x-text-input id="namagiat" class="block mt-1 w-full" type="text" name="namagiat"
                                        :value="old('namagiat')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="kegiatan" :value="__('Deskripsi Kegiatan')" />
                                <textarea id="kegiatan" name="kegiatan" rows="3"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('kegiatan') }}</textarea>
                            </div>

                            <div>
                                <x-input-label for="link" :value="__('Link Terkait (Opsional)')" />
                                <x-text-input id="link" class="block mt-1 w-full" type="url" name="link"
                                    :value="old('link')" placeholder="https://..." />
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('setting.kegiatan.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</a>
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