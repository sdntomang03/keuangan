<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Data Rekanan') }}
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Notifikasi SweetAlert akan muncul lewat script global Anda,
                tapi kita siapkan alert html standard juga --}}
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif
                <div class="mb-6 border-b pb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Download Format</h3>

                        </div>
                        <a href="{{ route('settings.rekanan.template') }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Template .XLSX
                        </a>
                    </div>
                </div>
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Petunjuk Import</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-2">
                        <li>Gunakan file format <strong>.xlsx</strong> atau <strong>.xls</strong>.</li>
                        <li>Pastikan baris pertama adalah <strong>Judul Kolom (Header)</strong>.</li>
                        <li>Kolom yang wajib diisi: <strong>nama_rekanan</strong>.</li>
                    </ul>
                </div>

                {{-- Form Import --}}
                <form action="{{ route('settings.rekanan.import.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih File Excel</label>
                        <input type="file" name="file_excel" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Upload & Import
                        </button>
                    </div>
                </form>

                {{-- Contoh Tabel Format Excel --}}
                <div class="mt-8">
                    <p class="text-sm font-bold text-gray-700 mb-2">Contoh Format Header Excel:</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left text-gray-500 border">
                            <thead class="bg-gray-50 text-gray-700 uppercase">
                                <tr>
                                    <th class="px-4 py-2 border">nama_rekanan</th>
                                    <th class="px-4 py-2 border">no_rekening</th>
                                    <th class="px-4 py-2 border">nama_bank</th>
                                    <th class="px-4 py-2 border">npwp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b">
                                    <td class="px-4 py-2">CV. Maju Jaya</td>
                                    <td class="px-4 py-2">1234567890</td>
                                    <td class="px-4 py-2">BCA</td>
                                    <td class="px-4 py-2">09.123.456.7-123.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>


</x-app-layout>