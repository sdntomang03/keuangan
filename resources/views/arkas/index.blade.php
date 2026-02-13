<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data RKAS & Rincian Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- TABEL UTAMA --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                                        Kegiatan</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                                        Kode Rekening</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">
                                        Komponen</th>
                                    <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">
                                        Harga Satuan</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">
                                        Satuan</th>
                                    <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">
                                        Total Pagu</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dataRkas as $rkas)
                                @php
                                // Cek apakah relasi arkasChecklist ada datanya?
                                // Jika ada, ambil kolom 'status'. Jika tidak ada (null), anggap false (belum input).
                                $isInputted = $rkas->arkasChecklist->status ?? false;
                                @endphp
                                <tr id="row-{{ $rkas->id }}"
                                    class="hover:bg-gray-200 transition {{ $rkas->arkasChecklist?->status ? 'bg-green-100' : 'bg-white' }}">
                                    <td class="px-4 py-3 text-gray-700 align-top">
                                        {{ $rkas->kegiatan->namagiat ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 align-top">
                                        <div class="font-semibold">{{ $rkas->korek->kode ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $rkas->korek->uraian_singkat ?? '' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 font-medium align-top">
                                        {{ $rkas->namakomponen }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 text-right align-top whitespace-nowrap">
                                        Rp {{ number_format($rkas->hargasatuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 text-center align-top">
                                        {{ $rkas->satuan }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-bold text-green-700 text-right align-top whitespace-nowrap">
                                        Rp {{ number_format($rkas->totalharga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center align-top">
                                        {{-- BUTTON TRIGGER --}}
                                        <button type="button" onclick="openModal('modal-rinci-{{ $rkas->id }}')"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Rincian
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-center align-top w-10">
                                        <div class="flex flex-col items-center justify-center">
                                            <input type="checkbox" id="cb-table-{{ $rkas->id }}" disabled {{-- 1.
                                                Matikan fungsi input --}} {{ $isInputted ? 'checked' : '' }} {{-- 2.
                                                Tambahkan opacity-100 agar warnanya tetap terang meski disabled --}}
                                                class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-default opacity-100 shadow-sm">
                                            <span class="text-[10px] text-gray-400 mt-1">Arkas</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Data RKAS tidak ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $dataRkas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- LOOPING MODAL (DILUAR CARD UTAMA) --}}
    {{-- ========================================================== --}}
    @foreach($dataRkas as $rkas)
    {{-- Perhatikan z-[9999] agar selalu di atas navbar --}}
    <div id="modal-rinci-{{ $rkas->id }}" class="fixed inset-0 z-[9999] hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">

        {{-- Overlay Background (Gelap) --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
            onclick="closeModal('modal-rinci-{{ $rkas->id }}')"></div>

        {{-- Modal Layout Centering --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

            {{-- Modal Panel --}}
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-200">

                {{-- Header Modal --}}
                <div class="bg-indigo-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white" id="modal-title">
                        Detail Rincian RKAS
                    </h3>
                    <button type="button" class="text-white hover:text-gray-200 text-2xl focus:outline-none"
                        onclick="closeModal('modal-rinci-{{ $rkas->id }}')">
                        &times;
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <div
                        class="{{ $rkas->arkasChecklist?->status ? 'bg-green-100 border-green-200' : 'bg-yellow-50 border-yellow-200' }}   rounded p-3 mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="cb-modal-{{ $rkas->id }}" onchange="toggleArkas({{ $rkas->id }})"
                                {{ $rkas->arkasChecklist?->status ? 'checked' : '' }}
                            class="w-6 h-6 text-green-600 border-gray-300 rounded focus:ring-green-500 cursor-pointer">
                            <label for="cb-modal-{{ $rkas->id }}"
                                class="font-bold text-gray-700 cursor-pointer select-none">
                                Tandai Sudah Diinput ke ARKAS
                            </label>
                        </div>
                        <span id="badge-status-{{ $rkas->id }}"
                            class="px-2 py-1 rounded text-xs font-bold {{ $rkas->arkasChecklist?->status ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-500' }}">
                            {{ $rkas->arkasChecklist?->status ? 'SUDAH' : 'BELUM' }}
                        </span>
                    </div>
                    {{-- Detail Atas --}}
                    <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100 mb-6 text-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold">Kegiatan</p>
                                <p class="font-semibold text-gray-800">{{
                                    preg_replace('/(&nbsp;|\s)*Terverifikasi(&nbsp;|\s)*History/i', '', $rkas->namasub)
                                    }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold">Kode Rekening</p>
                                <p class="font-semibold text-gray-800">{{ $rkas->korek->kode ?? '-' }} <span
                                        class="font-normal text-gray-600">({{ $rkas->korek->uraian_singkat ?? ''
                                        }})</span></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold">Komponen</p>
                                <p class="font-semibold text-gray-800">{{ $rkas->namakomponen }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold">Spesifikasi</p>
                                <p class="font-semibold text-gray-800">{{ $rkas->spek }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500 text-xs uppercase font-bold">Harga & Pajak</p>
                                <div class="flex gap-4">
                                    <span>Harga Satuan: <strong>Rp {{ number_format($rkas->hargasatuan, 0, ',', '.')
                                            }}</strong></span>
                                    @if($rkas->totalpajak > 0)
                                    @php $ppn = $rkas->hargasatuan * 0.12; @endphp
                                    <span
                                        class="text-orange-600 text-xs font-bold bg-orange-100 px-2 rounded flex items-center">+
                                        PPN (12%): Rp {{ number_format($ppn, 0, ',', '.') }} = {{
                                        $ppn+$rkas->hargasatuan }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="text-gray-500 text-xs uppercase font-bold">Id Komponen</p>
                                <input type="text" id="input-idkomponen-{{ $rkas->id }}" value="{{ $rkas->idkomponen }}"
                                    class="text-sm border-gray-300 rounded px-2 py-1 w-full md:w-1/2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Masukkan ID Komponen...">

                                <button type="button" onclick="saveIdKomponen({{ $rkas->id }})"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-bold transition flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan
                                </button>

                                {{-- Pesan Feedback Kecil --}}
                                <span id="msg-idkomponen-{{ $rkas->id }}"
                                    class="text-xs font-bold text-green-600 hidden">Tersimpan!</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="text-gray-500 text-xs uppercase font-bold">Satuan</p>
                                <p class="font-semibold text-gray-800">{{ $rkas->satuan }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Bulanan (3 Kolom) --}}
                    <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Rincian Anggaran Kas (AKB)</h4>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto pr-1">
                        @foreach(range(1,12) as $bulan)
                        @php
                        $detail = $rkas->akbRincis->firstWhere('bulan', $bulan);
                        $hasData = $detail && $detail->nominal > 0;
                        $namaBulan = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
                        @endphp

                        <div
                            class="border rounded p-3 flex flex-col justify-between transition-all
                                {{ $hasData ? 'bg-white border-green-300 shadow-sm ring-1 ring-green-100' : 'bg-gray-50 border-gray-100 text-gray-400' }}">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold uppercase">{{ $namaBulan }}</span>
                                @if($hasData)
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                @endif
                            </div>
                            <div class="flex justify-between items-end text-sm">
                                <div class="text-xs">
                                    Vol: <span class="font-bold">{{ $hasData ? $detail->volume : '-' }}</span>
                                </div>
                                <div class="font-bold {{ $hasData ? 'text-gray-800' : '' }}">
                                    {{ $hasData ? 'Rp '.number_format($detail->nominal, 0, ',', '.') : '-' }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Total Bawah --}}
                    <div class="mt-4 bg-gray-800 text-white p-3 rounded-lg flex justify-between items-center shadow">
                        <span class="font-bold text-sm uppercase tracking-wide">Total Setahun</span>
                        <div class="text-right flex items-center gap-4">
                            <span class="text-xs text-gray-300">Total Volume: <b class="text-white">{{
                                    $rkas->akbRincis->sum('volume') }}</b></span>
                            <span class="font-bold text-lg text-green-400">Rp {{
                                number_format($rkas->akbRincis->sum('nominal'), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition"
                        onclick="closeModal('modal-rinci-{{ $rkas->id }}')">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Script JavaScript --}}
    <script>
        // --- FUNGSI MODAL ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if(modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if(modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        document.onkeydown = function(evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) {
                document.querySelectorAll('[id^="modal-rinci-"]').forEach(modal => {
                    modal.classList.add('hidden');
                });
                document.body.style.overflow = 'auto';
            }
        };

        // --- FUNGSI UPDATE STATUS CHECKLIST (OPTIMISTIC UI) ---
        function toggleArkas(id) {
            // 1. Ambil elemen UI
            const row = document.getElementById('row-' + id);
            const cbTable = document.getElementById('cb-table-' + id);
            const cbModal = document.getElementById('cb-modal-' + id);
            const badgeModal = document.getElementById('badge-status-' + id);

            // 2. Tentukan Status Baru berdasarkan input yang diklik
            // Kita ambil status dari salah satu checkbox yang ada
            let isChecked = false;
            if (cbModal) isChecked = cbModal.checked;
            else if (cbTable) isChecked = cbTable.checked;

            // 3. UPDATE VISUAL LANGSUNG (Jangan tunggu server!)
            updateVisualElements(isChecked, row, cbTable, cbModal, badgeModal);

            // 4. Kirim Request ke Server (Background Process)
            fetch(`/arkas/toggle-status/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Jika GAGAL: Balikkan tampilan ke status sebelumnya (Undo)
                    alert('Gagal menyimpan status ke server.');
                    updateVisualElements(!isChecked, row, cbTable, cbModal, badgeModal);
                }
                // Jika SUKSES: Tidak perlu apa-apa, karena visual sudah diupdate di langkah 3
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi internet.');
                // Jika ERROR: Balikkan tampilan ke status sebelumnya (Undo)
                updateVisualElements(!isChecked, row, cbTable, cbModal, badgeModal);
            });
        }

        // Fungsi helper untuk mengubah tampilan CSS/HTML
    function updateVisualElements(isChecked, row, cbTable, cbModal, badgeModal) {
        // Ambil ID dari elemen baris untuk mencari container modal
        // row.id formatnya "row-123", kita butuh "123"
        let id = null;
        if(row) {
            id = row.id.replace('row-', '');
        } else if(cbModal) {
            // Fallback jika row tidak ditemukan (jarang terjadi)
            id = cbModal.id.replace('cb-modal-', '');
        }

        // Ambil elemen Container Status di Modal
        const modalContainer = document.getElementById('modal-status-container-' + id);

        if (isChecked) {
            // -- JIKA STATUS: SUDAH (CHECKED) --

            // 1. Warna Baris Tabel (Hijau)
            if(row) {
                row.classList.remove('bg-white');
                row.classList.add('bg-green-100');
            }

            // 2. Centang Checkbox
            if(cbTable) cbTable.checked = true;
            if(cbModal) cbModal.checked = true;

            // 3. Update Badge di Modal
            if(badgeModal) {
                badgeModal.innerText = "SUDAH";
                badgeModal.className = "px-2 py-1 rounded text-xs font-bold bg-green-200 text-green-800";
            }

            // 4. UBAH BACKGROUND CONTAINER MODAL JADI HIJAU (BARU)
            if(modalContainer) {
                modalContainer.classList.remove('bg-yellow-50', 'border-yellow-200');
                modalContainer.classList.add('bg-green-100', 'border-green-200');
            }

        } else {
            // -- JIKA STATUS: BELUM (UNCHECKED) --

            // 1. Warna Baris Tabel (Putih)
            if(row) {
                row.classList.remove('bg-green-100');
                row.classList.add('bg-white');
            }

            // 2. Uncheck Checkbox
            if(cbTable) cbTable.checked = false;
            if(cbModal) cbModal.checked = false;

            // 3. Update Badge di Modal
            if(badgeModal) {
                badgeModal.innerText = "BELUM";
                badgeModal.className = "px-2 py-1 rounded text-xs font-bold bg-gray-200 text-gray-500";
            }

            // 4. KEMBALIKAN BACKGROUND CONTAINER MODAL JADI KUNING (BARU)
            if(modalContainer) {
                modalContainer.classList.remove('bg-green-100', 'border-green-200');
                modalContainer.classList.add('bg-yellow-50', 'border-yellow-200');
            }
        }
    }
    </script>

    {{-- Script Simpan ID Komponen (Biarkan tetap ada) --}}
    <script>
        function saveIdKomponen(id) {
            const inputField = document.getElementById('input-idkomponen-' + id);
            const msgSpan = document.getElementById('msg-idkomponen-' + id);
            const newValue = inputField.value;

            fetch(`/arkas/update-idkomponen/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ idkomponen: newValue })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    msgSpan.classList.remove('hidden');
                    inputField.classList.add('border-green-500', 'bg-green-50');
                    setTimeout(() => {
                        msgSpan.classList.add('hidden');
                        inputField.classList.remove('border-green-500', 'bg-green-50');
                    }, 2000);
                } else {
                    alert('Gagal menyimpan ID Komponen');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            });
        }
    </script>
</x-app-layout>