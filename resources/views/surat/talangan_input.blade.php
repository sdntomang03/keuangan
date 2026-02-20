<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-gray-800 leading-tight uppercase tracking-widest">
            Pembuatan Surat Talangan
        </h2>
        <p class="text-sm text-gray-500 font-medium">
            Triwulan {{ $triwulanAktif }} — {{ $sekolah->nama_sekolah }}
        </p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div
                class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm font-bold text-sm">
                {{ session('success') }}
            </div>
            @endif

            {{-- FORM INPUT --}}
            <form action="{{ route('surat.talangan.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200 mb-8 overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-black text-gray-800">1. Pengaturan Surat</h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Surat</label>
                            <input type="text" name="nomor_surat" required placeholder="Contoh: 025/UD.02.02"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" required value="{{ date('Y-m-d') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Filter Kode Rekening
                                Belanja</label>
                            <select id="pilihRekening"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold text-indigo-700">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach($listRekening as $rek)
                                <option value="{{ $rek['kodeakun'] }}">{{ $rek['kodeakun'] }} - {{ $rek['nama_rekening']
                                    }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200 mb-8 overflow-hidden" id="areaTabel"
                    style="display: none;">
                    <div class="p-6 bg-gray-800 border-b border-gray-700 flex justify-between items-center">
                        <h3 class="text-md font-bold text-white tracking-widest uppercase">2. Pilih Transaksi yang
                            Ditalangi</h3>
                        <button type="submit"
                            class="bg-indigo-500 hover:bg-indigo-400 text-white font-bold py-1.5 px-4 rounded shadow text-sm">
                            Simpan Surat Talangan
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-center w-10">Pilih</th>
                                    <th class="px-4 py-3 text-left">Tanggal & Uraian Belanja</th>
                                    <th class="px-4 py-3 text-left w-48">ID Pelanggan</th>
                                    <th class="px-4 py-3 text-left w-40">Bulan</th>
                                    <th class="px-4 py-3 text-right w-40">Total Rp</th>
                                </tr>
                            </thead>
                            <tbody id="tabelBelanja" class="bg-white divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                </div>
            </form>

            {{-- TABEL RIWAYAT --}}
            <div class="bg-white shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-black text-gray-800 uppercase tracking-tight">
                        Riwayat Surat Talangan TW {{ $triwulanAktif }}
                    </h3>

                    <a href="{{ route('surat.regenerate_all') }}"
                        onclick="return confirm('PERINGATAN: Proses ini akan mengurutkan ulang SELURUH nomor surat di database berdasarkan tanggal. Lanjutkan?')"
                        class="group relative flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-[10px] font-bold rounded-xl shadow-lg shadow-orange-200 transition-all hover:-translate-y-0.5 hover:shadow-orange-300 overflow-hidden">

                        <div
                            class="absolute inset-0 w-full h-full bg-white/10 group-hover:bg-white/20 transition-colors">
                        </div>

                        <div
                            class="relative bg-white/20 p-1 rounded-full group-hover:rotate-180 transition-transform duration-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <span class="relative tracking-widest uppercase">Reset Nomor</span>
                    </a>
                </div>
                <div class="p-0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left">Nomor Surat</th>
                                <th class="px-6 py-4 text-left">Rekening Tagihan</th>
                                <th class="px-6 py-4 text-left">Bulan Tertanggung</th>
                                <th class="px-6 py-4 text-right">Total Talangan (Rp)</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($riwayatTalangan as $suratId => $grupTalangan)
                            @php
                            // Ambil data surat dari relasi di record pertama grup
                            $dataSurat = $grupTalangan->first()->surat;
                            $totalRupiah = $grupTalangan->sum('jumlah');
                            $kumpulanBulan = $grupTalangan->pluck('bulan')->filter()->unique()->toArray();
                            $teksBulan = implode(', ', $kumpulanBulan);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-indigo-900">{{ $dataSurat->nomor_surat ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $dataSurat->tanggal_surat ?
                                        \Carbon\Carbon::parse($dataSurat->tanggal_surat)->format('d/m/Y') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4">{{ $grupTalangan->first()->korek->ket ?? '-' }}</td>
                                <td class="px-6 py-4 italic text-gray-500">{{ $teksBulan ?: '-' }}</td>
                                <td class="px-6 py-4 text-right font-mono font-bold">{{ number_format($totalRupiah, 0,
                                    ',', '.') }}</td>
                                <td class="px-6 py-4 text-center flex justify-center gap-2">


                                    {{-- Tombol Hapus (Opsional - Jika Anda membuat route destroy) --}}
                                    <form action="{{ route('surat.talangan.destroy', $suratId) }}" method="POST"
                                        onsubmit="return confirm('Hapus seluruh rincian surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded text-xs font-bold hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                    {{-- Tombol Cetak menggunakan surat_id --}}
                                    <a href="{{ route('surat.talangan_pdf', $suratId) }}" target="_blank"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white rounded text-xs font-bold hover:bg-black transition">
                                        Cetak PDF
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada riwayat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataBelanja = @json($listBelanja);
            const selectRekening = document.getElementById('pilihRekening');
            const areaTabel = document.getElementById('areaTabel');
            const tabelBody = document.getElementById('tabelBelanja');

            selectRekening.addEventListener('change', function() {
                const kodeAkun = this.value;
                tabelBody.innerHTML = '';

                if(!kodeAkun) {
                    areaTabel.style.display = 'none';
                    return;
                }

                const filtered = dataBelanja.filter(b => b.kodeakun === kodeAkun);

                if(filtered.length === 0) {
                    areaTabel.style.display = 'none';
                    return;
                }

                areaTabel.style.display = 'block';

                filtered.forEach(b => {
                    const row = `
                        <tr class="hover:bg-indigo-50/50 transition">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="items[${b.id}][selected]" value="1" class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-[10px] text-gray-400 font-mono">${b.tanggal}</div>
                                <div class="font-bold text-gray-800">${b.uraian}</div>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[${b.id}][kodepelanggan]" placeholder="ID Pelanggan" class="w-full border-gray-300 rounded text-sm p-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[${b.id}][bulan]" placeholder="Bulan" class="w-full border-gray-300 rounded text-sm p-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-indigo-700">
                                ${new Intl.NumberFormat('id-ID').format(b.jumlah)}
                                <input type="hidden" name="items[${b.id}][jumlah]" value="${b.jumlah}">
                            </td>
                        </tr>
                    `;
                    tabelBody.insertAdjacentHTML('beforeend', row);
                });
            });
        });
    </script>
</x-app-layout>