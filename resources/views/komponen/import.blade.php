<x-app-layout>
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Page Title & Action Button --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fa fa-upload text-blue-600 mr-3"></i> Import JSON Komponen
            </h3>
            <a href="{{ route('komponenrkas.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fa fa-arrow-left mr-2 text-gray-400"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md border-t-4 border-blue-600 overflow-hidden">

            {{-- Form Header --}}
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h4 class="text-lg font-semibold text-gray-900">Formulir Import Data</h4>
                <p class="mt-1 text-sm text-gray-500">Unggah satu atau beberapa file JSON sekaligus. Sistem otomatis
                    mendeteksi kode rekening dari nama file Anda.</p>
            </div>

            {{-- Error Validation --}}
            @if ($errors->any())
            <div class="p-6 pb-0">
                <div class="rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada input Anda:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Form Content --}}
            <form action="{{ route('komponenrkas.storeImport') }}" method="POST" enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Tahun Anggaran <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tahun" value="{{ date('Y') + 1 }}" required
                        class="mt-1 block w-full md:w-1/3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2 border">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Kode Rekening (Opsional/Default)
                    </label>
                    <select name="kode_rekening"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2 border bg-white">
                        <option value="">-- Otomatis ambil dari nama file --</option>
                        @foreach($koreks as $korek)
                        <option value="{{ $korek->kode }}">{{ $korek->kode }} - {{ $korek->uraian_singkat }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fa fa-info-circle text-blue-500 mr-1"></i>
                        Hanya digunakan jika nama file gagal dibaca. Jika nama file sudah diawali kode (contoh:
                        5.1.xx.json), kosongkan saja.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        File JSON Komponen <span class="text-red-500">*</span>
                    </label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors bg-gray-50/30">
                        <div class="space-y-1 text-center">
                            <i class="fa fa-file-code-o text-gray-400 text-4xl mb-2"></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="json_files"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 p-1">
                                    <span>Pilih File JSON</span>
                                    <input id="json_files" name="json_files[]" type="file" accept=".json" multiple
                                        required class="sr-only">
                                </label>
                                <p class="pl-1 py-1">atau *drag and drop* ke area ini</p>
                            </div>
                            <p class="text-xs text-gray-500">Anda dapat memilih (sorot) banyak file sekaligus.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('komponenrkas.index') }}"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fa fa-save mr-2 mt-0.5"></i> Simpan Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>