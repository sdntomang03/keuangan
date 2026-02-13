<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Referensi ARKAS</h2>
            <a href="{{ route('arkas.import.page') }}"
                class="bg-green-600 text-white text-sm px-4 py-2 rounded hover:bg-green-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Data
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">{{ session('success') }}</div>
            @endif

            {{-- FILTER SECTION --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    {{-- Filter Jenis --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Belanja</label>
                        <select id="filterJenis"
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua</option>
                            @foreach($listJenisBelanja as $jenis)
                            <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Sort Harga --}}
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <select id="filterSort"
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Terbaru</option>
                            <option value="termahal">Harga Tertinggi</option>
                            <option value="termurah">Harga Terendah</option>
                        </select>
                    </div>
                    {{-- Search --}}
                    <div class="w-full md:w-2/4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <input type="text" id="filterSearch" placeholder="Cari nama barang / kode rekening..."
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- TABLE SECTION --}}
            <div class="bg-white shadow sm:rounded-lg p-6 relative">

                {{-- Loading Spinner (Overlay) --}}
                <div id="loadingOverlay"
                    class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 hidden">
                    <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Data Database</h3>
                    <span id="totalInfo" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Memuat...</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-500">Nama Barang</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500">Id Barang</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500">Satuan</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500">Harga Maks</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500">Jenis</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                            {{-- Data akan diisi via Javascript --}}
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-4 flex justify-end gap-2" id="paginationControls">
                    {{-- Tombol prev/next akan diinject JS --}}
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT AJAX --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let debounceTimer;
            const baseUrl = "{{ route('arkas.data') }}";

            // Elemen DOM
            const inputSearch = document.getElementById('filterSearch');
            const inputJenis = document.getElementById('filterJenis');
            const inputSort = document.getElementById('filterSort');
            const tableBody = document.getElementById('tableBody');
            const paginationControls = document.getElementById('paginationControls');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const totalInfo = document.getElementById('totalInfo');

            // Format Rupiah
            const formatRupiah = (number) => {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
            };

            // Fungsi Utama: Fetch Data
            function fetchData(url = baseUrl) {
                // Tampilkan Loading
                loadingOverlay.classList.remove('hidden');

                // Ambil parameter filter
                const params = new URLSearchParams(new URL(url).search);
                params.set('search', inputSearch.value);
                params.set('jenis_belanja', inputJenis.value);
                params.set('sort', inputSort.value);

                // Request ke Server
                fetch(`${baseUrl}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        renderTable(data.data);
                        renderPagination(data);
                        totalInfo.innerText = `Total: ${data.total} Data`;
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        loadingOverlay.classList.add('hidden');
                    });
            }

            // Fungsi Render Baris Tabel
            function renderTable(items) {
                tableBody.innerHTML = '';

                if(items.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Data tidak ditemukan.</td></tr>`;
                    return;
                }

                items.forEach(item => {
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">${item.nama_barang}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs font-bold text-gray-700">${item.id_barang ?? '-'}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">${item.satuan ?? '-'}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-800">${formatRupiah(item.harga_maksimal)}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full ${item.jenis_belanja ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'}">
                                    ${item.jenis_belanja ?? '-'}
                                </span>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }

            // Fungsi Render Pagination
           // --- FUNGSI UPDATE PAGINATION ANGKA ---
function renderPagination(data) {
    paginationControls.innerHTML = '';

    // Konfigurasi tampilan
    const currentPage = data.current_page;
    const lastPage = data.last_page;
    const delta = 2; // Jumlah angka di kiri/kanan halaman aktif
    const range = [];
    const rangeWithDots = [];
    let l;

    // 1. Tombol PREV (<)
    const prevBtn = createPaginationButton('«', currentPage - 1, false, currentPage === 1);
    paginationControls.appendChild(prevBtn);

    // 2. Logika Pembuatan Angka (1 ... 5 6 7 ... 20)
    range.push(1);
    for (let i = currentPage - delta; i <= currentPage + delta; i++) {
        if (i < lastPage && i > 1) {
            range.push(i);
        }
    }
    range.push(lastPage);

    // Tambahkan titik-titik (...) jika ada lompatan
    for (let i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }

    // Render Angka
    rangeWithDots.forEach(page => {
        if (page === '...') {
            const dots = document.createElement('span');
            dots.className = 'px-3 py-1 text-gray-500';
            dots.innerText = '...';
            paginationControls.appendChild(dots);
        } else {
            const isActive = page === currentPage;
            const btn = createPaginationButton(page, page, isActive, false);
            paginationControls.appendChild(btn);
        }
    });

    // 3. Tombol NEXT (>)
    const nextBtn = createPaginationButton('»', currentPage + 1, false, currentPage === lastPage);
    paginationControls.appendChild(nextBtn);
}

// Helper: Membuat Element Tombol
function createPaginationButton(label, pageNumber, isActive, isDisabled) {
    const btn = document.createElement('button');

    // Styling Tailwind
    let baseClass = 'px-3 py-1 mx-1 border rounded text-sm transition-colors duration-200 ';

    if (isActive) {
        // Style Halaman Aktif (Biru)
        btn.className = baseClass + 'bg-blue-600 text-white border-blue-600 cursor-default';
    } else if (isDisabled) {
        // Style Disabled (Abu-abu)
        btn.className = baseClass + 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed';
        btn.disabled = true;
    } else {
        // Style Halaman Biasa (Putih)
        btn.className = baseClass + 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:text-blue-600';
        btn.onclick = () => {
            // Kita harus menyusun URL manual agar page terganti
            // tapi filter pencarian/sorting tetap terbawa
            const targetUrl = new URL(baseUrl);
            targetUrl.searchParams.set('page', pageNumber);

            // Panggil fetchData dengan URL baru
            fetchData(targetUrl.toString());
        };
    }

    btn.innerText = label;
    return btn;
}

            // Event Listeners
            // Debounce untuk Search agar tidak spam request
            inputSearch.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData(); // Reset ke halaman 1 dengan kata kunci baru
                }, 500); // Tunggu 500ms setelah mengetik
            });

            inputJenis.addEventListener('change', () => fetchData());
            inputSort.addEventListener('change', () => fetchData());

            // Load data pertama kali saat halaman dibuka
            fetchData();
        });
    </script>
</x-app-layout>