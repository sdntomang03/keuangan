<x-app-layout>
    <x-slot name="header">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Input Massal Laporan (Simple)') }}
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

            <form action="{{ route('ekskul.store_sederhana') }}" method="POST" enctype="multipart/form-data"
                id="bulkForm">
                @csrf
                <input type="hidden" name="spj_ekskul_id" value="{{ $spj->id }}">

                {{-- Global Setting --}}
                <div class="hidden">
                    {{-- Default Jam Kegiatan agar Controller tetap berjalan --}}
                    <input type="number" name="jam_global" value="15">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- LANGKAH 1: INPUT JSON & AI --}}
                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">1</span>
                                Masukkan Materi (JSON)
                            </h4>

                            <!-- Input API Key -->
                            <div
                                class="mb-3 bg-slate-50 p-3 rounded-lg border border-slate-200 flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4l-4 4Z" />
                                    <path d="m21 2-9.6 9.6" />
                                </svg>
                                <input type="password" id="apiKey" value="{{ env('GEMINI_API_KEY', '') }}"
                                    placeholder="API Key Gemini..."
                                    class="outline-none bg-transparent text-xs w-full focus:ring-0 text-slate-700 border-none p-0">
                            </div>

                            <!-- Tombol Generate AI -->
                            <button type="button" onclick="generateMateriAI()" id="btnGenAI"
                                class="mb-4 w-full bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-bold py-2.5 px-4 rounded-lg shadow-sm transition-colors flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span id="txtGenAI">Buat Materi Otomatis (AI)</span>
                            </button>

                            <p class="text-xs text-gray-500 mb-2">Atau buat/edit manual struktur JSON di bawah ini:</p>

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

                    {{-- LANGKAH 2: FORM DINAMIS --}}
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
                                            <th class="p-3 font-semibold w-56">Foto Kegiatan (Opsional)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="containerBaris">
                                        <tr>
                                            <td colspan="4"
                                                class="p-8 text-center text-gray-400 text-sm border-b border-dashed">
                                                Silakan input JSON di kolom kiri lalu klik <strong>"Proses
                                                    JSON"</strong> atau gunakan fitur AI.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Area Submit --}}
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" id="areaSubmit"
                            style="display: none;">
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
        // Data SPJ untuk keperluan Validasi & AI
        const kuotaTotal = {{ $spj->jumlah_pertemuan }};
        const terisi = {{ $spj->details->count() }};
        const sisaKuota = kuotaTotal - terisi;
        const namaEkskul = "{{ $spj->ekskul->nama }}";

        // === LOGIKA GENERATE MATERI DENGAN AI ===
        async function generateMateriAI() {
            const apiKey = document.getElementById('apiKey').value.trim();
            if (!apiKey) {
                alert("Silakan masukkan Gemini API Key terlebih dahulu!");
                return;
            }

            if (sisaKuota <= 0) {
                alert("Kuota pertemuan sudah penuh! Tidak perlu generate materi lagi.");
                return;
            }

            const btn = document.getElementById('btnGenAI');
            const txt = document.getElementById('txtGenAI');
            btn.disabled = true;
            txt.innerText = "AI Sedang Berpikir...";

            const prompt = `Buatkan daftar materi kegiatan ekstrakurikuler "${namaEkskul}" sebanyak ${sisaKuota} pertemuan.
            Keluarkan HANYA dalam format array JSON string biasa tanpa format markdown (tanpa awalan \`\`\`json).
            Contoh balasan yang valid:
            ["Materi pertemuan 1", "Materi pertemuan 2"]`;

            try {
                const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-goog-api-key': apiKey },
                    body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }], generationConfig: { temperature: 0.7 } })
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error?.message || "Error API Gemini.");

                let textResult = data.candidates[0].content.parts[0].text;

                // Membersihkan markdown
                textResult = textResult.replace(/```json/gi, '').replace(/```/g, '').trim();

                // Masukkan hasil ke text area
                document.getElementById('jsonArea').value = textResult;

                // Otomatis men-trigger pembuatan baris form tabel di kanan
                generateFormDariJSON();

            } catch (error) {
                alert("Gagal memanggil AI: " + error.message);
            } finally {
                btn.disabled = false;
                txt.innerText = "Buat Materi Otomatis (AI)";
            }
        }

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
                            <input type="file" name="foto_kegiatan[]" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </td>
                    `;
                    container.appendChild(tr);
                });

                // Tampilkan tombol submit
                areaSubmit.style.display = 'block';

            } catch (e) {
                alert("Gagal memproses JSON!\nPastikan format benar (contoh: [\"Materi 1\", \"Materi 2\"]).\nError: " + e.message);
            }
        }
    </script>
</x-app-layout>