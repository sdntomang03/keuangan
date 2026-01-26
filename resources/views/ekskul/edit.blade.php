<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Data SPJ') }}
            </h2>
            <a href="{{ route('ekskul.index', $belanja->id) }}" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; Batal
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Error Validasi --}}
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Periksa Kembali Inputan Anda!</strong>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('ekskul.update', $spj->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- WAJIB UNTUK UPDATE --}}

                {{-- Data ID Belanja tetap dikirim untuk redirect --}}
                <input type="hidden" name="belanja_id" value="{{ $belanja->id }}">

                {{-- BAGIAN 1: DATA UMUM --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold mb-4 text-indigo-600">A. Data Umum Kegiatan</h3>

                        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm rounded">
                            <span class="font-bold">Sumber Dana:</span> {{ $belanja->uraian }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Pilih Ekskul (Diisi data lama) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Jenis
                                    Ekstrakurikuler</label>
                                <select name="ref_ekskul_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="">-- Pilih Ekskul --</option>
                                    @foreach($daftarEkskul as $eks)
                                    <option value="{{ $eks->id }}" {{ $spj->ref_ekskul_id == $eks->id ? 'selected' : ''
                                        }}>
                                        {{ $eks->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pelatih (Readonly) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Pelatih /
                                    Rekanan</label>
                                <input type="text"
                                    value="{{ $spj->pelatih->nama }} ({{ $spj->pelatih->npwp ? 'NPWP' : 'Non-NPWP' }})"
                                    class="w-full bg-gray-100 border-gray-300 text-gray-600 rounded-md shadow-sm cursor-not-allowed"
                                    readonly>
                            </div>

                            {{-- TW (Readonly) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Periode
                                    Triwulan</label>
                                <input type="text" value="Triwulan {{ $spj->tw }}"
                                    class="w-full bg-gray-100 border-gray-300 text-gray-600 rounded-md shadow-sm cursor-not-allowed"
                                    readonly>
                            </div>

                            {{-- Honor (Readonly) --}}
                            <div>
                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Honor Per
                                    Pertemuan</label>
                                <input type="text" value="Rp {{ number_format($spj->honor, 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border-gray-300 text-gray-600 rounded-md shadow-sm cursor-not-allowed"
                                    readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN 2: RINCIAN PERTEMUAN --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-indigo-600">B. Rincian & Dokumentasi Pertemuan</h3>
                            <button type="button" onclick="addMeetingRow()"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition">
                                + Tambah Pertemuan
                            </button>
                        </div>

                        <div id="meeting-container" class="space-y-4">

                            {{-- LOOPING DATA LAMA (EXISTING DATA) --}}
                            @foreach($spj->details as $index => $detail)
                            <div
                                class="meeting-row border border-gray-200 dark:border-gray-700 p-4 rounded bg-gray-50 dark:bg-gray-700 relative">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                                    {{-- Tanggal --}}
                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                                        <input type="date" name="pertemuan[{{ $index }}][tanggal]"
                                            value="{{ $detail->tanggal_kegiatan }}"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm" required>
                                    </div>

                                    {{-- Materi --}}
                                    <div class="md:col-span-5">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Materi</label>
                                        <input type="text" name="pertemuan[{{ $index }}][materi]"
                                            value="{{ $detail->materi }}"
                                            class="w-full text-sm border-gray-300 rounded-md shadow-sm" required>
                                    </div>

                                    {{-- Foto (Logic Khusus) --}}
                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Bukti
                                            Foto</label>

                                        {{-- Input Hidden untuk menyimpan path foto lama --}}
                                        <input type="hidden" name="pertemuan[{{ $index }}][old_foto]"
                                            value="{{ $detail->foto_kegiatan }}">

                                        {{-- Tampilkan Link Foto Lama --}}
                                        <div class="mb-1 text-xs">
                                            <a href="{{ asset('storage/'.$detail->foto_kegiatan) }}" target="_blank"
                                                class="text-blue-600 underline">Lihat Foto Saat Ini</a>
                                        </div>

                                        {{-- Input Upload Baru (Tidak required karena sudah ada foto lama) --}}
                                        <input type="file" name="pertemuan[{{ $index }}][foto]"
                                            class="w-full text-sm bg-white border border-gray-300 rounded p-1"
                                            accept="image/*">
                                        <p class="text-[10px] text-gray-500">Biarkan kosong jika tidak ingin mengganti
                                            foto.</p>
                                    </div>

                                    <div class="md:col-span-1 text-right">
                                        <button type="button" onclick="this.closest('.meeting-row').remove()"
                                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded shadow text-xs">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg">
                        Update Data SPJ
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        // Mulai index dari jumlah data yang sudah ada agar tidak bentrok
        let meetingIndex = {{ count($spj->details) }};

        function addMeetingRow() {
            const container = document.getElementById('meeting-container');
            const html = `
            <div class="meeting-row border border-gray-200 dark:border-gray-700 p-4 rounded bg-gray-50 dark:bg-gray-700 relative mt-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold">Tanggal</label>
                        <input type="date" name="pertemuan[${meetingIndex}][tanggal]" class="w-full text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold">Materi</label>
                        <input type="text" name="pertemuan[${meetingIndex}][materi]" class="w-full text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold">Bukti Foto (Baru)</label>
                        <input type="file" name="pertemuan[${meetingIndex}][foto]" class="w-full text-sm bg-white border border-gray-300 rounded p-1" accept="image/*" required>
                    </div>
                    <div class="md:col-span-1 text-right">
                        <button type="button" onclick="this.closest('.meeting-row').remove()" class="bg-red-500 text-white p-2 rounded text-xs">Hapus</button>
                    </div>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            meetingIndex++;
        }
    </script>
</x-app-layout>