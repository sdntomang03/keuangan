<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Halaman AKB') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                     <form action="{{ route('akb.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Jenis Anggaran</label>
            <select name="jenis_anggaran" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="bos">BOS</option>
                <option value="bop">BOP</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tahun Anggaran</label>
            <select name="tahun" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="2026">2026</option>
                <option value="2027">2027</option>
            </select>
        </div>
    </div>

    <div class="mb-6">
        <input type="file" name="json_files[]" multiple required ...>
    </div>

 <div class="flex flex-wrap gap-4 mt-6 items-center">
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
        </svg>
        Simpan Data
    </button>

    <a href="{{ route('akb.generate', request()->all()) }}" 
       onclick="return confirm('Apakah Anda yakin ingin menjalankan proses generate rincian untuk data ini? Proses ini mungkin akan menimpa data rincian yang sudah ada.')"
       class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Generate Rincian
    </a>
</div>
</form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
