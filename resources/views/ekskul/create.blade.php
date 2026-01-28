<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Input Honor Ekskul (RKAS)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Anggaran: <span class="font-bold text-indigo-600">{{
                        $anggaran->nama_anggaran }}</span></p>
            </div>
            <a href="{{ route('belanja.index') }}"
                class="text-sm text-gray-500 hover:text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ERROR ALERT --}}
            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Gagal Menyimpan Data</h3>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('ekskul.store') }}" method="POST" id="formEkskul">
                @csrf

                {{-- A. INFORMASI UMUM --}}
                <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b pb-2">A. Informasi
                        Transaksi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Tanggal --}}
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Tanggal
                                Transaksi</label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        {{-- No Bukti (BISA DIEDIT) --}}
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Nomor Urut
                                (Opsional)</label>

                            {{-- Hapus attribute 'disabled', ubah type jadi number agar user input angka saja --}}
                            <input type="number" name="no_bukti" placeholder="Auto" value="{{ old('no_bukti') }}"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            <p class="text-xs text-gray-500 mt-1">
                                *Isi angka (misal: <b>15</b>) jika ingin mulai dari nomor tertentu.<br>
                                *Kosongkan untuk nomor otomatis lanjut dari database.
                            </p>
                        </div>

                        {{-- Pajak --}}
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Pajak PPh
                                21</label>
                            <select name="pph21_id" id="pph21_id" onchange="hitungTotal()"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" data-persen="0">-- Tidak Ada --</option>
                                @foreach($pajakPPh21 as $pajak)
                                <option value="{{ $pajak->id }}" data-persen="{{ $pajak->persen }}">
                                    {{ $pajak->nama_pajak }} ({{ $pajak->persen }}%)
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- B. SUMBER DANA --}}
                <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b pb-2">B. Sumber Dana (RKAS)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Kegiatan --}}
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">1. Pilih
                                Kegiatan</label>
                            <select name="idbl" id="idbl" onchange="loadRekening()"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Kegiatan --</option>
                                @foreach($listKegiatan as $giat)
                                <option value="{{ $giat->idbl }}">{{ $giat->namagiat }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rekening --}}
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">2. Pilih Kode
                                Rekening</label>
                            <select name="kodeakun" id="kodeakun" onchange="loadKomponen()"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm bg-gray-50"
                                disabled required>
                                <option value="">-- Pilih Kegiatan Dulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- C. TABEL KOMPONEN --}}
                <div class="p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">C. Rincian Komponen & Pelatih</h3>
                        <span class="text-xs text-gray-500 bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            Komponen diambil otomatis dari RKAS
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                        Aksi</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Komponen</th>
                                    {{-- Kolom Pelatih --}}
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                        Pilih Pelatih</th>
                                    {{-- Kolom Ekskul --}}
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                        Jenis Ekskul</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                        Input Vol</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                id="tabelKomponen">
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">
                                        Silakan pilih Kegiatan dan Kode Rekening terlebih dahulu...
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700 font-bold text-gray-700 dark:text-gray-300">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right">Total Bruto:</td>
                                    <td class="px-4 py-3 text-right" id="labelTotalBruto">Rp 0</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-red-600">Potongan PPh 21:</td>
                                    <td class="px-4 py-3 text-right text-red-600" id="labelTotalPajak">Rp 0</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-green-600 text-lg">Total Netto
                                        (Diterima):</td>
                                    <td class="px-4 py-3 text-right text-green-600 text-lg" id="labelTotalNetto">Rp 0
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="mt-6 flex justify-end gap-3">
                    <button type="reset"
                        class="px-6 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-gray-700 hover:bg-gray-50">
                        Reset
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        SIMPAN TRANSAKSI
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        // Data awal untuk dropdown (dari Controller)
    const daftarEkskul = @json($daftarEkskul);
    const daftarPelatih = @json($pelatih);

    // 1. FUNGSI LOAD KODE REKENING (Berdasarkan Kegiatan)
    function loadRekening() {
        const idbl = document.getElementById('idbl').value;
        const selectAkun = document.getElementById('kodeakun');

        // Reset Dropdown
        selectAkun.innerHTML = '<option value="">Memuat...</option>';
        selectAkun.disabled = true;
        document.getElementById('tabelKomponen').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-400">Pilih Kode Rekening...</td></tr>';
        resetTotal();

        if(!idbl) return;

        fetch(`{{ route('ekskul.get_rekening') }}?idbl=${idbl}`)
            .then(res => res.json())
            .then(data => {
                let opts = '<option value="">-- Pilih Kode Rekening --</option>';
                data.forEach(i => opts += `<option value="${i.kodeakun}">${i.namarekening}</option>`);
                selectAkun.innerHTML = opts;
                selectAkun.disabled = false;
            });
    }

    // 2. FUNGSI LOAD KOMPONEN RKAS (Membuat Baris Tabel)
    function loadKomponen() {
        const idbl = document.getElementById('idbl').value;
        const kodeakun = document.getElementById('kodeakun').value;
        const tabel = document.getElementById('tabelKomponen');

        // Loading State
        tabel.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center animate-pulse">Sedang mengambil data RKAS...</td></tr>';
        resetTotal();

        if(!kodeakun) return;

        fetch(`{{ route('ekskul.get_komponen') }}?idbl=${idbl}&kodeakun=${kodeakun}`)
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    tabel.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Tidak ada komponen tersedia / Pagu Habis untuk Triwulan ini.</td></tr>';
                    return;
                }

                let rows = '';

                // Generate Opsi Dropdown Ekskul
                let ekskulOptions = '<option value="">-- Pilih --</option>';
                daftarEkskul.forEach(e => {
                    ekskulOptions += `<option value="${e.id}">${e.nama}</option>`;
                });

                // Generate Opsi Dropdown Pelatih
                let pelatihOptions = '<option value="">-- Pilih --</option>';
                daftarPelatih.forEach(p => {
                    pelatihOptions += `<option value="${p.id}">${p.nama_rekanan}</option>`;
                });

                data.forEach((item, index) => {
                    const hargaFmt = new Intl.NumberFormat('id-ID').format(item.hargasatuan);

                    rows += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition" id="row_${index}">
                            {{-- Tombol Hapus --}}
                            <td class="px-4 py-3 text-center">
                                <button type="button" onclick="removeRow('row_${index}')" class="text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded transition" title="Hapus Komponen">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>

                            {{-- Nama Komponen --}}
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                ${item.namakomponen} <br>
                                <span class="text-xs text-gray-500">Sisa Pagu: ${item.volume_tersedia}</span>
                                <span class="text-xs text-gray-500">Keterangan: ${item.keterangan}</span>
                                <input type="hidden" name="items[${index}][idblrinci]" value="${item.idblrinci}">
                                <input type="hidden" name="items[${index}][namakomponen]" value="${item.namakomponen}">
                                <input type="hidden" name="items[${index}][harga_satuan]" value="${item.hargasatuan}">
                            </td>

                            {{-- Dropdown Pilih Pelatih (Trigger AJAX) --}}
                            <td class="px-4 py-3">
                                <select name="items[${index}][rekanan_id]"
                                        onchange="getEkskulByPelatih(this, ${index})"
                                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded shadow-sm py-1" required>
                                    ${pelatihOptions}
                                </select>
                            </td>

                            {{-- Dropdown Pilih Ekskul (Target Otomatis) --}}
                            <td class="px-4 py-3">
                                <select name="items[${index}][ref_ekskul_id]"
                                        id="ekskul_select_${index}"
                                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded shadow-sm py-1 bg-gray-50" required>
                                    ${ekskulOptions}
                                </select>
                            </td>

                            {{-- Harga Satuan --}}
                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                Rp ${hargaFmt} <input type="hidden" class="price-val" value="${item.hargasatuan}">
                            </td>

                            {{-- Input Volume --}}
                            <td class="px-4 py-3 text-center">
                                <input type="number" min="1" max="${item.volume_tersedia}" required
                                    class="w-full text-center border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded shadow-sm text-sm py-1 vol-input font-bold text-indigo-600"
                                    name="items[${index}][volume]" value="0"
                                    oninput="hitungTotal()">
                            </td>

                            {{-- Subtotal --}}
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-800 dark:text-gray-200 subtotal-display">
                                Rp 0
                            </td>
                        </tr>
                    `;
                });
                tabel.innerHTML = rows;
            });
    }

    // 3. FUNGSI BARU: REQUEST API SAAT PELATIH BERUBAH
    function getEkskulByPelatih(selectElement, index) {
        const rekananId = selectElement.value;
        const ekskulSelect = document.getElementById(`ekskul_select_${index}`);

        // Visual feedback (loading)
        ekskulSelect.style.opacity = '0.5';

        if (!rekananId) {
            ekskulSelect.value = "";
            ekskulSelect.style.opacity = '1';
            return;
        }

        // Panggil API Laravel
        fetch(`{{ route('ekskul.get_by_pelatih') }}?rekanan_id=${rekananId}`)
            .then(res => res.json())
            .then(data => {
                if (data) {
                    // Jika data ditemukan (data.id adalah ID ekskul)
                    ekskulSelect.value = data.id;

                    // Efek visual 'flash' hijau tanda berhasil
                    ekskulSelect.classList.remove('bg-gray-50');
                    ekskulSelect.classList.add('bg-green-100');
                    setTimeout(() => {
                        ekskulSelect.classList.remove('bg-green-100');
                        ekskulSelect.classList.add('bg-gray-50');
                    }, 500);
                } else {
                    // Jika pelatih tidak punya ekskul terdaftar
                    ekskulSelect.value = "";
                }
            })
            .catch(err => {
                console.error('Gagal mengambil data ekskul:', err);
            })
            .finally(() => {
                ekskulSelect.style.opacity = '1';
            });
    }

    // 4. FUNGSI HAPUS BARIS
    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if(row) {
            row.remove();
            hitungTotal();
        }
    }

    // 5. FUNGSI HITUNG TOTAL
    function hitungTotal() {
        let totalBruto = 0;
        const rows = document.querySelectorAll('#tabelKomponen tr[id^="row_"]');

        rows.forEach(row => {
            const volInput = row.querySelector('.vol-input');
            const priceInput = row.querySelector('.price-val');
            const subDisplay = row.querySelector('.subtotal-display');

            if(volInput && priceInput) {
                const vol = parseFloat(volInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = vol * price;

                totalBruto += subtotal;
                subDisplay.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
            }
        });

        const pajakSelect = document.getElementById('pph21_id');
        const persenPajak = pajakSelect.options[pajakSelect.selectedIndex].dataset.persen || 0;
        const nominalPajak = Math.floor(totalBruto * (persenPajak / 100));
        const totalNetto = totalBruto - nominalPajak;

        document.getElementById('labelTotalBruto').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalBruto);
        document.getElementById('labelTotalPajak').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(nominalPajak);
        document.getElementById('labelTotalNetto').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalNetto);
    }

    // 6. FUNGSI RESET TOTAL
    function resetTotal() {
        document.getElementById('labelTotalBruto').innerText = 'Rp 0';
        document.getElementById('labelTotalPajak').innerText = 'Rp 0';
        document.getElementById('labelTotalNetto').innerText = 'Rp 0';
    }
    </script>
</x-app-layout>
