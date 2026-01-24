<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Pembersihan Data RKAS</h2>
                <p class="text-sm text-gray-500">Fitur ini menghapus seluruh rincian belanja (RKAS) tanpa menghapus
                    master anggaran.</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100">

                @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">{{
                    session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">{{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.rkas.cleanup.destroy') }}" method="POST" id="form-cleanup">
                    @csrf
                    @method('DELETE')

                    <div class="grid grid-cols-1 gap-6">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">1. Pilih Sekolah</label>
                            <select name="sekolah_id" id="sekolah_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                                <option value="">-- Cari Sekolah --</option>
                                @foreach($sekolahs as $sekolah)
                                <option value="{{ $sekolah->id }}">{{ $sekolah->nama_sekolah }} ({{ $sekolah->npsn }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-bold text-gray-700 mb-2">2. Pilih Anggaran yang akan
                                Dikosongkan</label>
                            <select name="anggaran_id" id="anggaran_id"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed"
                                disabled required>
                                <option value="">-- Pilih Sekolah Terlebih Dahulu --</option>
                            </select>

                            <div id="loading-spinner" class="absolute right-10 top-10 hidden">
                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                            <button type="button" onclick="confirmDelete()" id="btn-submit"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform transition hover:scale-105 opacity-50 cursor-not-allowed"
                                disabled>
                                <span class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Semua Data RKAS
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const sekolahSelect = document.getElementById('sekolah_id');
        const anggaranSelect = document.getElementById('anggaran_id');
        const btnSubmit = document.getElementById('btn-submit');
        const loadingSpinner = document.getElementById('loading-spinner');

        // 1. Saat Sekolah Dipilih
        sekolahSelect.addEventListener('change', function() {
            const sekolahId = this.value;

            // Reset dropdown anggaran
            anggaranSelect.innerHTML = '<option value="">-- Memuat Data... --</option>';
            anggaranSelect.disabled = true;
            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');

            if (sekolahId) {
                loadingSpinner.classList.remove('hidden');

                // Fetch API
                fetch(`/admin/api/anggaran-by-sekolah/${sekolahId}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingSpinner.classList.add('hidden');

                        if (data.length > 0) {
                            anggaranSelect.innerHTML = '<option value="">-- Pilih Anggaran --</option>';
                            data.forEach(item => {
                                anggaranSelect.innerHTML += `<option value="${item.id}">${item.nama_anggaran} - ${item.tahun} (${item.singkatan})</option>`;
                            });
                            anggaranSelect.disabled = false;
                            anggaranSelect.classList.remove('bg-gray-50', 'cursor-not-allowed');
                        } else {
                            anggaranSelect.innerHTML = '<option value="">Tidak ada anggaran ditemukan</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadingSpinner.classList.add('hidden');
                        anggaranSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                    });
            } else {
                anggaranSelect.innerHTML = '<option value="">-- Pilih Sekolah Terlebih Dahulu --</option>';
            }
        });

        // 2. Saat Anggaran Dipilih -> Aktifkan Tombol
        anggaranSelect.addEventListener('change', function() {
            if (this.value) {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        // 3. Konfirmasi SweetAlert
        function confirmDelete() {
            const sekolahText = sekolahSelect.options[sekolahSelect.selectedIndex].text;
            const anggaranText = anggaranSelect.options[anggaranSelect.selectedIndex].text;

            Swal.fire({
                title: 'PERINGATAN KERAS!',
                html: `
                    Anda akan menghapus seluruh data RKAS untuk:<br>
                    <b>${sekolahText}</b><br>
                    <span class="text-red-600 font-bold text-lg">${anggaranText}</span><br><br>
                    Tindakan ini tidak dapat dibatalkan!
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Menghapus...',
                        text: 'Mohon jangan tutup halaman ini.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });
                    document.getElementById('form-cleanup').submit();
                }
            });
        }
    </script>
</x-app-layout>