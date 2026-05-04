<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 min-h-screen space-y-6">

        {{-- Header & Flash Messages --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-900 flex items-center">
                <span class="w-1.5 h-7 bg-indigo-600 rounded-full mr-3"></span>
                Manajemen Komponen Barang
            </h2>
            <p class="text-slate-500 font-medium mt-1 ml-4.5">
                Upload data barang menggunakan file JSON/Excel dan cari komponen dengan spesifik.
            </p>
        </div>

        @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        </div>
        @endif

        @if ($errors->any() || session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl shadow-sm flex items-start">
            <svg class="w-5 h-5 text-rose-600 mr-3 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <ul class="list-disc ml-4 space-y-1 text-sm font-medium text-rose-800">
                @if(session('error')) <li>{{ session('error') }}</li> @endif
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif
        <form action="{{ route('barang.truncate') }}" method="POST"
            onsubmit="return confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin MENGHAPUS SEMUA DATA barang?\n\nTindakan ini permanen dan tidak dapat dibatalkan!');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-5 py-2.5 bg-white text-rose-600 border-2 border-rose-200 hover:bg-rose-50 hover:border-rose-300 font-bold rounded-xl shadow-sm transition flex items-center shrink-0">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
                Kosongkan Semua Data
            </button>
        </form>
    </div>
    {{-- FORM IMPORT (JSON / EXCEL) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Import Data Komponen</h3>

        <form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data"
            class="flex flex-col md:flex-row items-end gap-4">
            @csrf
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-slate-700 mb-2">Pilih File (JSON / XLSX / CSV)</label>
                <label for="file_import"
                    class="flex items-center justify-center w-full px-4 py-3 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition relative">
                    <svg class="w-5 h-5 text-indigo-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span id="file-text" class="text-sm font-medium text-slate-600 truncate">Pilih atau letakkan
                        file di sini...</span>
                    <input id="file_import" type="file" name="file_import" accept=".json,.xlsx,.xls,.csv" required
                        class="hidden" />
                </label>
            </div>
            <button type="submit"
                class="w-full md:w-auto px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Mulai Import
            </button>
        </form>
    </div>

    {{-- AREA PENCARIAN & TABEL --}}
    <div x-data="pencarianBarang()" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Filter & Pencarian Data
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

            <!-- 1. Filter Nama Barang -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Nama Barang</label>
                <input type="text" x-model="nama_barang" @input.debounce.500ms="cariData()"
                    placeholder="Ketik nama barang..."
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition text-sm">
            </div>

            <!-- 2. Filter Kode Rekening (Searchable Select Custom) -->
            <div class="relative" @click.away="rekeningOpen = false">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Kode Rekening</label>
                <div class="relative">
                    <input type="text" x-model="rekeningSearch" @focus="rekeningOpen = true"
                        @input="rekeningOpen = true; kode_rekening = ''" placeholder="Ketik untuk mencari rekening..."
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 transition text-sm font-mono bg-white">

                    <!-- Tombol Clear -->
                    <button x-show="rekeningSearch !== ''" @click="rekeningSearch = ''; kode_rekening = ''; cariData();"
                        type="button" class="absolute right-3 top-2.5 text-slate-400 hover:text-rose-500 transition"
                        style="display: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Dropdown Options -->
                <div x-show="rekeningOpen" style="display: none;" x-transition
                    class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                    <template x-for="rek in filteredRekening" :key="rek.kode_rekening">
                        <div @click="pilihRekening(rek)"
                            class="px-4 py-2 hover:bg-indigo-50 cursor-pointer border-b border-slate-50 last:border-0 transition text-left">
                            <div class="font-mono text-xs font-bold text-indigo-600" x-text="rek.kode_rekening">
                            </div>
                            <div class="text-[11px] text-slate-600 truncate mt-0.5" x-text="rek.nama_rekening">
                            </div>
                        </div>
                    </template>
                    <div x-show="filteredRekening.length === 0" class="px-4 py-3 text-xs text-slate-500 text-center">
                        Rekening tidak ditemukan.
                    </div>
                </div>
            </div>

            <!-- 3. Filter Kategori -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Kategori</label>
                <select x-model="kategori" @change="cariData()"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-50 focus:ring-2 focus:ring-indigo-600 transition text-sm">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Filter Kode Belanja (Checklist) -->
            <div class="md:col-span-3">
                <label
                    class="block text-xs font-bold text-slate-500 uppercase mb-2 border-t border-slate-100 pt-4">Pilih
                    Kode Belanja / Barang</label>
                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1">
                    @foreach($kodeBelanjaList as $kb)
                    <label
                        class="inline-flex items-center cursor-pointer bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50 hover:border-indigo-200 transition shadow-sm">
                        <input type="checkbox" value="{{ $kb }}" x-model="selectedKodeBelanja" @change="cariData()"
                            class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-600 cursor-pointer">
                        <span class="ml-2 text-xs font-bold text-slate-700 font-mono">{{ $kb }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Status Loading -->
        <div x-show="isLoading" style="display: none;" class="text-center py-12 text-slate-500">
            <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="font-medium text-sm">Menyaring data...</p>
        </div>

        <!-- Hasil Pencarian -->
        <div x-show="!isLoading && hasil.length > 0" style="display: none;">
            <div class="overflow-x-auto border border-slate-200 rounded-xl max-h-[600px] custom-scrollbar">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="py-3 px-4 font-bold text-xs uppercase tracking-wider">Kode Rekening</th>
                            <th class="py-3 px-4 font-bold text-xs uppercase tracking-wider">Kode Belanja</th>
                            <th class="py-3 px-4 font-bold text-xs uppercase tracking-wider">ID Barang</th>
                            <th class="py-3 px-4 font-bold text-xs uppercase tracking-wider">Nama Barang & Satuan
                            </th>
                            <th class="py-3 px-4 font-bold text-xs uppercase tracking-wider text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="item in hasil" :key="item.id_barang">
                            <tr class="hover:bg-indigo-50/50 transition">
                                <td class="py-3 px-4 font-mono text-xs text-indigo-600 font-bold"
                                    x-text="item.kode_rekening"></td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600" x-text="item.kode_belanja">
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-600" x-text="item.id_barang">
                                </td>
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-[11px] font-bold"
                                    x-text="item.kategori"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 whitespace-normal line-clamp-2"
                                        x-text="item.nama_barang"></div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] text-slate-500">Satuan: <span
                                                class="font-bold text-slate-700" x-text="item.satuan"></span></span>
                                        <span x-show="item.digunakan_rkas"
                                            class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold">RKAS</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-bold text-slate-900">
                                        Rp <span
                                            x-text="new Intl.NumberFormat('id-ID').format(item.harga_barang)"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Min: <span
                                            x-text="new Intl.NumberFormat('id-ID').format(item.harga_minimal)"></span>
                                        | Max: <span
                                            x-text="new Intl.NumberFormat('id-ID').format(item.harga_maksimal)"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-slate-500 mt-3 text-right">Menampilkan maksimal 50 data teratas.</p>
        </div>

        <!-- State Awal / Kosong -->
        <div x-show="!isLoading && hasil.length === 0"
            class="text-center py-16 bg-slate-50 rounded-xl border border-slate-200 border-dashed">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="text-slate-500 font-medium text-sm"
                x-text="(nama_barang !== '' || kode_rekening !== '' || selectedKodeBelanja.length > 0 || kategori !== '') ? 'Data tidak ditemukan dengan kriteria tersebut.' : 'Isi salah satu kolom pencarian di atas untuk mulai mencari.'">
            </p>
        </div>
    </div>

    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }
    </style>

    <!-- Script Native Nama File -->
    <script>
        document.getElementById('file_import').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : '';
            var textElement = document.getElementById('file-text');
            if(fileName) {
                textElement.textContent = fileName;
                textElement.classList.add('text-indigo-600', 'font-bold');
            } else {
                textElement.textContent = 'Pilih atau letakkan file di sini...';
                textElement.classList.remove('text-indigo-600', 'font-bold');
            }
        });
    </script>

    <!-- Script AlpineJS Utama -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pencarianBarang', () => ({
                nama_barang: '',
                kode_rekening: '',
                kategori: '',
                selectedKodeBelanja: [],

                rekeningOptions: @json($rekeningList),
                rekeningSearch: '',
                rekeningOpen: false,

                hasil: [],
                isLoading: false,

                get filteredRekening() {
                    if (this.rekeningSearch === '') return this.rekeningOptions;
                    const searchLower = this.rekeningSearch.toLowerCase();
                    return this.rekeningOptions.filter(rek =>
                        rek.kode_rekening.toLowerCase().includes(searchLower) ||
                        rek.nama_rekening.toLowerCase().includes(searchLower)
                    );
                },

                pilihRekening(rek) {
                    this.kode_rekening = rek.kode_rekening;
                    this.rekeningSearch = rek.kode_rekening;
                    this.rekeningOpen = false;
                    this.cariData();
                },

                async cariData() {
                    if (this.nama_barang.trim() === '' &&
                        this.kode_rekening.trim() === '' &&
                        this.selectedKodeBelanja.length === 0 &&
                        this.kategori === '') {
                        this.hasil = [];
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const params = new URLSearchParams({
                            nama_barang: this.nama_barang,
                            kode_rekening: this.kode_rekening,
                            kategori: this.kategori
                        });

                        if (this.selectedKodeBelanja.length > 0) {
                            params.append('kode_belanja', this.selectedKodeBelanja.join(','));
                        }

                        const response = await fetch(`{{ route('api.barang.search') }}?${params.toString()}`);
                        const data = await response.json();
                        this.hasil = data;
                    } catch (error) {
                        console.error("Terjadi kesalahan saat mencari data:", error);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }))
        })
    </script>
</x-app-layout>