<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Halaman RKAS') }}
            </h2>
            <div class="flex items-center space-x-2">
                <span
                    class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full dark:bg-indigo-900 dark:text-indigo-200 shadow-sm border border-indigo-200">
                    {{ strtoupper($anggaranAktif->singkatan ?? '-') }} {{ $anggaranAktif->tahun ?? '' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ========================================================= --}}
            {{-- AREA PESAN ALERT (SUCCESS, ERROR, WARNING) --}}
            {{-- ========================================================= --}}
            <div class="mb-6">
                {{-- Alert Success --}}
                @if (session('success'))
                <!-- ... (Kode Alert Success Anda Tetap Sama) ... -->
                @endif
                {{-- Alert Error --}}
                @if (session('error'))
                <!-- ... (Kode Alert Error Anda Tetap Sama) ... -->
                @endif
                {{-- Alert Warning --}}
                @if (session('warning'))
                <!-- ... (Kode Alert Warning Anda Tetap Sama) ... -->
                @endif
            </div>

            {{-- ========================================================= --}}
            {{-- CARD 1: FORM UPLOAD RKAS --}}
            {{-- ========================================================= --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div
                        class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium">
                                Target Import RKAS:
                                <strong class="text-emerald-700 dark:text-emerald-300">
                                    {{ $anggaranAktif->nama_anggaran ?? 'Belum Diatur' }}
                                    ({{ strtoupper($anggaranAktif->singkatan ?? '-') }} Tahun {{ $anggaranAktif->tahun
                                    ?? '-' }})
                                </strong>
                            </span>
                        </div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 ml-7 italic">
                            * Pastikan file JSON yang Anda upload sesuai dengan sumber dana {{ $anggaranAktif->singkatan
                            ?? 'yang dipilih' }}.
                        </p>
                    </div>

                    <form action="{{ route('rkas.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Upload File JSON RKAS (Bisa multiple)</label>
                            <input type="file" name="json_files[]" multiple required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center shadow-md">
                                Simpan Data RKAS
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- CARD 2: TABEL DATA RKAS DARI API --}}
            {{-- ========================================================= --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Daftar Data RKAS (Via API)</h3>

                        <!-- Tombol untuk trigger Fetch API -->
                        <button onclick="loadRkasData()"
                            class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded-lg transition shadow flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Muat Data
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border dark:border-gray-700 rounded-lg">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Kode Akun</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama Kegiatan</th>
                                </tr>
                            </thead>
                            <tbody id="table-rkas-body"
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="3"
                                        class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Klik tombol <strong>"Muat Data"</strong> untuk menampilkan JSON RKAS dari API.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SCRIPT FETCH API --}}
    {{-- ========================================================= --}}
    <script>
        function loadRkasData() {
            const tbody = document.getElementById('table-rkas-body');
            // Ambil ID Anggaran dari Variabel PHP Blade Anda
            const anggaranId = '{{ $anggaranAktif->id ?? '' }}';

            // Ambil Token CSRF dari Meta Tag Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!anggaranId) {
                alert('Peringatan: Anggaran Aktif belum diatur!');
                return;
            }

            // Animasi Loading
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="animate-spin h-5 w-5 mx-auto text-emerald-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat data dari API...
                    </td>
                </tr>`;
    fetch(`/json/get-rkas?anggaran_id=${anggaranId}`, {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest' // Tambahkan ini
    }
})
            .then(response => {
                if (response.status === 401) {
                    throw new Error("Akses Ditolak (401). Sesi login mungkin sudah habis.");
                }
                if (!response.ok) {
                    throw new Error("Terjadi kesalahan jaringan atau server (Error " + response.status + ")");
                }

                return response.json();
            })
            .then(result => {
                if (result.status === 'success' && result.data.length > 0) {
                    console.log('Data RKAS berhasil dimuat:', result.data);
                    let rows = '';
                    result.data.forEach((item, index) => {
                        // Cek apakah relasi kegiatan ada
                        let namaKegiatan = item.kegiatan
    ? (item.kegiatan.nama ?? item.kegiatan.uraian ?? item.keterangan)
    : item.keterangan;

                        rows += `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${index + 1}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-emerald-600 dark:text-emerald-400">${item.kodeakun ?? '-'}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">${namaKegiatan}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = rows;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada data RKAS ditemukan untuk anggaran ini.
                            </td>
                        </tr>`;
                }
            })
            .catch(error => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-red-500">
                            <strong>Gagal memuat data!</strong> <br> ${error.message}
                        </td>
                    </tr>`;
            });
        }
    </script>
</x-app-layout>