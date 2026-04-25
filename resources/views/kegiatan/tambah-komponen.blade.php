<x-manual-layout>
    <div class="max-w-7xl mx-auto">
        <nav class="flex mb-4 text-xs font-semibold uppercase tracking-widest text-slate-400">
            <a href="{{ route('kegiatan.index') }}" class="hover:text-indigo-600 transition-colors">Daftar Kegiatan</a>
            <span class="mx-2">/</span>
            <span class="text-slate-600 dark:text-slate-300">Rincian RKAS</span>
        </nav>

        <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg mb-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold uppercase tracking-tight">{{ $kegiatan->nama_kegiatan }}</h1>
                    <p class="text-indigo-200 mt-1 font-medium text-sm">
                        <span class="bg-indigo-800/50 px-2 py-0.5 rounded mr-2">ID: {{ $kegiatan->id_kegiatan }}</span>
                        Sumber Dana: {{ $kegiatan->sumberDana->nama }}
                    </p>
                </div>
            </div>
        </div>

        <div id="section-tambah"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 lg:p-8">
            <h3
                class="text-lg font-bold text-slate-800 dark:text-white mb-6 border-b border-slate-100 dark:border-slate-700 pb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Rincian Multi-Komponen
            </h3>

            <div id="tahap-1-pilih">
                <div class="space-y-4 mb-6">
                    <button type="button" onclick="openModal()"
                        class="w-full py-4 px-4 border-2 border-dashed border-indigo-300 rounded-lg text-indigo-600 font-bold hover:bg-indigo-50 transition-colors flex justify-center items-center shadow-sm">
                        Buka Master Komponen & Pilih
                    </button>
                </div>

                <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 min-h-[96px] mb-6">
                    <div class="flex justify-between items-center border-b border-slate-200 pb-2 mb-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Keranjang Komponen
                            Terpilih:</label>
                        <span id="badge_count"
                            class="bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold hidden">0</span>
                    </div>
                    <ul id="list_komponen_terpilih" class="text-sm font-medium text-slate-600 space-y-2">
                        <li class="italic text-slate-400">Belum ada komponen yang dipilih.</li>
                    </ul>
                </div>

                <button type="button" id="btn_lanjut" onclick="cekDuplikasi()" disabled
                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm uppercase tracking-wide rounded-lg shadow-md transition-all disabled:opacity-50 flex justify-center items-center">
                    Lanjut Isi Rincian Volume &rarr;
                </button>
            </div>

            <div id="tahap-2-form" class="hidden">
                <div class="mb-4 flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-500 uppercase">2. Isi Rincian Perhitungan</label>
                    <button type="button" onclick="kembaliPilih()"
                        class="text-xs font-bold text-rose-500 hover:underline">&larr; Kembali</button>
                </div>

                <form action="{{ route('kegiatan.store_komponen', $kegiatan->id) }}" method="POST" id="form-multi">
                    @csrf

                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                    Uraian Kegiatan <span class="text-rose-500">*</span>
                                </label>
                                <select name="uraian_id" required
                                    class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm p-2.5 bg-slate-50 cursor-pointer">
                                    <option value="">-- Pilih Uraian Kegiatan --</option>
                                    @php
                                    // PERBAIKAN: Memfilter uraian kegiatan berdasarkan sub_program_id
                                    $masterUraian = \App\Models\UraianKegiatan::where('sub_program_id',
                                    $kegiatan->sub_program_id)
                                    ->orderBy('nama_uraian', 'asc')
                                    ->get();
                                    @endphp
                                    @foreach($masterUraian as $ur)
                                    <option value="{{ $ur->id }}" {{ $kegiatan->uraian_kegiatan_id == $ur->id ?
                                        'selected' : '' }}>
                                        {{ $ur->nama_uraian }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                    Daftar Rincian <span class="text-xs font-normal text-slate-400 italic">(Pilih/Ketik
                                        baru)</span>
                                </label>
                                <input type="text" name="nama_rincian" list="list_rincian" required autocomplete="off"
                                    class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm p-2.5 bg-slate-50"
                                    placeholder="Contoh: Alat Peraga IPA">

                                <datalist id="list_rincian">
                                    @php
                                    // Ambil langsung dari tabel master rincian berdasarkan kegiatan ini
                                    $masterRincian = \App\Models\RincianKegiatan::where('kegiatan_manual_id',
                                    $kegiatan->id)
                                    ->orderBy('nama_rincian', 'asc')
                                    ->get();
                                    @endphp

                                    @foreach($masterRincian as $rincian)
                                    <option value="{{ $rincian->nama_rincian }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                        </div>
                    </div>

                    <div id="dynamic_form_container" class="space-y-6 mb-6"></div>

                    <div
                        class="bg-indigo-50 p-5 rounded-lg border border-indigo-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <label
                            class="flex items-center cursor-pointer bg-white p-3 rounded-lg shadow-sm border border-indigo-200">
                            <input type="checkbox" name="pakai_ppn" id="global_ppn"
                                class="w-5 h-5 text-indigo-600 rounded cursor-pointer">
                            <span class="ml-3 text-sm font-bold text-indigo-700 uppercase">Terapkan PPN 12%</span>
                        </label>
                        <div class="text-right">
                            <span class="text-sm font-bold text-slate-500 uppercase block mb-1">Estimasi Total:</span>
                            <span id="grand_total_display" class="text-3xl font-mono font-black text-indigo-700">Rp
                                0</span>
                        </div>
                    </div>
                    <button type="submit" id="btn_submit_final"
                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm uppercase rounded-lg shadow-md transition-colors">
                        Simpan RKAS
                    </button>
                </form>
            </div>
        </div>

        <div id="section-edit" class="hidden bg-white border-2 border-amber-200 rounded-xl shadow-sm p-6 lg:p-8 mb-8">
            <h3
                class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-3 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Multi Komponen
                </div>
                <button type="button" onclick="batalEdit()"
                    class="text-xs font-bold text-rose-500 hover:underline">Batal Edit</button>
            </h3>

            <form action="{{ route('kegiatan.update_multi_komponen', $kegiatan->id) }}" method="POST"
                id="form-edit-multi">
                @csrf
                @method('PUT')
                <div id="dynamic_edit_container" class="space-y-6 mb-6"></div>

                <div
                    class="bg-amber-50 p-5 rounded-lg border border-amber-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <label
                        class="flex items-center cursor-pointer bg-white p-3 rounded-lg shadow-sm border border-amber-200">
                        <input type="checkbox" name="edit_pakai_ppn" id="edit_global_ppn"
                            class="w-5 h-5 text-amber-600 rounded cursor-pointer">
                        <span class="ml-3 text-sm font-bold text-amber-700 uppercase">Terapkan PPN 12%</span>
                    </label>
                    <div class="text-right">
                        <span class="text-sm font-bold text-slate-500 uppercase block mb-1">Estimasi Total Edit:</span>
                        <span id="edit_grand_total_display" class="text-3xl font-mono font-black text-amber-700">Rp
                            0</span>
                    </div>
                </div>
                <button type="submit" id="btn_submit_edit"
                    class="w-full py-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm uppercase rounded-lg shadow-md">
                    Simpan Perubahan RKAS
                </button>
            </form>
        </div>

        <div id="section-tabel"
            class="mt-8 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-12">
            <div
                class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Daftar Rincian Tersimpan
                </h3>
                <div class="flex items-center space-x-3">
                    <button type="button" id="btn_hapus_terpilih" onclick="prosesHapusTerpilih()"
                        class="hidden bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-colors">
                        Hapus Terpilih (<span id="count_hapus">0</span>)
                    </button>
                    <button type="button" id="btn_edit_terpilih" onclick="prosesEditTerpilih()"
                        class="hidden bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition-colors">
                        Edit Terpilih (<span id="count_edit">0</span>)
                    </button>
                    <div
                        class="text-sm font-bold text-slate-600 bg-white py-2 px-4 rounded-lg shadow-sm border border-slate-200">
                        Total Anggaran: <span class="text-emerald-600 ml-1">Rp {{
                            number_format($rincianRkas->sum('total_akhir'), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-5 py-4 text-center w-12"><input type="checkbox" id="checkAllSaved"
                                    class="rounded border-slate-300 text-amber-500 focus:ring-amber-500"></th>
                            <th class="px-5 py-4 text-left text-[11px] font-bold text-slate-500 uppercase">Komponen &
                                Spesifikasi</th>
                            <th class="px-5 py-4 text-center text-[11px] font-bold text-slate-500 uppercase">Rincian &
                                Vol</th>
                            <th class="px-5 py-4 text-right text-[11px] font-bold text-slate-500 uppercase">Harga Satuan
                            </th>
                            <th class="px-5 py-4 text-right text-[11px] font-bold text-slate-500 uppercase">PPN</th>
                            <th class="px-5 py-4 text-right text-[11px] font-bold text-slate-500 uppercase">Total Akhir
                            </th>
                            <th class="px-5 py-4 text-center text-[11px] font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">

                        @forelse($rincianRkas->groupBy('uraian_id') as $uraianId => $uraianGroup)
                        @php
                        // Mengambil nama uraian dari relasi
                        $namaUraian = $uraianGroup->first()->uraian->nama_uraian ?? 'Uraian Tidak Ditemukan';
                        $subtotalUraian = $uraianGroup->sum('total_akhir');
                        @endphp

                        <tr class="bg-indigo-50/80 border-y border-indigo-100">
                            <td colspan="7" class="px-5 py-3">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-xs font-black text-indigo-800 uppercase tracking-wider flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                        {{ $namaUraian }}
                                    </span>
                                    <span class="text-xs font-bold text-indigo-700">Rp {{ number_format($subtotalUraian,
                                        0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach($uraianGroup->groupBy('rincian_kegiatan_id') as $rincianId => $rincianGroup)
                        @php
                        // Mengambil nama rincian dari relasi
                        $namaRincian = $rincianGroup->first()->rincianKegiatan->nama_rincian ?? 'Rincian Tidak
                        Ditemukan';
                        $subtotalRincian = $rincianGroup->sum('total_akhir');
                        @endphp

                        <tr class="bg-slate-50/80">
                            <td colspan="7" class="px-5 py-2 pl-12">
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] font-bold text-slate-700 flex items-center">
                                        <svg class="w-3 h-3 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        {{ $namaRincian }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500">Rp {{
                                        number_format($subtotalRincian, 0, ',', '.') }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach($rincianGroup as $index => $rkas)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 text-center pl-6">
                                <input type="checkbox"
                                    class="chk-saved rounded border-slate-300 text-amber-500 focus:ring-amber-500"
                                    value="{{ $rkas->id }}">
                            </td>
                            <td class="px-5 py-4 pl-8">
                                <div class="text-sm font-bold text-slate-800">{{ $rkas->nama_komponen }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $rkas->spesifikasi ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-center">
                                <div class="text-[11px] text-slate-500 mb-1 italic">{{ $rkas->keterangan }}</div>
                                <div class="font-bold text-indigo-600 bg-indigo-50 inline-block px-2 py-0.5 rounded">
                                    = {{ $rkas->volume }} <span
                                        class="text-[10px] font-normal text-slate-500 uppercase">{{ $rkas->satuan
                                        }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-slate-600 text-right">{{
                                number_format($rkas->harga_satuan, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-sm text-rose-500 text-right font-medium">{{ $rkas->ppn > 0 ?
                                number_format($rkas->ppn, 0, ',', '.') : '-' }}</td>
                            <td class="px-5 py-4 text-sm font-bold text-emerald-600 text-right">{{
                                number_format($rkas->total_akhir, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-center text-sm font-medium">
                                <form action="{{ route('kegiatan.destroy_komponen', [$kegiatan->id, $rkas->id]) }}"
                                    method="POST" onsubmit="return confirm('Yakin ingin menghapus komponen ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-rose-500 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-md font-bold text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-slate-500 text-sm font-medium">Belum ada
                                rincian tersimpan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modal_komponen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white w-full max-w-5xl max-h-[90vh] rounded-xl shadow-2xl flex flex-col mx-4 transform scale-95 transition-transform duration-300"
            id="modal_content">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-xl">
                <h3 class="text-lg font-bold text-slate-800 flex items-center">Database Master Komponen</h3>
                <button type="button" onclick="closeModal()"
                    class="text-slate-400 hover:text-rose-500 p-1 bg-slate-200/50 hover:bg-rose-100 rounded-lg"><svg
                        class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>
            <div class="p-6 border-b border-slate-200 space-y-4 bg-white z-20">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Rekening Belanja</label>
                    <select id="modal_korek_id"
                        class="block w-full rounded-lg border-slate-300 focus:ring-indigo-500 shadow-sm text-sm">
                        <option value="">-- Silakan Pilih Kode Rekening --</option>
                        @foreach($koreks as $korek)
                        <option value="{{ $korek->id }}">{{ $korek->kode_rekening }} - {{ $korek->uraian_singkat ??
                            $korek->uraian_rekening }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative w-full hidden" id="search_container">
                    <input type="text" id="search_komponen" placeholder="Cari nama komponen..."
                        class="block w-full pl-4 py-3 rounded-lg border-slate-300 shadow-sm bg-slate-50">
                </div>
            </div>
            <div class="overflow-y-auto flex-1 bg-slate-50 p-6 rounded-b-xl" id="table_container">
                <div id="empty_state_modal"
                    class="border-2 border-dashed border-slate-300 rounded-xl p-12 text-center bg-white">
                    <h3 class="text-sm font-bold text-slate-900">Menunggu Pilihan</h3>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 hidden shadow-sm" id="table_wrapper">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-center"><input type="checkbox" id="checkAll"
                                        class="rounded border-slate-300 text-indigo-600"></th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Nama &
                                    Spesifikasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">Harga Satuan
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbody_komponen" class="divide-y divide-slate-200 bg-white"></tbody>
                    </table>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-white rounded-b-xl flex justify-end">
                <button type="button" onclick="konfirmasiPilihanModal()"
                    class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700">Tambahkan
                    Pilihan</button>
            </div>
        </div>
    </div>
    <form id="form-hapus-multi" action="{{ route('kegiatan.destroy_multi_komponen', $kegiatan->id) }}" method="POST"
        class="hidden">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="hapus_ids_input" value="">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Data dari backend untuk referensi fungsi Edit
        const dataRkasTersimpan = @json($rincianRkas);

        document.addEventListener("DOMContentLoaded", function() {
            @if(session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session("success") }}', timer: 3000, showConfirmButton: false });
            @endif
            @if($errors->any())
                let errorMsg = '';
                @foreach($errors->all() as $error) errorMsg += '{{ $error }}<br>'; @endforeach
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan!', html: errorMsg });
            @endif
        });

        // ==========================================
        // LOGIKA TAMBAH BARU (ADD)
        // ==========================================
        const modal = document.getElementById('modal_komponen');
        const modalContent = document.getElementById('modal_content');
        let allKomponenData = [];
        let keranjangKomponen = [];
        const idKegiatan = '{{ $kegiatan->id }}';
        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

        window.openModal = () => { modal.classList.remove('hidden'); setTimeout(() => { modal.classList.remove('opacity-0'); modalContent.classList.remove('scale-95'); }, 10); };
        window.closeModal = () => { modal.classList.add('opacity-0'); modalContent.classList.add('scale-95'); setTimeout(() => { modal.classList.add('hidden'); }, 300); };

        const renderTable = (data) => {
            const tbody = document.getElementById('tbody_komponen');
            tbody.innerHTML = '';
            if (data.length === 0) return tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4">Tidak ada data.</td></tr>`;
            data.forEach(item => {
                const isChecked = keranjangKomponen.find(k => k.id === item.id) ? 'checked' : '';
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 cursor-pointer';
                tr.onclick = (e) => { if(e.target.type !== 'checkbox') { const cb = document.getElementById(`chk_${item.id}`); cb.checked = !cb.checked; } };
                tr.innerHTML = `
                    <td class="px-4 py-3 text-center"><input type="checkbox" id="chk_${item.id}" value="${item.id}" class="chk-komponen rounded border-slate-300 text-indigo-600" ${isChecked}></td>
                    <td class="px-4 py-3 text-sm"><div class="font-bold">${item.nama}</div><div class="text-xs text-slate-500">${item.spesifikasi ?? '-'}</div></td>
                    <td class="px-4 py-3 text-sm">${item.satuan ?? '-'}</td>
                    <td class="px-4 py-3 text-sm font-bold text-emerald-600 text-right">${formatRupiah(item.harga)}</td>
                `;
                tbody.appendChild(tr);
            });
        };

        document.getElementById('modal_korek_id').addEventListener('change', function() {
            if(!this.value) {
                document.getElementById('table_wrapper').classList.add('hidden'); document.getElementById('search_container').classList.add('hidden'); document.getElementById('empty_state_modal').classList.remove('hidden');
                return;
            }
            document.getElementById('empty_state_modal').classList.add('hidden'); document.getElementById('search_container').classList.remove('hidden'); document.getElementById('table_wrapper').classList.remove('hidden');
            document.getElementById('tbody_komponen').innerHTML = `<tr><td colspan="4" class="text-center py-8">Memuat data...</td></tr>`;
            fetch(`/api/komponen-by-korek?korek_id=${this.value}`).then(res => res.json()).then(data => { allKomponenData = data; renderTable(data); });
        });

        document.getElementById('search_komponen').addEventListener('input', function(e) {
            const kw = e.target.value.toLowerCase();
            renderTable(allKomponenData.filter(i => i.nama.toLowerCase().includes(kw) || (i.spesifikasi && i.spesifikasi.toLowerCase().includes(kw))));
        });

        document.getElementById('checkAll').addEventListener('change', function() { document.querySelectorAll('.chk-komponen').forEach(cb => cb.checked = this.checked); });

        window.konfirmasiPilihanModal = () => {
            document.querySelectorAll('.chk-komponen:checked').forEach(cb => {
                const data = allKomponenData.find(i => i.id === parseInt(cb.value));
                if(data && !keranjangKomponen.find(k => k.id === parseInt(cb.value))) keranjangKomponen.push(data);
            });
            updateUIKeranjang(); closeModal(); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Tersimpan ke keranjang', showConfirmButton:false, timer:2000});
        };

        function updateUIKeranjang() {
            const list = document.getElementById('list_komponen_terpilih'), btnLanjut = document.getElementById('btn_lanjut'), badge = document.getElementById('badge_count');
            list.innerHTML = ''; badge.innerText = keranjangKomponen.length;
            if(keranjangKomponen.length === 0) {
                list.innerHTML = '<li class="italic text-slate-400">Belum ada komponen yang dipilih.</li>'; badge.classList.add('hidden'); btnLanjut.disabled = true; return;
            }
            badge.classList.remove('hidden'); btnLanjut.disabled = false;
            keranjangKomponen.forEach(k => {
                list.innerHTML += `
                    <li class="flex justify-between items-center bg-white border border-slate-200 px-3 py-2 rounded shadow-sm">
                        <div class="flex-1 pr-4">
                            <div class="text-sm font-bold text-indigo-700">${k.nama} <span class="text-xs text-slate-400 font-normal">(${formatRupiah(k.harga)})</span></div>
                            <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1 italic">Spek: ${k.spesifikasi ? k.spesifikasi : '-'}</div>
                        </div>
                        <button onclick="hapusDariKeranjang(${k.id})" class="text-rose-500 hover:bg-rose-100 p-1 rounded shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </li>`;
            });
        }
        window.hapusDariKeranjang = (id) => { keranjangKomponen = keranjangKomponen.filter(k => k.id !== id); updateUIKeranjang(); };

        window.cekDuplikasi = async () => {
            const ids = keranjangKomponen.map(k => k.id);
            if(ids.length === 0) return;
            Swal.fire({title: 'Mengecek Database...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                let res = await fetch(`/kegiatan/${idKegiatan}/cek-komponen-duplikat`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ komponen_ids: ids }) });
                let result = await res.json();
                Swal.close();
                if (result.is_duplicate) {
                    Swal.fire({ title: '⚠️ Duplikasi', html: `Komponen sudah ada:<br><b class="text-rose-600">${result.names}</b><br><br>Lanjutkan?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' })
                        .then((r) => { if (r.isConfirmed) generateDynamicForms(); });
                } else generateDynamicForms();
            } catch (error) { Swal.fire('Error', 'Gagal menghubungi server.', 'error'); }
        };

        function generateDynamicForms() {
            document.getElementById('tahap-1-pilih').classList.add('hidden'); document.getElementById('tahap-2-form').classList.remove('hidden');
            const container = document.getElementById('dynamic_form_container'); container.innerHTML = '';
            keranjangKomponen.forEach((k, index) => {
                container.innerHTML += `
                <div class="bg-white border-2 border-indigo-100 rounded-xl p-5 shadow-sm relative pt-8">
                    <div class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200">Item ${index + 1}: ${k.nama}</div>
                    <input type="hidden" name="rincian[${index}][komponen_manual_id]" value="${k.id}">
                    <input type="hidden" name="rincian[${index}][nama_komponen]" value="${k.nama}">
                    <input type="hidden" name="rincian[${index}][harga_satuan]" value="${k.harga}">
                    <input type="hidden" name="rincian[${index}][keterangan]" id="ket_${index}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div class="flex items-center space-x-2">
                                <input type="text" id="rin1_${index}" class="rincian-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" placeholder="Ex: 2 lembar" required> <span class="text-slate-400 font-bold text-xs">x</span>
                                <input type="text" id="rin2_${index}" class="rincian-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" placeholder="3 orang"> <span class="text-slate-400 font-bold text-xs">x</span>
                                <input type="text" id="rin3_${index}" class="rincian-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" placeholder="...">
                            </div>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-200">
                                <span class="text-xs font-bold text-slate-500">VOL:</span>
                                <input type="number" name="rincian[${index}][volume]" id="vol_${index}" readonly class="w-20 py-1 text-right rounded bg-indigo-50 border-none font-bold text-indigo-700">
                            </div>
                        </div>
                        <div class="flex flex-col justify-center space-y-3">
                            <div class="flex justify-between text-sm"><span class="text-slate-500">Harga:</span><span class="font-mono font-bold">${formatRupiah(k.harga)}</span></div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-2"><span class="text-xs font-bold uppercase text-slate-600">Subtotal:</span><span id="subtotal_${index}" class="text-lg font-mono font-black text-slate-700">Rp 0</span></div>
                        </div>
                    </div>
                </div>`;
            });

            keranjangKomponen.forEach((k, index) => {
                const hitungBaris = () => {
                    let texts = []; let totalVol = 1; let adaAngka = false;
                    document.querySelectorAll(`.rincian-input-${index}`).forEach(inp => {
                        const val = inp.value.trim();
                        if (val !== '') { texts.push(val); const numbers = val.match(/\d+/); if (numbers) { totalVol *= parseInt(numbers[0], 10); adaAngka = true; } }
                    });
                    document.getElementById(`ket_${index}`).value = texts.join(' x ');
                    const finalVol = adaAngka ? totalVol : (texts.length > 0 ? 1 : 0);
                    document.getElementById(`vol_${index}`).value = finalVol;
                    document.getElementById(`subtotal_${index}`).innerText = formatRupiah(k.harga * finalVol);
                    hitungGrandTotal();
                };
                document.querySelectorAll(`.rincian-input-${index}`).forEach(inp => inp.addEventListener('input', hitungBaris));
            });
            hitungGrandTotal();
        }

        document.getElementById('global_ppn').addEventListener('change', hitungGrandTotal);
        function hitungGrandTotal() {
            let grandTotal = 0;
            keranjangKomponen.forEach((k, index) => { grandTotal += (k.harga * (parseInt(document.getElementById(`vol_${index}`).value) || 0)); });
            if(document.getElementById('global_ppn').checked) grandTotal += (grandTotal * 0.12);
            document.getElementById('grand_total_display').innerText = formatRupiah(grandTotal);
        }

        window.kembaliPilih = () => { document.getElementById('tahap-2-form').classList.add('hidden'); document.getElementById('tahap-1-pilih').classList.remove('hidden'); };


        // ==========================================
        // LOGIKA EDIT MULTI (DAFTAR TERSIMPAN)
        // ==========================================
        const checkAllSaved = document.getElementById('checkAllSaved');
        const chkSaved = document.querySelectorAll('.chk-saved');
        const btnEditTerpilih = document.getElementById('btn_edit_terpilih');
        const countEdit = document.getElementById('count_edit');

        // === TAMBAHAN UNTUK TOMBOL HAPUS ===
        const btnHapusTerpilih = document.getElementById('btn_hapus_terpilih');
        const countHapus = document.getElementById('count_hapus');
        // ===================================

        // Toggle checkbox master
        checkAllSaved.addEventListener('change', function() {
            chkSaved.forEach(cb => cb.checked = this.checked);
            updateTombolEdit();
        });

        // Toggle tiap baris tabel tersimpan
        chkSaved.forEach(cb => {
            cb.addEventListener('change', updateTombolEdit);
        });

        function updateTombolEdit() {
            const checkedCount = document.querySelectorAll('.chk-saved:checked').length;
            countEdit.innerText = checkedCount;
            countHapus.innerText = checkedCount; // Update angka di tombol hapus

            if(checkedCount > 0) {
                btnEditTerpilih.classList.remove('hidden');
                btnHapusTerpilih.classList.remove('hidden'); // Munculkan tombol hapus
            } else {
                btnEditTerpilih.classList.add('hidden');
                btnHapusTerpilih.classList.add('hidden'); // Sembunyikan tombol hapus
            }
        }

        // Proses Hapus Banyak Data
        window.prosesHapusTerpilih = () => {
            const checkedBoxes = document.querySelectorAll('.chk-saved:checked');
            if (checkedBoxes.length === 0) return;

            Swal.fire({
                title: 'Hapus Rincian?',
                text: `Bapak yakin ingin menghapus ${checkedBoxes.length} rincian ini sekaligus? Data yang dihapus tidak bisa dikembalikan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Warna merah (rose)
                cancelButtonColor: '#94a3b8',  // Warna abu-abu (slate)
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kumpulkan semua ID yang dicentang
                    let ids = [];
                    checkedBoxes.forEach(cb => ids.push(cb.value));

                    // Masukkan array ID tersebut ke dalam input hidden berbentuk teks JSON
                    document.getElementById('hapus_ids_input').value = JSON.stringify(ids);

                    // Eksekusi form
                    document.getElementById('form-hapus-multi').submit();
                }
            });
        };

        // Proses Hapus Banyak Data
        window.prosesHapusTerpilih = () => {
            const checkedBoxes = document.querySelectorAll('.chk-saved:checked');
            if (checkedBoxes.length === 0) return;

            Swal.fire({
                title: 'Hapus Rincian?',
                text: `Bapak yakin ingin menghapus ${checkedBoxes.length} rincian ini sekaligus? Data yang dihapus tidak bisa dikembalikan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Warna merah (rose)
                cancelButtonColor: '#94a3b8',  // Warna abu-abu (slate)
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kumpulkan semua ID yang dicentang
                    let ids = [];
                    checkedBoxes.forEach(cb => ids.push(cb.value));

                    // Masukkan array ID tersebut ke dalam input hidden berbentuk teks JSON
                    document.getElementById('hapus_ids_input').value = JSON.stringify(ids);

                    // Eksekusi form
                    document.getElementById('form-hapus-multi').submit();
                }
            });
        };

        // Proses membuka halaman Edit
        window.prosesEditTerpilih = () => {
            const checkedBoxes = document.querySelectorAll('.chk-saved:checked');
            const editContainer = document.getElementById('dynamic_edit_container');
            editContainer.innerHTML = '';

            let isPpnChecked = false; // Deteksi apakah ada PPN di database awal

            checkedBoxes.forEach((cb, index) => {
                const rkasId = parseInt(cb.value);
                // Cari data RKAS asli dari object PHP yang di json-kan
                const rkasData = dataRkasTersimpan.find(r => r.id === rkasId);

                if(rkasData) {
                    if(parseFloat(rkasData.ppn) > 0) isPpnChecked = true;

                    // Memecah teks keterangan ke 3 kotak text (berdasarkan "x" atau "X")
                    let ketParts = rkasData.keterangan ? rkasData.keterangan.split(/ x | X /) : [];
                    let txt1 = ketParts[0] || '';
                    let txt2 = ketParts[1] || '';
                    let txt3 = ketParts[2] || '';

                    let html = `
                    <div class="bg-white border-2 border-amber-100 rounded-xl p-5 shadow-sm relative pt-8">
                        <div class="absolute -top-3 left-4 bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">
                            Edit ID: ${rkasData.id} - ${rkasData.nama_komponen}
                        </div>

                        <input type="hidden" name="edit_rincian[${index}][id]" value="${rkasData.id}">
                        <input type="hidden" name="edit_rincian[${index}][keterangan]" id="edit_ket_${index}" value="${rkasData.keterangan}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <div class="flex items-center space-x-2">
                                    <input type="text" id="edit_rin1_${index}" class="edit-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" value="${txt1}" placeholder="Ex: 2 lembar" required> <span class="text-slate-400 font-bold text-xs">x</span>
                                    <input type="text" id="edit_rin2_${index}" class="edit-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" value="${txt2}"> <span class="text-slate-400 font-bold text-xs">x</span>
                                    <input type="text" id="edit_rin3_${index}" class="edit-input-${index} block w-full py-2 px-2 rounded-md border-slate-300 text-xs" value="${txt3}">
                                </div>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-200">
                                    <span class="text-xs font-bold text-slate-500">VOL:</span>
                                    <input type="number" name="edit_rincian[${index}][volume]" id="edit_vol_${index}" value="${rkasData.volume}" readonly class="w-20 py-1 text-right rounded bg-amber-50 border-none font-bold text-amber-700">
                                </div>
                            </div>

                            <div class="flex flex-col justify-center space-y-3">
                                <div class="flex justify-between text-sm"><span class="text-slate-500">Harga:</span><span class="font-mono font-bold">${formatRupiah(rkasData.harga_satuan)}</span></div>
                                <div class="flex justify-between items-center border-t border-slate-200 pt-2"><span class="text-xs font-bold uppercase text-slate-600">Subtotal:</span><span id="edit_subtotal_${index}" class="text-lg font-mono font-black text-slate-700">Rp 0</span></div>
                            </div>
                        </div>
                    </div>`;
                    editContainer.innerHTML += html;
                }
            });

            // Set PPN Checkbox
            document.getElementById('edit_global_ppn').checked = isPpnChecked;

            // Pasang event listener ke form Edit
            checkedBoxes.forEach((cb, index) => {
                const rkasId = parseInt(cb.value);
                const rkasData = dataRkasTersimpan.find(r => r.id === rkasId);

                const hitungBarisEdit = () => {
                    let texts = []; let totalVol = 1; let adaAngka = false;
                    document.querySelectorAll(`.edit-input-${index}`).forEach(inp => {
                        const val = inp.value.trim();
                        if (val !== '') { texts.push(val); const numbers = val.match(/\d+/); if (numbers) { totalVol *= parseInt(numbers[0], 10); adaAngka = true; } }
                    });
                    document.getElementById(`edit_ket_${index}`).value = texts.join(' x ');
                    const finalVol = adaAngka ? totalVol : (texts.length > 0 ? 1 : 0);
                    document.getElementById(`edit_vol_${index}`).value = finalVol;
                    document.getElementById(`edit_subtotal_${index}`).innerText = formatRupiah(rkasData.harga_satuan * finalVol);
                    hitungGrandTotalEdit();
                };
                document.querySelectorAll(`.edit-input-${index}`).forEach(inp => inp.addEventListener('input', hitungBarisEdit));
                hitungBarisEdit(); // trigger awal
            });

            // Sembunyikan Area Tambah, Tampilkan Edit
            document.getElementById('section-tambah').classList.add('hidden');
            document.getElementById('section-tabel').classList.add('hidden');
            document.getElementById('section-edit').classList.remove('hidden');
        };

        document.getElementById('edit_global_ppn').addEventListener('change', hitungGrandTotalEdit);
        function hitungGrandTotalEdit() {
            let grandTotal = 0;
            document.querySelectorAll('.chk-saved:checked').forEach((cb, index) => {
                const rkasId = parseInt(cb.value);
                const rkasData = dataRkasTersimpan.find(r => r.id === rkasId);
                const vol = parseInt(document.getElementById(`edit_vol_${index}`).value) || 0;
                grandTotal += (rkasData.harga_satuan * vol);
            });
            if(document.getElementById('edit_global_ppn').checked) grandTotal += (grandTotal * 0.12);
            document.getElementById('edit_grand_total_display').innerText = formatRupiah(grandTotal);
        }

        window.batalEdit = () => {
            document.getElementById('section-edit').classList.add('hidden');
            document.getElementById('section-tambah').classList.remove('hidden');
            document.getElementById('section-tabel').classList.remove('hidden');
        };

    </script>
</x-manual-layout>