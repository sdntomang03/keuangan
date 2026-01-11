<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Halaman RKAS') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                     <form action="{{ route('rkas.import') }}" method="POST" enctype="multipart/form-data">
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

 <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        Proses & Simpan Data
                    </button>
</form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
