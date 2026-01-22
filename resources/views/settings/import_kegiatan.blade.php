<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Master Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Alert --}}
                @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
                </script>
                @endif
                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif

                {{-- Step 1: Download --}}
                <div class="mb-6 border-b pb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Langkah 1: Download Format</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Template berisi kolom standar: <strong>idbl</strong>, <strong>snp</strong>,
                                <strong>sumber_dana</strong>, dll.
                            </p>
                        </div>
                        <a href="{{ route('settings.kegiatan.template') }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                            Download Template .XLSX
                        </a>
                    </div>
                </div>

                {{-- Step 2: Upload --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Langkah 2: Upload File</h3>
                    <p class="text-xs text-gray-500 mb-4">Pastikan kolom <strong>idbl</strong> unik. Jika idbl sudah
                        ada, data akan diupdate.</p>

                    <form action="{{ route('settings.kegiatan.import.store') }}" method="POST"
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- Dropdown Anggaran DIHAPUS karena schema database tidak punya anggaran_id --}}

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
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</x-app-layout>