<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-6 sm:py-10" x-data="belanjaDetail()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">Buku Kas Umum</p>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Detail Belanja</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500" x-data="{ copied: false }">
                        <span class="font-medium">Nomor bukti</span>
                        <span class="rounded-md bg-white px-2 py-1 font-mono text-xs text-slate-700 ring-1 ring-slate-200">{{ $belanja->no_bukti }}</span>
                        <button type="button"
                            @click="copyText(@js($belanja->no_bukti), 'no-bukti').then(success => copied = success)"
                            title="Salin nomor bukti" aria-label="Salin nomor bukti" class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <svg x-show="copied" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex w-full gap-3 sm:w-auto">
                    <a href="{{ route('belanja.index') }}"
                        class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:flex-none">
                        Kembali
                    </a>
                    <button onclick="window.print()"
                        class="inline-flex flex-1 items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:flex-none">
                        Cetak
                    </button>
                </div>
            </div>

            <div class="space-y-6 sm:space-y-8">

                <div class="space-y-6">

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                        <div class="grid grid-cols-1 gap-7 md:grid-cols-2 md:gap-8">

                            <div class="min-w-0 space-y-4">
                                <div>
                                    <span
                                        class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Informasi
                                        Kegiatan</span>
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <span
                                            class="rounded-md bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">{{
                                            $belanja->korek->ket }}</span>
                                    </div>
                                    <h3 class="text-base font-semibold leading-relaxed text-slate-900 sm:text-lg">
                                        {{ $kegiatan->namagiat ?? 'Kegiatan Tidak Ditemukan' }}
                                    </h3>
                                    <div class="mt-5 border-t border-slate-100 pt-4">
                                        <span
                                            class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Uraian
                                            Transaksi</span>
                                        <div x-data="{ copied: false }">
                                            <p class="text-sm leading-relaxed text-slate-600">
                                                {{ $belanja->uraian }}
                                            </p>
                                            <div class="mt-3 flex items-start justify-between gap-3 rounded-lg bg-slate-50 p-3">
                                                <p class="text-xs leading-relaxed text-slate-700"
                                                x-text="transactionDescription(@js($belanja->uraian), @js($belanja->rekanan->nama_rekanan ?? 'Pihak Ketiga'), @js($sekolah->nama_sekolah ?? ''))"></p>
                                                <button type="button"
                                                @click="copyText(transactionDescription(@js($belanja->uraian), @js($belanja->rekanan->nama_rekanan ?? 'Pihak Ketiga'), @js($sekolah->nama_sekolah ?? '')), 'keterangan').then(success => copied = success)"
                                                    title="Salin keterangan transaksi" aria-label="Salin keterangan transaksi"
                                                    class="shrink-0 rounded-md p-1.5 text-blue-600 transition hover:bg-blue-100 hover:text-blue-800">
                                                    <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <svg x-show="copied" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-0 space-y-5 border-t border-slate-100 pt-6 md:border-l md:border-t-0 md:pl-8 md:pt-0">
                                <div>
                                    <span
                                        class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Rekanan</span>
                                    <p class="mb-1 text-lg font-semibold leading-tight text-slate-900">
                                        {{ $belanja->rekanan->nama_rekanan ?? 'N/A' }}
                                    </p>
                                    <div class="flex flex-col gap-0.5">
                                        <p class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                            <span class="font-semibold text-slate-700">{{ $belanja->rekanan->nama_bank ??
                                                '-' }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span class="font-mono">{{ $belanja->rekanan->no_rekening ?? '-' }}</span>
                                        </p>
                                        <p class="text-xs font-medium text-slate-400">NPWP: {{ $belanja->rekanan->npwp ??
                                            '-' }}</p>
                                    </div>
                                    <div class="mt-5 border-t border-slate-100 pt-4">
                                        <span
                                            class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Rincian
                                            Transaksi</span>
                                        <div x-data="{ copied: false }">
                                            <p class="text-sm leading-relaxed text-slate-600">
                                                {{ $belanja->rincian ?? 'Tidak ada rincian tambahan.' }}
                                            </p>
                                            @if($belanja->rincian)
                                            <button type="button"
                                                @click="copyText(@js($belanja->rincian), 'rincian').then(success => copied = success)"
                                                title="Salin rincian transaksi" aria-label="Salin rincian transaksi"
                                                class="mt-2 rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800">
                                                <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <svg x-show="copied" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Rincian Belanja</h3>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $belanja->rincis->count() }} komponen belanja</p>
                            </div>
                            <button type="button"
                                @click="copyText(@js((string) $belanja->rincis->count()), 'jumlah-barang')"
                                title="Salin jumlah barang" aria-label="Salin jumlah barang"
                                class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800">
                                <svg x-show="!copied['jumlah-barang']" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <svg x-show="copied['jumlah-barang']" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] table-fixed">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="w-12 px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        No.
                                    </th>
                                    <th
                                        class="w-auto px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        Komponen / Spesifikasi
                                    </th>
                                    <th
                                        class="w-52 px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        Vol & Pagu
                                    </th>
                                    <th
                                        class="w-40 px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        Harga Satuan
                                    </th>
                                    <th
                                        class="w-40 px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                        Total Bruto
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($belanja->rincis as $rinci)
                                <tr class="transition-colors hover:bg-slate-50/70">
                                    <td class="py-5 text-center text-sm text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-semibold leading-snug text-slate-800">{{ $rinci->namakomponen
                                            }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $rinci->spek }}</p>
                                    </td>
                                    <td class="px-4 py-5 text-left">
                                        <div class="mb-1.5">
                                            <span class="block text-[8px] uppercase text-gray-400">Vol Belanja</span>
                                            <span class="text-xs font-bold text-gray-800">{{ rtrim(rtrim(number_format((float) $rinci->volume, 2, ',', '.'), '0'), ',') }} {{ $rinci->rkas->satuan ?? '' }}</span>
                                            <button type="button"
                                                @click="copyText(@js(rtrim(rtrim(number_format((float) $rinci->volume, 2, '.', ''), '0'), '.')), 'volume-{{ $rinci->id }}')"
                                                title="Salin jumlah barang" aria-label="Salin jumlah barang"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <svg x-show="!copied['volume-{{ $rinci->id }}']" class="inline h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <svg x-show="copied['volume-{{ $rinci->id }}']" x-cloak class="inline h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="mb-1.5 border-t border-gray-100 pt-1">
                                            <span class="block text-[8px] uppercase text-gray-400">Vol TW / Setahun</span>
                                            <span class="text-[10px] font-mono text-gray-800">{{ rtrim(rtrim(number_format((float) $rinci->total_volume_akb, 2, ',', '.'), '0'), ',') }} / {{ rtrim(rtrim(number_format((float) $rinci->total_volume_setahun, 2, ',', '.'), '0'), ',') }}</span>
                                            <button type="button"
                                                @click="copyText(@js(rtrim(rtrim(number_format((float) $rinci->total_volume_akb, 2, '.', ''), '0'), '.') . ' / ' . rtrim(rtrim(number_format((float) $rinci->total_volume_setahun, 2, '.', ''), '0'), '.')), 'volume-akb-{{ $rinci->id }}')"
                                                title="Salin volume TW dan setahun" aria-label="Salin volume TW dan setahun"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <svg x-show="!copied['volume-akb-{{ $rinci->id }}']" class="inline h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <svg x-show="copied['volume-akb-{{ $rinci->id }}']" x-cloak class="inline h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="border-t border-gray-100 pt-1">
                                            <span class="block text-[8px] uppercase text-gray-400">Pagu TW / Setahun</span>
                                            <span class="text-[10px] font-mono text-gray-700">
                                                Rp {{ number_format($rinci->pagu_dana, 0, ',', '.') }} / Rp {{ number_format($rinci->pagu_setahun, 0, ',', '.') }}
                                            </span>
                                            <div class="flex gap-2">
                                                <button type="button"
                                                    @click="copyText(@js(number_format($rinci->pagu_dana, 0, '.', '')), 'pagu-tw-{{ $rinci->id }}')"
                                                    title="Salin pagu TW" aria-label="Salin pagu TW"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    <svg x-show="!copied['pagu-tw-{{ $rinci->id }}']" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <svg x-show="copied['pagu-tw-{{ $rinci->id }}']" x-cloak class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    @click="copyText(@js(number_format($rinci->pagu_setahun, 0, '.', '')), 'pagu-year-{{ $rinci->id }}')"
                                                    title="Salin pagu setahun" aria-label="Salin pagu setahun"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    <svg x-show="!copied['pagu-year-{{ $rinci->id }}']" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <svg x-show="copied['pagu-year-{{ $rinci->id }}']" x-cloak class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm font-bold text-gray-700 whitespace-nowrap">
                                        <span class="block">Rp {{ number_format($rinci->harga_satuan, 0, ',', '.') }}</span>
                                        <span class="mt-1 block text-[9px] font-medium text-emerald-600">+ {{ rtrim(rtrim(number_format($persenPpn, 2, ',', '.'), '0'), ',') }}% PPN: Rp {{ number_format($rinci->harga_satuan * (1 + $persenPpn / 100), 0, ',', '.') }}</span>
                                        <button type="button"
                                            @click="copyText(@js(number_format($rinci->harga_satuan * (1 + $persenPpn / 100), 0, '.', '')), 'harga-ppn-{{ $rinci->id }}')"
                                            title="Salin harga satuan termasuk PPN" aria-label="Salin harga satuan termasuk PPN"
                                            class="mt-1 text-blue-600 hover:text-blue-800">
                                            <svg x-show="!copied['harga-ppn-{{ $rinci->id }}']" class="inline h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            <svg x-show="copied['harga-ppn-{{ $rinci->id }}']" x-cloak class="inline h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm font-black text-gray-900 whitespace-nowrap">
                                        Rp {{ number_format($rinci->volume * $rinci->harga_satuan, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-slate-900">Informasi Pajak</h3>
                        <p class="mt-1 text-xs text-slate-500">Rincian pajak diterima atau disetor</p>
                    </div>
                    <div class="space-y-3">
                        @if($belanja->pajaks->isEmpty())
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center text-sm text-slate-500">
                            Tidak ada informasi pajak.
                        </p>
                            @endif
                            @foreach($belanja->pajaks as $pajak)
                            @php
                                $namaPajak = $pajak->masterPajak->nama_pajak ?? 'Pajak';
                                $uraianPajak = $pajak->is_setor
                                    ? "Disetor {$namaPajak} atas SPJ {$belanja->uraian}"
                                    : "Diterima {$namaPajak} atas SPJ {$belanja->uraian} dari ".($belanja->rekanan->nama_rekanan ?? '-');
                            @endphp
                            <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-800">{{ $namaPajak }}</span>
                                        <span class="rounded-full {{ $pajak->is_setor ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }} px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">{{ $pajak->is_setor ? 'Disetor' : 'Diterima' }}</span>
                                    </div>
                                    <span class="mt-2 block break-words text-xs leading-relaxed text-slate-600">{{ $uraianPajak }}</span>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <span class="mr-2 whitespace-nowrap text-sm font-bold text-slate-800">Rp {{ number_format($pajak->nominal, 0, ',', '.') }}</span>
                                    <button type="button"
                                        @click="copyText(@js($uraianPajak), 'pajak-{{ $pajak->id }}')"
                                        title="Salin keterangan pajak" aria-label="Salin keterangan pajak"
                                        class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-100 hover:text-blue-800">
                                        <svg x-show="!copied['pajak-{{ $pajak->id }}']" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <svg x-show="copied['pajak-{{ $pajak->id }}']" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button type="button"
                                        @click="copyText(@js(number_format($pajak->nominal, 0, '.', '')), 'nominal-pajak-{{ $pajak->id }}')"
                                        title="Salin nominal pajak" aria-label="Salin nominal pajak"
                                        class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-100 hover:text-blue-800">
                                        <svg x-show="!copied['nominal-pajak-{{ $pajak->id }}']" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <svg x-show="copied['nominal-pajak-{{ $pajak->id }}']" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5">
                            <h3 class="text-base font-semibold text-slate-900">Ringkasan Pembayaran</h3>
                            <p class="mt-1 text-xs text-slate-500">Ikhtisar nilai transaksi</p>
                        </div>

                        <div class="mb-5 space-y-4 border-b border-slate-100 pb-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-500">Nomor Bukti</span>
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="truncate font-mono text-sm font-semibold text-slate-800">{{ $belanja->no_bukti }}</span>
                                    <button type="button"
                                        @click="copyText(@js($belanja->no_bukti), 'no-bukti-ringkasan')"
                                        title="Salin nomor bukti" aria-label="Salin nomor bukti"
                                        class="shrink-0 rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800">
                                        <svg x-show="!copied['no-bukti-ringkasan']" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <svg x-show="copied['no-bukti-ringkasan']" x-cloak class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-500">Subtotal Belanja</span>
                                <span class="text-sm font-semibold text-slate-800">Rp {{ number_format($belanja->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-medium text-slate-500">PPN</span>
                                <span class="text-sm font-semibold text-blue-700">+ Rp {{ number_format($belanja->ppn, 0, ',', '.')
                                    }}</span>
                            </div>
                        </div>

                        <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-blue-700">Nilai Bruto (Kwitansi)</span>
                            <span class="text-2xl font-bold tracking-tight text-slate-900">Rp {{ number_format($belanja->subtotal +
                                $belanja->ppn, 0, ',', '.') }}</span>
                        </div>

                        <div class="mb-5">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-500">Total PPh</span>
                                <span class="text-sm font-semibold text-slate-700">- Rp {{ number_format($belanja->pph, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Netto Diterima Rekanan</span>
                            <span class="text-2xl font-bold tracking-tight text-emerald-800">Rp {{
                                number_format($belanja->transfer, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-center gap-4 text-slate-700">
                            <div class="rounded-lg bg-slate-100 px-3 py-2 text-[10px] font-bold uppercase tracking-wide text-slate-600">
                                INFO
                            </div>
                            <div class="space-y-1 text-xs text-slate-500">
                                <p>Diposting oleh: <strong>{{ $belanja->user->name ?? 'System' }}</strong></p>
                                <p>Pada: {{ $belanja->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        function belanjaDetail() {
            return {
                copied: {},

                transactionDescription(uraian, rekanan, sekolah) {
                    return `Dibayar ${uraian || ''} kepada ${rekanan || 'Pihak Ketiga'} dari ${sekolah || ''}`;
                },

                async copyText(text, key) {
                    const value = String(text ?? '').trim();
                    if (!value) {
                        alert('Tidak ada teks untuk disalin.');
                        return false;
                    }

                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(value);
                        } else {
                            const textArea = document.createElement('textarea');
                            textArea.value = value;
                            textArea.style.position = 'fixed';
                            textArea.style.left = '-999999px';
                            textArea.style.top = '-999999px';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            const copied = document.execCommand('copy');
                            textArea.remove();
                            if (!copied) throw new Error('Browser menolak akses clipboard.');
                        }

                        this.copied[key] = true;
                        setTimeout(() => this.copied[key] = false, 2000);
                        return true;
                    } catch (error) {
                        console.error('Gagal menyalin teks:', error);
                        alert('Gagal menyalin teks ke clipboard.');
                        return false;
                    }
                }
            };
        }
    </script>
</x-app-layout>