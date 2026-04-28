<x-manual-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic">
                    Inspector <span class="text-indigo-600">JSON</span> RKAS Multi-Unit
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Analisis mendalam, pengelompokan otomatis, dan
                    konsolidasi anggaran antar unit sekolah.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('kegiatan.index') }}"
                    class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Daftar Kegiatan
                </a>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8 transition-all hover:border-indigo-300">
            <form action="{{ route('kegiatan.cek_json') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col md:flex-row items-end gap-5">
                @csrf
                <div class="flex-1 w-full">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Pilih Satu
                        atau Beberapa File JSON RKAS</label>
                    <input type="file" name="file_json[]" accept=".json" multiple required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border-2 border-slate-100 rounded-2xl">
                </div>
                <button type="submit"
                    class="w-full md:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm uppercase tracking-wider rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Bedah & Konsolidasi
                </button>
            </form>
        </div>

        @if(isset($groupedData))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Unit Terlibat</p>
                <p class="text-sm font-black text-slate-800">{{ $metaData['kode_sekolah'] }}</p>
            </div>
            <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori Dana</p>
                <p class="text-xl font-black text-emerald-600 uppercase">{{ $metaData['kategori'] }}</p>
            </div>
            <div
                class="bg-slate-900 p-5 rounded-2xl shadow-xl lg:col-span-2 flex justify-between items-center relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 text-indigo-300">
                        Total Konsolidasi Anggaran</p>
                    <p class="text-3xl font-black text-white font-mono">Rp {{ number_format($totalKeseluruhan, 0, ',',
                        '.') }}</p>
                </div>
                <svg class="w-16 h-16 text-white/5 absolute -right-2 -bottom-2" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto max-h-[800px] sticky-table">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 sticky top-0 shadow-sm z-40">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider w-1/3">
                                Detail Komponen
                            </th>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider w-1/4">
                                Spesifikasi
                            </th>
                            <th
                                class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                Koefisien
                            </th>
                            <th
                                class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                Harga Satuan
                            </th>
                            <th
                                class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                Total + Pajak
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($groupedData as $namaKegiatan => $grupKeterangan)
                        <tr class="bg-indigo-50/50">
                            <td colspan="5" class="px-6 py-4 border-b-2 border-indigo-100">
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center mr-3 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-black text-indigo-900 uppercase tracking-widest">{{
                                        $namaKegiatan }}</span>
                                </div>
                            </td>
                        </tr>

                        @foreach($grupKeterangan as $keterangan => $dataKeterangan)
                        <tr class="bg-white border-b border-slate-100 sticky top-[52px] z-30 shadow-sm">
                            <td colspan="4" class="px-6 py-3 pl-14">
                                <div class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-2 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                        </path>
                                    </svg>
                                    <span class="text-[11px] font-bold uppercase text-slate-500 mr-2">Keterangan:</span>
                                    <span class="text-xs font-black text-slate-900 italic">{{ $keterangan }}</span>

                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span
                                    class="text-[10px] font-black text-emerald-500 uppercase block leading-none mb-1">Sub-Total</span>
                                <span class="text-sm font-black font-mono text-emerald-700">Rp {{
                                    number_format($dataKeterangan['total_anggaran_keterangan'], 0, ',', '.') }}</span>
                            </td>
                        </tr>

                        @foreach($dataKeterangan['items'] as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 pl-20">
                                {{ $item['namakomponen'] }}
                            </td>

                            <td class="px-6 py-4 text-xs text-slate-500 italic max-w-xs break-words">
                                {{ $item['spek'] ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-xs font-black text-slate-400 italic">{{ $item['koefisien'] }}</td>
                            <td class="px-6 py-4 text-right text-xs font-mono font-bold text-slate-500">
                                Rp {{ number_format($item['hargasatuan'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-mono font-black text-slate-900">
                                Rp {{ number_format((float)$item['totalharga'] + (float)$item['totalpajak'], 0, ',',
                                '.') }}
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <div class="mt-8 pt-8 border-t border-slate-200 text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">
                    &copy; 2026 SI-KEUANGAN &bull; Portal Terintegrasi SDN Tomang 03 Pagi
                </p>
            </div>
        </div>
</x-manual-layout>