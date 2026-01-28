<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Referensi Ekskul & Pelatih') }}
        </h2>
    </x-slot>

    {{-- Gunakan Alpine x-data untuk mengelola Modal --}}
    <div class="py-12" x-data="{
        showModal: false,
        isEdit: false,
        formAction: '',
        dataNama: '',
        dataPelatih: '',
        modalTitle: ''
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Success --}}
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Header & Tombol Tambah --}}
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Ekstrakurikuler</h3>
                        <p class="text-sm text-gray-500">Kelola nama ekskul dan pelatih default-nya.</p>
                    </div>
                    <button @click="
                        showModal = true;
                        isEdit = false;
                        formAction = '{{ route('ekskul.ref.store') }}';
                        dataNama = '';
                        dataPelatih = '';
                        modalTitle = 'Tambah Ekskul Baru';
                    "
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Tambah Ekskul
                    </button>
                </div>

                {{-- Tabel Data --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Ekskul</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pelatih Default</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @forelse($ekskuls as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ekskuls->firstItem() +
                                    $index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{
                                    $item->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    @if($item->rekanan)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->rekanan->nama_rekanan }}
                                    </span>
                                    @else
                                    <span class="text-gray-400 italic">- Belum diset -</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center gap-3">
                                        {{-- Tombol Edit --}}
                                        <button @click="
                                            showModal = true;
                                            isEdit = true;
                                            formAction = '{{ route('ekskul.ref.update', $item->id) }}';
                                            dataNama = '{{ addslashes($item->nama) }}';
                                            dataPelatih = '{{ $item->rekanan_id }}';
                                            modalTitle = 'Edit Data Ekskul';
                                        " class="text-amber-500 hover:text-amber-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('ekskul.ref.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus ekskul ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada data ekskul.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $ekskuls->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL UNIVERSAL (TAMBAH & EDIT) --}}
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                {{-- Backdrop --}}
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                {{-- Modal Panel --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                    <form :action="formAction" method="POST">
                        @csrf
                        {{-- Method Spoofing untuk PUT jika Edit --}}
                        <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4"
                                x-text="modalTitle"></h3>

                            {{-- Input Nama Ekskul --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                                    Ekskul</label>
                                <input type="text" name="nama" x-model="dataNama"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    required placeholder="Contoh: Futsal, Pramuka">
                            </div>

                            {{-- Dropdown Pelatih --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Pelatih
                                    (Rekanan)</label>
                                <select name="rekanan_id" x-model="dataPelatih"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Pilih Pelatih --</option>
                                    @foreach($listPelatih as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_rekanan }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">*Hanya menampilkan rekanan yang dicentang sebagai
                                    "Pembina Ekskul"</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="showModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
