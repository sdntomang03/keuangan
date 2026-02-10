<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Data Arkas</h2>
            <a href="{{ route('arkas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke
                Data</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Form Upload --}}
                <div class="md:col-span-1">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="font-bold mb-4">Upload Excel</h3>
                        <form action="{{ route('arkas.import.store') }}" method="POST" enctype="multipart/form-data"
                            onsubmit="document.getElementById('btnSubmit').disabled=true; document.getElementById('btnSubmit').innerHTML='Proses...';">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">File</label>
                                <input type="file" name="file" required class="block w-full border rounded p-2">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Set Jenis Belanja (Opsional)</label>
                                <select name="jenis_belanja_input" class="w-full border rounded p-2">
                                    <option value="">-- Sesuai Excel --</option>
                                    @foreach($listJenisBelanja as $jenis)
                                    <option value="{{ $jenis }}">{{ $jenis }}</option>
                                    @endforeach
                                    <option value="BOS Reguler">BOS Reguler</option>
                                    <option value="BOS Kinerja">BOS Kinerja</option>
                                </select>
                            </div>
                            <button id="btnSubmit" type="submit"
                                class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">Import</button>
                        </form>
                    </div>
                </div>

                {{-- Panduan --}}
                <div class="md:col-span-2">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="font-bold mb-4">Format Excel</h3>
                        <p class="text-sm text-gray-600 mb-2">Pastikan header baris pertama sesuai:</p>
                        <table class="min-w-full text-xs border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">ID Barang</th>
                                    <th class="border p-2">Kode Rekening</th>
                                    <th class="border p-2">Nama Barang</th>
                                    <th class="border p-2">Harga Barang</th>
                                    <th class="border p-2">Jenisbelanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border p-2">BRG-001</td>
                                    <td class="border p-2">5.1.02...</td>
                                    <td class="border p-2">Kertas A4</td>
                                    <td class="border p-2">50000</td>
                                    <td class="border p-2">BOS Reguler</td>
                                    </td>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>