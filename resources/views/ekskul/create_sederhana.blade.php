<x-app-layout>
    <x-slot name="header">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input Massal Laporan') }}
            </h2>
            <a href="{{ route('ekskul.manage_details', $spj->belanja_id) }}"
                class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition shadow-sm">
                &larr; Batal / Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- HEADER INFO SPJ --}}
            <div
                class="bg-white border border-gray-100 p-6 rounded-xl shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                <div class="z-10">
                    <h3 class="font-bold text-gray-900 text-xl mb-1">{{ $spj->ekskul->nama }}</h3>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Pelatih: <span class="font-medium text-gray-700">{{ $spj->rekanan->nama_rekanan }}</span>
                    </div>
                </div>
                <div class="flex gap-8 z-10 bg-gray-50 px-6 py-3 rounded-lg border border-gray-100">
                    <div class="text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Kuota</span>
                        <span class="text-lg font-bold text-indigo-600">{{ $spj->jumlah_pertemuan }}</span>
                    </div>
                    <div class="w-px h-10 bg-gray-200"></div>
                    <div class="text-center">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Terisi</span>
                        <span class="text-lg font-bold text-gray-700">{{ $spj->details->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- PERUBAHAN ACTION ROUTE DISINI -->
            <form action="{{ route('ekskul.store_sederhana') }}" method="POST" enctype="multipart/form-data"
                id="bulkForm">
                @csrf
                <input type="hidden" name="spj_ekskul_id" value="{{ $spj->id }}">
                <input type="hidden" name="signature" id="signatureInput">

                {{-- Global Setting --}}
                <div class="hidden">
                    {{-- Default Jam Kegiatan agar Controller tetap berjalan --}}
                    <input type="number" name="jam_global" value="15">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- LANGKAH 1: INPUT JSON --}}
                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">1</span>
                                Masukkan Materi (JSON)
                            </h4>

                            <p class="text-xs text-gray-500 mb-3">Sistem akan membuatkan baris form otomatis berdasarkan
                                JSON ini.</p>

                            <textarea name="materi_json" id="jsonArea" rows="10"
                                class="w-full text-sm font-mono bg-slate-800 text-emerald-400 border-none rounded-lg focus:ring-emerald-500 shadow-inner p-4 leading-relaxed"
                                placeholder='[
  "Latihan Fisik",
  "Latihan Teknik",
  "Game Internal"
]' required></textarea>

                            <button type="button" onclick="generateFormDariJSON()"
                                class="mt-4 w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                                Proses JSON &rarr;
                            </button>
                        </div>
                    </div>

                    {{-- LANGKAH 2: FORM DINAMIS & TTD --}}
                    <div class="lg:col-span-8 space-y-6">

                        {{-- Tabel Form Dinamis --}}
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">2</span>
                                Lengkapi Tanggal & Foto
                            </h4>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 text-sm border-b">
                                            <th class="p-3 font-semibold w-10">No</th>
                                            <th class="p-3 font-semibold">Materi Latihan</th>
                                            <th class="p-3 font-semibold w-40">Tanggal</th>
                                            <th class="p-3 font-semibold w-56">Foto Kegiatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="containerBaris">
                                        <tr>
                                            <td colspan="4"
                                                class="p-8 text-center text-gray-400 text-sm border-b border-dashed">
                                                Silakan input JSON di kolom kiri lalu klik <strong>"Proses
                                                    JSON"</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Area Tanda Tangan & Submit --}}
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" id="areaSubmit"
                            style="display: none;">
                            <h4 class="font-bold text-gray-800 mb-2">Tanda Tangan Pelatih</h4>
                            <p class="text-xs text-gray-500 mb-4">Wajib ditandatangani sebelum menyimpan data.</p>

                            <div
                                class="border-2 border-dashed border-gray-300 bg-gray-50 rounded-lg relative w-full overflow-hidden flex justify-center mb-6">
                                <canvas id="signaturePad" width="400" height="150"
                                    class="cursor-crosshair bg-white w-full max-w-md mx-auto border-x"></canvas>
                                <button type="button" onclick="clearSignature()"
                                    class="absolute top-2 right-2 bg-red-100 text-red-600 hover:bg-red-200 text-[10px] px-2 py-1 rounded font-bold">
                                    Hapus TTD
                                </button>
                            </div>

                            <button type="submit" id="btnSubmit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-lg shadow-md transition-all text-lg flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Laporan
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === LOGIKA GENERATE FORM DARI JSON ===
        function generateFormDariJSON() {
            const jsonText = document.getElementById('jsonArea').value;
            const container = document.getElementById('containerBaris');
            const areaSubmit = document.getElementById('areaSubmit');

            try {
                // Parsing text ke array JSON
                const listMateri = JSON.parse(jsonText);

                // Validasi harus array
                if (!Array.isArray(listMateri)) {
                    throw new Error("Format harus berupa Array yang diawali [ dan diakhiri ]");
                }

                if (listMateri.length === 0) {
                    throw new Error("Array JSON tidak boleh kosong.");
                }

                // Cek kuota sisa (Informasi Opsional)
                const kuotaTotal = {{ $spj->jumlah_pertemuan }};
                const terisi = {{ $spj->details->count() }};
                const sisaKuota = kuotaTotal - terisi;

                if (listMateri.length > sisaKuota) {
                    alert(`Peringatan: Jumlah materi (${listMateri.length}) melebihi sisa kuota (${sisaKuota}). Sisa materi akan diabaikan oleh sistem.`);
                }

                // Bersihkan tabel
                container.innerHTML = '';

                // Loop dan buat baris form
                listMateri.forEach((materi, index) => {
                    const tr = document.createElement('tr');
                    tr.className = "border-b hover:bg-gray-50 transition";
                    tr.innerHTML = `
                        <td class="p-3 text-sm font-bold text-gray-500 text-center">${index + 1}</td>
                        <td class="p-3">
                            <div class="text-sm font-medium text-gray-800 line-clamp-2" title="${materi}">${materi}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">JSON Index: [${index}]</div>
                        </td>
                        <td class="p-3">
                            <input type="date" name="tanggals[]" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 py-1.5 shadow-sm">
                        </td>
                        <td class="p-3">
                            <input type="file" name="foto_kegiatan[]" accept="image/*" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </td>
                    `;
                    container.appendChild(tr);
                });

                // Tampilkan tombol submit & canvas tanda tangan
                areaSubmit.style.display = 'block';

            } catch (e) {
                alert("Gagal memproses JSON!\nPastikan format benar (contoh: [\"Materi 1\", \"Materi 2\"]).\nError: " + e.message);
            }
        }

        // === LOGIKA SIGNATURE PAD ===
        const canvas = document.getElementById('signaturePad');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        }

        const startDrawing = (e) => { e.preventDefault(); drawing = true; const pos = getPos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); }
        const stopDrawing = () => { drawing = false; ctx.beginPath(); }
        const draw = (e) => {
            if (!drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#0f172a';
            ctx.lineTo(pos.x, pos.y); ctx.stroke();
        }

        canvas.addEventListener('mousedown', startDrawing); canvas.addEventListener('mouseup', stopDrawing); canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('touchstart', startDrawing); canvas.addEventListener('touchend', stopDrawing); canvas.addEventListener('touchmove', draw);

        function clearSignature() { ctx.clearRect(0, 0, canvas.width, canvas.height); }

        // === INTERCEPT SUBMIT UNTUK VALIDASI CANVAS ===
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width; blank.height = canvas.height;

            if (canvas.toDataURL() === blank.toDataURL()) {
                e.preventDefault();
                alert('Tanda tangan pelatih wajib diisi!');
            } else {
                // Simpan base64 ke input hidden
                document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
            }
        });
    </script>
</x-app-layout>