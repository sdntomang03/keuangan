<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Volume Tersedia untuk Penyesuaian</h2>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                {{ strtoupper($anggaran->singkatan) }} {{ $anggaran->tahun }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
                <p>Jenis {{ ucfirst($jenis) }}. Untuk memindahkan volume antarbulan, kurangi volume bulan sumber dan tambahkan volume bulan tujuan. Total volume setelah penyesuaian tidak boleh melebihi total sisa volume setahun. PPN 12% hanya dihitung jika komponen RKAS memiliki pajak.</p>
                <a href="{{ route('rkas.penyesuaian-pagu', array_filter(['kegiatan' => $kegiatanDipilih, 'keterangan' => $keteranganDipilih, 'tw' => $twDipilih])) }}" class="shrink-0 font-bold underline">Pilih ulang komponen</a>
            </div>
            @cannot('kelola-anggaran')
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Anda dapat melihat pratinjau ini, tetapi perlu hak kelola-anggaran untuk menyimpan catatan penyesuaian.
                </div>
            @endcannot

            @can('kelola-anggaran')
            <form action="{{ route('rkas.penyesuaian-pagu.simpan') }}" method="POST">
                @csrf
            @else
            <div>
            @endcan
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                @if ($kegiatanDipilih)
                    <input type="hidden" name="kegiatan" value="{{ $kegiatanDipilih }}">
                @endif
                @if ($keteranganDipilih !== '')
                    <input type="hidden" name="keterangan" value="{{ $keteranganDipilih }}">
                @endif
                @foreach ($twDipilih as $tw)
                    <input type="hidden" name="tw[]" value="{{ $tw }}">
                @endforeach
            @foreach ($komponen as $item)
                <section class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800"
                    data-component data-price="{{ (float) $item->hargasatuan }}" data-tax="{{ (float) $item->totalpajak > 0 ? $tarifPajak : 0 }}">
                    <input type="hidden" name="komponen[]" value="{{ $item->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-200 pb-4 dark:border-gray-700">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                                {{ $item->kegiatan->kodegiat ?? $item->idbl ?? 'Kegiatan tidak terdefinisi' }}
                                · {{ $item->kegiatan->namagiat ?? 'Kegiatan tidak terdefinisi' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $item->namakomponen ?: 'Komponen tanpa nama' }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->spek ?: 'Tanpa spesifikasi' }} · {{ $item->satuan ?: 'Satuan tidak diatur' }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Harga satuan: Rp{{ number_format((float) $item->hargasatuan, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ $twDipilih === [] ? 'Pagu setahun' : 'Pagu TW dipilih' }}</span>
                            <strong class="text-right text-gray-800 dark:text-gray-100">Rp{{ number_format((float) $item->pagu_setahun, 0, ',', '.') }}</strong>
                            <span class="text-gray-500 dark:text-gray-400">{{ $twDipilih === [] ? 'Realisasi setahun' : 'Realisasi TW dipilih' }}</span>
                            <strong class="text-right text-gray-800 dark:text-gray-100">Rp{{ number_format((float) $item->realisasi_setahun, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        @for ($bulan = 1; $bulan <= 12; $bulan++)
                            <label class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Bulan {{ $bulan }}</span>
                                <span class="mt-2 block text-xs text-gray-500 dark:text-gray-400">Volume AKB / Satuan</span>
                                <span class="mt-0.5 block text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ number_format($item->volume_akb->get($bulan, 0), 2, ',', '.') }} {{ $item->satuan }}
                                </span>
                                <span class="mt-2 block text-xs text-gray-500 dark:text-gray-400">Sisa volume belum direalisasi</span>
                                <span class="mt-0.5 block text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ number_format($item->volume_tersisa->get($bulan, 0), 2, ',', '.') }} {{ $item->satuan }}
                                </span>
                                <span class="mt-2 block text-xs font-semibold text-indigo-700 dark:text-indigo-300">Volume setelah penyesuaian</span>
                                <input type="number" min="0"
                                    step="0.01" required value="{{ $item->volume_tersisa->get($bulan, 0) }}"
                                    name="volume[{{ $item->id }}][{{ $bulan }}]"
                                    data-volume-input data-volume-awal="{{ $item->volume_tersisa->get($bulan, 0) }}"
                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                    @disabled(! auth()->user()->can('kelola-anggaran'))
                                    aria-label="Volume digeser bulan {{ $bulan }} untuk {{ $item->namakomponen }}">
                            </label>
                        @endfor
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-x-6 gap-y-2 border-t border-gray-200 pt-4 text-sm dark:border-gray-700">
                        <p class="text-gray-600 dark:text-gray-300">Volume dilepas: <strong data-total-volume class="text-gray-900 dark:text-white">0</strong> {{ $item->satuan }}</p>
                        <p class="text-gray-600 dark:text-gray-300">Nilai volume dilepas: <strong data-total-net class="text-gray-900 dark:text-white">Rp0</strong></p>
                        <p class="text-gray-600 dark:text-gray-300">PPN {{ (float) $item->totalpajak > 0 ? $tarifPajak.'%' : 'tidak dikenakan' }}: <strong data-total-tax class="text-gray-900 dark:text-white">Rp0</strong></p>
                        <p class="text-gray-600 dark:text-gray-300">Pagu dapat digeser/diubah: <strong data-total-gross class="text-indigo-700 dark:text-indigo-300">Rp0</strong></p>
                        <p class="text-gray-600 dark:text-gray-300">Nilai dialokasikan ke bulan lain: <strong data-total-allocated class="text-gray-900 dark:text-white">Rp0</strong></p>
                    </div>
                    <p data-volume-balance class="mt-2 text-right text-xs font-semibold text-green-700 dark:text-green-300"></p>
                </section>
            @endforeach
                <div class="sticky bottom-0 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                    <a href="{{ route('rkas.penyesuaian-pagu') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Kembali
                    </a>
                    @can('kelola-anggaran')
                    <button type="submit" data-save-adjustment class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Simpan Catatan Penyesuaian
                    </button>
                    @endcan
                </div>
            @can('kelola-anggaran')
            </form>
            @else
            </div>
            @endcan
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-component]').forEach((component) => {
            const inputs = component.querySelectorAll('[data-volume-input]');
            const updateTotals = () => {
                let volumeReleased = 0;
                let volumeAllocated = 0;
                inputs.forEach((input) => {
                    const difference = Number(input.dataset.volumeAwal) - (Number(input.value) || 0);
                    volumeReleased += Math.max(0, difference);
                    volumeAllocated += Math.max(0, -difference);
                });
                const subtotal = volumeReleased * Number(component.dataset.price);
                const tax = subtotal * Number(component.dataset.tax) / 100;
                const allocated = volumeAllocated * Number(component.dataset.price) * (1 + Number(component.dataset.tax) / 100);
                const format = (value) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
                const rupiah = (value) => 'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);

                component.querySelector('[data-total-volume]').textContent = format(volumeReleased);
                component.querySelector('[data-total-net]').textContent = rupiah(subtotal);
                component.querySelector('[data-total-tax]').textContent = rupiah(tax);
                component.querySelector('[data-total-gross]').textContent = rupiah(subtotal + tax);
                component.querySelector('[data-total-allocated]').textContent = rupiah(allocated);

                const form = component.closest('form');
                if (form) {
                    const isBalanced = Array.from(form.querySelectorAll('[data-component]')).every((section) => {
                        let released = 0;
                        let allocatedVolume = 0;
                        section.querySelectorAll('[data-volume-input]').forEach((input) => {
                            const difference = Number(input.dataset.volumeAwal) - (Number(input.value) || 0);
                            released += Math.max(0, difference);
                            allocatedVolume += Math.max(0, -difference);
                        });

                        return allocatedVolume <= released + 0.000001;
                    });
                    const balanceMessage = component.querySelector('[data-volume-balance]');
                    balanceMessage.textContent = isBalanced
                        ? 'Volume seimbang: pemindahan ditutup oleh volume yang dilepas.'
                        : 'Volume tujuan melebihi volume yang dilepas. Kurangi volume bulan sumber.';
                    balanceMessage.classList.toggle('text-red-700', !isBalanced);
                    balanceMessage.classList.toggle('dark:text-red-300', !isBalanced);
                    balanceMessage.classList.toggle('text-green-700', isBalanced);
                    balanceMessage.classList.toggle('dark:text-green-300', isBalanced);
                    form.querySelector('[data-save-adjustment]').disabled = !isBalanced;
                }
            };

            inputs.forEach((input) => input.addEventListener('input', () => {
                const value = Number(input.value);

                if (input.value !== '' && value < 0) {
                    input.value = '0';
                }

                updateTotals();
            }));
            updateTotals();
        });
    </script>
</x-app-layout>
