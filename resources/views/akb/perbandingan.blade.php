<x-app-layout>
    <div x-data="{ isModalOpen: false }" class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Header & Breadcrumbs --}}
        <div class="mb-8 pb-6 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center">
                        <span class="w-1.5 h-7 bg-indigo-600 rounded-full mr-3"></span>
                        Preview Analisis ARKAS
                    </h2>
                    <p class="text-slate-500 font-medium mt-1 ml-4.5">
                        Alat bantu tinjau untuk mengecek komponen yang perlu diubah atau dihapus. <span
                            class="text-indigo-500 font-bold">(Hanya Mode Baca)</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-bold border border-indigo-100">
                        Anggaran: {{ $anggaran->nama_anggaran ?? 'Tahun Berjalan' }}
                    </span>
                    <button @click="isModalOpen = true"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 text-white font-bold rounded-xl shadow-md transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        Upload & Analisis
                    </button>
                </div>
            </div>
        </div>

        @if ($errors->any() || session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm font-medium text-rose-800 shadow-sm">
            <ul class="list-disc ml-5 space-y-1">
                @if(session('error')) <li>{{ session('error') }}</li> @endif
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        @if($koleksiPerbandingan->isEmpty())
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 text-center">
            <div
                class="w-24 h-24 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Monitor Perubahan Data ARKAS</h3>
            <p class="text-slate-500 max-w-md mx-auto mb-8">Upload file JSON untuk mendeteksi komponen mana saja yang
                berubah pagu, rincian spek, atau perlu dihapus.</p>
            <button @click="isModalOpen = true"
                class="px-6 py-3 bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold rounded-xl hover:bg-indigo-100 transition shadow-sm">
                Mulai Upload Data
            </button>
        </div>
        @else
        @php
        $countBaru = $koleksiPerbandingan->where('status', 'Baru')->count();
        $countBerubah = $koleksiPerbandingan->whereIn('status', ['Berubah Pagu', 'Berubah Rincian', 'Geser
        Jadwal'])->count();
        $countDihapus = $koleksiPerbandingan->where('status', 'Dihapus')->count();
        $totalSelisih = $koleksiPerbandingan->sum('selisih');
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center">
                <div
                    class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Data Baru</p>
                    <h4 class="text-2xl font-black text-emerald-600">{{ $countBaru }}</h4>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-amber-100 shadow-sm flex items-center">
                <div
                    class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Berubah/Geser</p>
                    <h4 class="text-2xl font-black text-amber-500">{{ $countBerubah }}</h4>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-sm flex items-center">
                <div
                    class="w-12 h-12 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Perlu Dihapus</p>
                    <h4 class="text-2xl font-black text-rose-500">{{ $countDihapus }}</h4>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl border {{ $totalSelisih >= 0 ? 'border-indigo-100' : 'border-rose-100' }} shadow-sm flex items-center">
                <div
                    class="w-12 h-12 {{ $totalSelisih >= 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' }} rounded-full flex items-center justify-center mr-4 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 14l6-6m-6 6h6m-6-6v6m-3 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Selisih Pagu Target</p>
                    <h4 class="text-lg font-black {{ $totalSelisih >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">{{
                        $totalSelisih > 0 ? '+' : '' }} Rp {{ number_format($totalSelisih, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="max-h-[600px] overflow-y-auto custom-scrollbar relative">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="py-4 px-4 border-b w-12"></th>
                            <th class="py-4 px-2 text-xs font-bold text-slate-500 uppercase border-b w-24">ID Rincian
                            </th>
                            <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase border-b">Komponen &
                                Spesifikasi</th>
                            <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase border-b text-center w-32">
                                Status</th>
                            <th class="py-4 px-4 text-xs font-bold text-amber-600 uppercase border-b text-right w-36">{{
                                $labelLama ?? 'Data Lama' }}</th>
                            <th class="py-4 px-4 text-xs font-bold text-emerald-600 uppercase border-b text-right w-36">
                                {{ $labelBaru ?? 'Data Baru' }}</th>
                            <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase border-b text-right w-36">
                                Selisih</th>
                        </tr>
                    </thead>

                    @foreach ($koleksiPerbandingan as $item)
                    @if($item['status'] == 'Tetap') @continue @endif

                    <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100 border-b border-slate-100">
                        <tr @click="expanded = !expanded" class="cursor-pointer transition-colors hover:bg-slate-50
                                    @if($item['status'] == 'Baru') bg-emerald-50/40
                                    @elseif($item['status'] == 'Dihapus') bg-rose-50/40
                                    @elseif($item['status'] == 'Berubah Pagu') bg-amber-50/40
                                    @elseif($item['status'] == 'Berubah Rincian') bg-fuchsia-50/40
                                    @elseif($item['status'] == 'Geser Jadwal') bg-sky-50/40
                                    @endif
                                ">
                            <td class="py-4 px-4 text-center text-slate-400">
                                <svg class="w-5 h-5 mx-auto transform transition-transform duration-200"
                                    :class="{'rotate-180': expanded}" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </td>
                            <td class="py-4 px-2 text-xs font-mono text-slate-500">{{ $item['idblrinci'] }}</td>

                            {{-- Kolom Komponen & Spesifikasi (DIUPDATE) --}}
                            <td class="py-4 px-4">
                                <div class="text-sm font-bold text-slate-800 line-clamp-2"
                                    title="{{ $item['namakomponen'] }}">
                                    {{ $item['namakomponen'] }}
                                </div>
                                @if($item['spek'] && $item['spek'] != '-')
                                <div class="text-[11px] text-slate-500 mt-1 leading-tight line-clamp-2"
                                    title="{{ $item['spek'] }}">
                                    <span class="font-semibold text-slate-600">Spek:</span> {{ $item['spek'] }}
                                </div>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <span
                                        class="text-[10px] text-slate-600 font-bold bg-white border border-slate-200 px-2 py-0.5 rounded shadow-sm">
                                        Vol: {{ $item['koefisien'] }}
                                    </span>
                                    <span
                                        class="text-[10px] text-slate-600 font-bold bg-white border border-slate-200 px-2 py-0.5 rounded shadow-sm">
                                        @ {{ $item['hargasatuan'] > 0 ? 'Rp ' . number_format($item['hargasatuan'], 0,
                                        ',', '.') : '-' }}
                                    </span>
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border
                                            @if($item['status'] == 'Baru') bg-emerald-100 border-emerald-200 text-emerald-700
                                            @elseif($item['status'] == 'Dihapus') bg-rose-100 border-rose-200 text-rose-700
                                            @elseif($item['status'] == 'Berubah Pagu') bg-amber-100 border-amber-200 text-amber-700
                                            @elseif($item['status'] == 'Berubah Rincian') bg-fuchsia-100 border-fuchsia-200 text-fuchsia-700
                                            @elseif($item['status'] == 'Geser Jadwal') bg-sky-100 border-sky-200 text-sky-700
                                            @endif
                                        ">{{ $item['status'] }}</span>
                            </td>
                            <td class="py-4 px-4 text-right text-sm text-slate-600">
                                {{ $item['harga_lama'] > 0 ? number_format($item['harga_lama'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-right text-sm text-slate-800 font-bold">
                                {{ $item['harga_baru'] > 0 ? number_format($item['harga_baru'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-right text-sm font-bold
                                        @if($item['selisih'] > 0) text-emerald-600
                                        @elseif($item['selisih'] < 0) text-rose-600
                                        @else text-slate-400
                                        @endif
                                    ">
                                {{ $item['selisih'] > 0 ? '+' : '' }}{{ $item['selisih'] != 0 ?
                                number_format($item['selisih'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>

                        <tr x-show="expanded" style="display: none;" x-transition>
                            <td colspan="7" class="p-0 bg-slate-50/80">
                                <div class="p-5 border-l-4
                                            @if($item['status'] == 'Baru') border-emerald-400
                                            @elseif($item['status'] == 'Dihapus') border-rose-400
                                            @elseif($item['status'] == 'Berubah Pagu') border-amber-400
                                            @elseif($item['status'] == 'Berubah Rincian') border-fuchsia-400
                                            @elseif($item['status'] == 'Geser Jadwal') border-sky-400
                                            @endif
                                        ">
                                    <h5 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">
                                        Rincian Pergeseran Bulanan</h5>
                                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                                        <table class="w-full text-[11px] text-right bg-white">
                                            <thead>
                                                <tr
                                                    class="bg-slate-100 text-slate-600 border-b border-slate-200 uppercase">
                                                    <th class="p-2.5 text-left font-bold border-r w-32">Sumber Data</th>
                                                    @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']
                                                    as $bulan)
                                                    <th class="p-2.5 border-r min-w-[70px]">{{ $bulan }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-b border-slate-100 text-slate-500">
                                                    <td
                                                        class="p-2.5 text-left font-bold bg-slate-50 border-r text-amber-600">
                                                        {{ $labelLama ?? 'Lama' }}</td>
                                                    @for($i=1; $i<=12; $i++) <td class="p-2.5 border-r">{{
                                                        $item['bulan_lama'][$i] > 0 ?
                                                        number_format($item['bulan_lama'][$i], 0, ',', '.') : '-' }}
                            </td>
                            @endfor
                        </tr>
                        <tr class="border-b border-slate-100 font-medium text-slate-800">
                            <td class="p-2.5 text-left font-bold bg-slate-50 border-r text-emerald-600">{{ $labelBaru ??
                                'Baru' }}</td>
                            @for($i=1; $i<=12; $i++) <td class="p-2.5 border-r">{{ $item['bulan_baru'][$i] > 0 ?
                                number_format($item['bulan_baru'][$i], 0, ',', '.') : '-' }}</td>
                                @endfor
                        </tr>
                        <tr class="bg-slate-50/50 font-bold">
                            <td class="p-2.5 text-left bg-slate-100 border-r text-slate-600">Selisih (+/-)</td>
                            @for($i=1; $i<=12; $i++) @php $sBulan=$item['selisih_bulan'][$i]; @endphp <td
                                class="p-2.5 border-r {{ $sBulan > 0 ? 'text-emerald-600' : ($sBulan < 0 ? 'text-rose-600' : 'text-slate-300') }}">
                                {{ $sBulan > 0 ? '+' : '' }}{{ $sBulan != 0 ? number_format($sBulan, 0, ',', '.') : '-'
                                }}
                                </td>
                                @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </td>
        </tr>
        </tbody>
        @endforeach
        </table>
    </div>

    <div class="bg-slate-50 border-t border-slate-200 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-xs text-slate-500">
            * Data dengan status <span class="font-bold text-slate-700 bg-slate-200 px-1 rounded">Tetap</span>
            disembunyikan. Data tidak disimpan ke database lokal.
        </p>
        <a href="{{ route('akb.index') }}"
            class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-bold text-sm hover:bg-slate-100 shadow-sm transition text-center">
            Selesai Mengecek
        </a>
    </div>
    </div>
    @endif

    {{-- MODAL UPLOAD --}}
    <div x-show="isModalOpen" style="display: none" class="relative z-50" aria-modal="true">
        <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="isModalOpen = false"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">

                    <div class="bg-white px-6 pb-6 pt-5 sm:p-8">
                        <div class="sm:flex sm:items-start mb-6">
                            <div
                                class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-indigo-50 sm:mx-0 sm:h-12 sm:w-12">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-bold leading-6 text-slate-900">Analisis Perubahan ARKAS</h3>
                                <p class="text-sm text-slate-500 mt-1">Sistem akan memetakan komponen mana saja yang
                                    perlu disesuaikan.</p>
                            </div>
                        </div>

                        <form action="{{ route('akb.perbandingan.proses') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-5 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <p class="text-sm font-bold text-slate-700 mb-3">Tentukan sumber data JSON yang Anda
                                    upload:</p>

                                <div class="space-y-3">
                                    <label class="flex items-start cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input type="radio" name="jenis_json" value="baru" checked
                                                class="w-4 h-4 text-indigo-600 bg-white border-slate-300 focus:ring-indigo-600 focus:ring-2">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="font-bold text-slate-800">Sebagai AKB Baru (Target)</span>
                                            <p class="text-slate-500 text-xs mt-0.5">DB Lokal dianggap data Lama. Anda
                                                ingin melihat apa saja yang berubah di JSON terbaru ini.</p>
                                        </div>
                                    </label>

                                    <label class="flex items-start cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input type="radio" name="jenis_json" value="lama"
                                                class="w-4 h-4 text-indigo-600 bg-white border-slate-300 focus:ring-indigo-600 focus:ring-2">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="font-bold text-slate-800">Sebagai AKB Lama (Baseline)</span>
                                            <p class="text-slate-500 text-xs mt-0.5">DB Lokal dianggap data Baru. Anda
                                                ingin memastikan DB lokal Anda sudah meng-cover JSON lama ini.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="dropzone-file"
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-slate-50 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-7 h-7 mb-2 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                        <p class="mb-1 text-sm text-slate-600"><span
                                                class="font-bold text-indigo-600">Klik untuk upload file</span> (.json)
                                        </p>
                                    </div>
                                    <input id="dropzone-file" type="file" name="json_files[]" multiple accept=".json"
                                        class="hidden" required />
                                </label>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="isModalOpen = false"
                                    class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800">Tutup</button>
                                <button type="submit"
                                    class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition">Bandingkan
                                    Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</x-app-layout>
