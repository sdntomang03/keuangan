<x-app-layout>

    <!-- 1. HEADER SLOT -->
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Surat Tanda Setoran (STS)') }}
            </h2>

            <!-- Tombol Tambah STS menggunakan $dispatch untuk mengirim sinyal ke konten bawah -->
            <button @click="$dispatch('buka-modal-create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah STS
            </button>
        </div>
    </x-slot>

    <!-- 2. KONTEN UTAMA (Wrapper Alpine.js) -->
    <!-- Menangkap event 'buka-modal-create' dari tombol di atas menggunakan @buka-modal-create.window -->
    <div class="py-12" x-data="stsManager(@js($stss))" @buka-modal-create.window="openCreate()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alert Notifikasi (AJAX) -->
            <div x-show="alert.show" x-transition
                :class="{'bg-green-100 border-green-500 text-green-700': alert.type === 'success', 'bg-red-100 border-red-500 text-red-700': alert.type === 'error'}"
                class="mb-4 p-4 border-l-4 rounded shadow-sm" style="display: none;">
                <p x-text="alert.message"></p>
            </div>

            <!-- Tabel Data -->
            <div
                class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    No. Bukti</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Uraian</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Nominal</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="sts in stssList" :key="sts.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200"
                                        x-text="formatDate(sts.tanggal)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-300"
                                        x-text="sts.no_bukti"></td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="sts.uraian">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100 text-right"
                                        x-text="formatRupiah(sts.nominal)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <button @click="editData(sts.id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mx-2">Edit</button>
                                        <button @click="deleteData(sts.id)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 mx-2">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="stssList.length === 0" style="display: none;">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                    data Surat Tanda Setoran (STS).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. MODAL FORM (Gabungan Create & Edit) -->
            <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
                <div x-show="isModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>
                <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                    <div x-show="isModalOpen" @click.away="isModalOpen = false" x-transition
                        class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                        <div
                            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                x-text="formMode === 'create' ? 'Input STS Baru' : 'Edit Data STS'"></h3>
                        </div>

                        <form @submit.prevent="submitForm">
                            <div class="px-4 pt-5 pb-4 sm:p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                                        Setor</label>
                                    <input type="date" x-model="form.tanggal" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor
                                        Bukti</label>
                                    <input type="text" x-model="form.no_bukti" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Uraian /
                                        Keterangan</label>
                                    <textarea rows="3" x-model="form.uraian" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal
                                        Setoran (Rp)</label>
                                    <input type="number" x-model="form.nominal" required min="1"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <div
                                class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-600">
                                <button type="submit" :disabled="isLoading"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-white font-medium hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 transition">
                                    <span x-show="!isLoading">Simpan</span>
                                    <span x-show="isLoading">Menyimpan...</span>
                                </button>
                                <button type="button" @click="isModalOpen = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 4. SCRIPT LOGIKA AJAX -->
    <script>
        function stsManager(initialData) {
            return {
                stssList: initialData,
                isModalOpen: false,
                isLoading: false,
                formMode: 'create',
                alert: { show: false, message: '', type: '' },
                form: { id: null, tanggal: '', no_bukti: '', uraian: '', nominal: '' },

                getCSRFToken() {
                    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                openCreate() {
                    this.formMode = 'create';
                    this.form = { id: null, tanggal: '', no_bukti: '', uraian: '', nominal: '' };
                    this.isModalOpen = true;
                },

                editData(id) {
                    fetch(`/sts/${id}/edit`)
                        .then(res => res.json())
                        .then(data => {
                            this.formMode = 'edit';
                            this.form = { id: data.id, tanggal: data.tanggal, no_bukti: data.no_bukti, uraian: data.uraian, nominal: data.nominal };
                            this.isModalOpen = true;
                        })
                        .catch(() => this.showAlert('Gagal mengambil data!', 'error'));
                },

                submitForm() {
                    this.isLoading = true;
                    const url = this.formMode === 'create' ? '{{ route("sts.store") }}' : `/sts/${this.form.id}`;

                    fetch(url, {
                        method: this.formMode === 'create' ? 'POST' : 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCSRFToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            if (this.formMode === 'create') {
                                this.stssList.unshift(response.data);
                            } else {
                                const index = this.stssList.findIndex(sts => sts.id === this.form.id);
                                this.stssList[index] = response.data;
                            }
                            this.isModalOpen = false;
                            this.showAlert(response.message, 'success');
                        } else {
                            this.showAlert(response.message || 'Terjadi kesalahan.', 'error');
                        }
                    })
                    .catch(() => this.showAlert('Terjadi kesalahan server.', 'error'))
                    .finally(() => { this.isLoading = false; });
                },

                deleteData(id) {
                    if(!confirm('Yakin ingin menghapus STS ini? Data di BKU juga akan terhapus.')) return;

                    fetch(`/sts/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.getCSRFToken(), 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            this.stssList = this.stssList.filter(sts => sts.id !== id);
                            this.showAlert(response.message, 'success');
                        } else {
                            this.showAlert(response.message, 'error');
                        }
                    });
                },

                showAlert(message, type) {
                    this.alert = { show: true, message: message, type: type };
                    setTimeout(() => { this.alert.show = false; }, 3000);
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                },

                formatRupiah(angka) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
                }
            }
        }
    </script>
</x-app-layout>