<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Rekanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- HEADER & TOMBOL --}}
                    <div class="flex flex-col gap-6 mb-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg text-blue-600 dark:text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6">
                                        <path fill-rule="evenodd"
                                            d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 0 0 4.25 22.5h15.5a1.875 1.875 0 0 0 1.865-2.071l-1.263-12a1.875 1.875 0 0 0-1.865-1.679H16.5V6a4.5 4.5 0 1 0-9 0ZM12 3a3 3 0 0 0-3 3v.75h6V6a3 3 0 0 0-3-3Zm-3 8.25a3 3 0 1 0 6 0v.75a3 3 0 1 0-6 0v-.75Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Data Rekanan</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Kelola daftar supplier & rekanan
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 w-full sm:w-auto">
                                <a href="{{ route('setting.rekanan.import') }}"
                                    class="inline-flex justify-center items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition w-full sm:w-auto">
                                    {{ __('Import') }}
                                </a>
                                <a href="{{ route('setting.rekanan.create') }}"
                                    class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition w-full sm:w-auto">
                                    {{ __('Tambah') }}
                                </a>
                                <form id="form-hapus-semua" action="{{ route('setting.rekanan.destroy_all') }}"
                                    method="POST" class="w-full sm:w-auto">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="konfirmasiHapus()"
                                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition w-full">
                                        {{ __('Hapus Semua') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- BAR PENCARIAN --}}
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-100 dark:border-gray-600">
                            <form action="{{ route('setting.rekanan.index') }}" method="GET"
                                class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Cari nama rekanan, NPWP, kota, atau PIC...">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="px-4 py-2 bg-gray-800 dark:bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition duration-150 shadow-sm">
                                        Cari
                                    </button>
                                    @if(request('search'))
                                    <a href="{{ route('setting.rekanan.index') }}"
                                        class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 transition duration-150">
                                        Reset
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- TABEL DATA --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Rekanan</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Alamat / Kota</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        PIC / Kontak</th>

                                    {{-- HEADER KOLOM CHECKBOX --}}
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pembina Ekskul
                                    </th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @forelse ($rekanans as $rekanan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{
                                            $rekanan->nama_rekanan }}</div>
                                        <div class="text-sm text-gray-500">{{ $rekanan->npwp ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{
                                            Str::limit($rekanan->alamat, 30) }}</div>
                                        <div class="text-sm text-gray-500">{{ $rekanan->kota }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $rekanan->pic ?? '-' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $rekanan->no_telp }}</div>
                                    </td>

                                    {{-- KOLOM CHECKBOX DENGAN API --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                class="form-checkbox h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 transition duration-150 ease-in-out"
                                                {{-- PERBAIKAN: Mengirim URL Route langsung dari Blade --}}
                                                onchange="togglePembina('{{ route('setting.rekanan.toggle_status', $rekanan->id) }}', this, {{ $rekanan->id }})"
                                                {{ $rekanan->ket == 1 ? 'checked' : '' }}>
                                            <span
                                                class="ml-2 text-sm text-gray-600 dark:text-gray-400 status-label-{{ $rekanan->id }}">
                                                {{ $rekanan->ket == 1 ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </label>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex justify-center items-center gap-4">
                                            <a href="{{ route('setting.rekanan.edit', $rekanan->id) }}"
                                                class="text-amber-500 hover:text-amber-700">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                            {{-- Form Hapus --}}
                                            <form action="{{ route('setting.rekanan.destroy', $rekanan->id) }}"
                                                method="POST" class="inline-block"
                                                onsubmit="return confirm('Yakin ingin menghapus?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
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
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $rekanans->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT FIX --}}
    <script>
        function togglePembina(url, checkbox, id) {
            // 1. Tentukan status baru (1 jika dicentang, 0 jika tidak)
            const newStatus = checkbox.checked ? 1 : 0;
            const labelSpan = document.querySelector(`.status-label-${id}`);

            // 2. Beri efek loading
            checkbox.disabled = true;
            labelSpan.innerText = 'Menyimpan...';
            labelSpan.classList.add('text-yellow-500');

            // 3. Kirim Request ke API Laravel menggunakan URL dari Blade
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Pastikan mengambil CSRF token dengan benar
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(async response => {
                // Cek apakah respons sukses (200 OK)
                if (!response.ok) {
                    // Coba ambil pesan error JSON jika ada
                    const text = await response.text();
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || response.statusText);
                    } catch (e) {
                        throw new Error(response.statusText);
                    }
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Berhasil Update Tampilan
                    labelSpan.innerText = newStatus ? 'Ya' : 'Tidak';
                    labelSpan.classList.remove('text-yellow-500');
                    console.log('Status updated successfully');
                } else {
                    // Gagal Logika Controller
                    throw new Error(data.message || 'Gagal memperbarui status');
                }
            })
            .catch(error => {
                console.error('Error Detail:', error);
                alert('Gagal: ' + error.message);

                // Kembalikan posisi checkbox karena gagal
                checkbox.checked = !checkbox.checked;

                // Kembalikan label text
                labelSpan.innerText = !newStatus ? 'Ya' : 'Tidak';
                labelSpan.classList.remove('text-yellow-500');
            })
            .finally(() => {
                // Aktifkan kembali checkbox
                checkbox.disabled = false;
            });
        }
    </script>
    <script>
        function konfirmasiHapus() {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Semua data rekanan akan dihapus permanen dan tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Merah
            cancelButtonColor: '#3085d6', // Biru
            confirmButtonText: 'Ya, Hapus Semuanya!',
            cancelButtonText: 'Batal',
            reverseButtons: true // Tombol batal di kiri, hapus di kanan (opsional)
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user klik Ya, submit form secara manual via ID
                document.getElementById('form-hapus-semua').submit();
            }
        })
    }
    </script>
</x-app-layout>
