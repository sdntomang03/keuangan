<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-black text-xl text-gray-800 leading-tight">
                {{ $laporan->exists ? 'Ubah Laporan Bulanan' : 'Isi Laporan Bulanan' }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">{{ $anggaran->nama_anggaran }} · Tahun {{ $anggaran->tahun }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="mb-1 font-bold">Periksa kembali data berikut:</p>
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            @if(!$laporan->exists)
            <form method="GET" action="{{ route('laporan-bulanan.create') }}" class="mb-4 max-w-sm">
                <label class="block text-sm font-semibold text-gray-700">
                    Pilih Bulan Laporan
                    <select name="bulan" required onchange="this.form.submit()" class="mt-1 w-full rounded-lg border-gray-300">
                        @foreach(range(1, 12) as $bulan)
                        <option value="{{ $bulan }}" @selected((int) old('bulan', $laporan->bulan) === $bulan)>
                            {{ \Carbon\Carbon::createFromDate($anggaran->tahun, $bulan, 1)->locale('id')->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </label>
            </form>
            @endif

            <form method="POST"
                action="{{ $laporan->exists ? route('laporan-bulanan.update', $laporan->id) : route('laporan-bulanan.store') }}"
                class="space-y-6">
                @csrf
                @if($laporan->exists) @method('PUT') @endif

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-base font-black text-gray-800">Identitas Laporan</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="text-sm font-semibold text-gray-700">
                            Bulan
                            <p class="mt-1 rounded-lg border border-gray-300 bg-gray-100 px-3 py-2">
                                {{ \Carbon\Carbon::createFromDate($anggaran->tahun, old('bulan', $laporan->bulan), 1)->locale('id')->translatedFormat('F') }}
                            </p>
                            <input type="hidden" name="bulan" value="{{ old('bulan', $laporan->bulan) }}">
                        </div>
                        <label class="block text-sm font-semibold text-gray-700">
                            Tahun Anggaran
                            <input value="{{ $anggaran->tahun }}" readonly class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700">
                            Nomor Surat
                            <input name="nomor_surat" value="{{ old('nomor_surat', $laporan->nomor_surat) }}"
                                placeholder="Contoh: ............................" class="mt-1 w-full rounded-lg border-gray-300">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700">
                            Sumber Dana
                            <input name="sumber_dana" required value="{{ old('sumber_dana', $laporan->sumber_dana) }}"
                                class="mt-1 w-full rounded-lg border-gray-300">
                        </label>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-1 text-base font-black text-gray-800">Rincian Keuangan</h3>
                    <p class="mb-5 text-xs text-gray-500">Penerimaan dibaca dari transaksi Penerimaan pada bulan ini. Pengeluaran diambil dari Belanja berstatus posted dan dikelompokkan menurut jenis belanja pada Korek.</p>
                    @if($monthlyTotals['belanja_belum_diatur'])
                    <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                        <p class="font-bold">Ada transaksi belanja yang belum memiliki jenis belanja Korek. Laporan belum dapat disimpan sampai klasifikasinya dilengkapi.</p>
                        <ul class="mt-2 list-inside list-disc">
                            @foreach($monthlyTotals['belanja_belum_diatur'] as $item)
                            <li>{{ $item['no_bukti'] }} — rekening {{ $item['rekening'] }} — {{ $item['uraian'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">A. Saldo Awal Dana</label>
                            <input data-money name="saldo_awal" type="number" min="0" step="0.01" required
                                value="{{ old('saldo_awal', $laporan->saldo_awal ?? 0) }}" class="w-full rounded-lg border-gray-300">
                        </div>

                        <div>
                            <h4 class="mb-2 text-sm font-bold text-gray-700">B. Penerimaan Dana</h4>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach([
                                    'penerimaan_tahap_i' => 'Tahap I',
                                    'penerimaan_tahap_ii' => 'Tahap II',
                                    'penerimaan_kinerja' => 'Kinerja',
                                    'penerimaan_lain' => 'Bunga Bank / Pungutan Pajak / Silpa',
                                ] as $field => $label)
                                <label class="block text-sm font-medium text-gray-600">
                                    {{ $label }}
                                    <input data-money name="{{ $field }}" type="number" min="0" step="0.01" readonly
                                        value="{{ $monthlyTotals['totals'][$field] }}" class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100">
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-right text-sm font-bold text-indigo-700">Jumlah Penerimaan: <output data-total-penerimaan>0</output></p>
                        </div>

                        <div>
                            <h4 class="mb-2 text-sm font-bold text-gray-700">C. Pengeluaran Dana</h4>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach([
                                    'pengeluaran_barang_jasa' => 'Belanja Barang dan Jasa',
                                    'pengeluaran_modal_peralatan' => 'Belanja Modal Peralatan dan Mesin',
                                    'pengeluaran_modal_aset_lainnya' => 'Belanja Modal Aset Tetap Lainnya',
                                    'pengeluaran_lain' => 'Biaya Bank / Setoran Pajak / Setoran Bunga',
                                ] as $field => $label)
                                <label class="block text-sm font-medium text-gray-600">
                                    {{ $label }}
                                    <input data-money name="{{ $field }}" type="number" min="0" step="0.01" readonly
                                        value="{{ $monthlyTotals['totals'][$field] }}" class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100">
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-right text-sm font-bold text-indigo-700">Jumlah Pengeluaran: <output data-total-pengeluaran>0</output></p>
                        </div>

                        <div class="rounded-lg bg-indigo-50 p-4">
                            <p class="text-sm font-black text-indigo-900">D. Sisa Dana (A + B - C): <output data-sisa-dana>0</output></p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <label class="block text-sm font-medium text-gray-700">Kas Tunai
                                    <input data-money name="kas_tunai" type="number" min="0" step="0.01" required
                                        value="{{ old('kas_tunai', $laporan->kas_tunai ?? 0) }}" class="mt-1 w-full rounded-lg border-gray-300">
                                </label>
                                <label class="block text-sm font-medium text-gray-700">Rekening Bank
                                    <input data-money name="rekening_bank" type="number" min="0" step="0.01" required
                                        value="{{ old('rekening_bank', $laporan->rekening_bank ?? 0) }}" class="mt-1 w-full rounded-lg border-gray-300">
                                </label>
                            </div>
                        </div>

                        <label class="block text-sm font-medium text-gray-700">
                            E. Penjelasan Selisih
                            <textarea name="penjelasan_selisih" rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ old('penjelasan_selisih', $laporan->penjelasan_selisih) }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 text-base font-black text-gray-800">Tanggal dan Penandatangan</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-gray-700">Kota
                            <input name="kota_surat" required value="{{ old('kota_surat', $laporan->kota_surat) }}"
                                class="mt-1 w-full rounded-lg border-gray-300">
                        </label>
                        <label class="block text-sm font-semibold text-gray-700">Tanggal Surat
                            <input name="tanggal_surat" type="date" required
                                value="{{ old('tanggal_surat', $laporan->tanggal_surat?->format('Y-m-d')) }}"
                                class="mt-1 w-full rounded-lg border-gray-300">
                        </label>
                    </div>
                    <p class="mt-4 text-xs text-gray-500">
                        Nama dan NIP kepala sekolah pada lembar cetak mengikuti data di Pengaturan Sekolah.
                    </p>
                </section>

                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('laporan-bulanan.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700">Batal</a>
                    <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const moneyFields = [...document.querySelectorAll('[data-money]')];
            const format = value => Math.round(value).toLocaleString('id-ID');
            const updateTotals = () => {
                const value = name => Number(document.querySelector(`[name="${name}"]`)?.value || 0);
                const totalPenerimaan = value('penerimaan_tahap_i') + value('penerimaan_tahap_ii') + value('penerimaan_kinerja') + value('penerimaan_lain');
                const totalPengeluaran = value('pengeluaran_barang_jasa') + value('pengeluaran_modal_peralatan') + value('pengeluaran_modal_aset_lainnya') + value('pengeluaran_lain');
                document.querySelector('[data-total-penerimaan]').textContent = format(totalPenerimaan);
                document.querySelector('[data-total-pengeluaran]').textContent = format(totalPengeluaran);
                document.querySelector('[data-sisa-dana]').textContent = format(value('saldo_awal') + totalPenerimaan - totalPengeluaran);
            };
            moneyFields.forEach(field => field.addEventListener('input', updateTotals));
            updateTotals();
        })();
    </script>
</x-app-layout>
