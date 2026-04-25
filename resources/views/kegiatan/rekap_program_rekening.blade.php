<x-manual-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <nav class="flex mb-4 text-xs font-semibold uppercase tracking-widest text-slate-400 print:hidden">
            <a href="{{ route('kegiatan.index') }}" class="hover:text-indigo-600 transition-colors">Perencanaan</a>
            <span class="mx-2">/</span>
            <span class="text-slate-600 dark:text-slate-300">Rekap Anggaran</span>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 print:hidden gap-4">
            <div>
                <h1 class="text-2xl font-bold uppercase tracking-tight text-slate-900 dark:text-white">
                    Laporan Rekapitulasi Anggaran
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Berdasarkan Program dan Kode Rekening Belanja
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('kegiatan.index') }}"
                    class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-bold shadow-sm transition-colors">
                    &larr; Kembali
                </a>
                <button onclick="window.print()" {{ !$sumberDana ? 'disabled' : '' }}
                    class="px-4 py-2 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center {{ !$sumberDana ? 'bg-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6 print:hidden">
            <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/2 md:w-1/3">
                    <label for="sumber_dana_id" class="block text-xs font-bold text-slate-500 uppercase mb-2">Pilih
                        Sumber Dana</label>
                    <select name="sumber_dana_id" id="sumber_dana_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50 cursor-pointer">
                        <option value="">-- Pilih Sumber Dana untuk Dilihat --</option>
                        @foreach($listSumberDana as $sd)
                        <option value="{{ $sd->id }}" {{ $sumberDanaId==$sd->id ? 'selected' : '' }}>
                            {{ $sd->nama }} (Tahun {{ $sd->tahun }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg transition-colors hidden sm:block">
                    Tampilkan
                </button>
            </form>
        </div>

        <div
            class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none print:m-0 print:bg-transparent">

            <div
                class="p-6 bg-slate-50 border-b border-slate-200 text-center print:bg-transparent print:border-b-2 print:border-black print:p-0 print:pb-4 print:mb-6">
                <h2 class="text-lg md:text-xl font-bold text-slate-800 uppercase tracking-wide print:text-black">
                    Rekapitulasi Anggaran Belanja
                </h2>

                @if($sumberDana)
                <div class="text-sm font-bold text-indigo-600 uppercase mt-1 print:text-black">
                    SUMBER DANA: {{ $sumberDana->nama }}
                </div>
                <div class="text-sm text-slate-500 font-medium mt-1 print:text-black">
                    Tahun Anggaran {{ $sumberDana->tahun }}
                </div>
                @else
                <div class="text-sm text-rose-500 font-medium mt-2 italic print:hidden">
                    Silakan pilih sumber dana pada filter di atas terlebih dahulu.
                </div>
                @endif
            </div>

            <div class="p-0 print:p-0">
                @if(!$sumberDana)
                <div class="py-16 text-center print:hidden">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <p class="text-slate-500 font-medium text-lg">Menunggu Pilihan</p>
                    <p class="text-slate-400 text-sm mt-1">Pilih sumber dana untuk menampilkan rekapitulasi program.</p>
                </div>
                @else

                <div class="mb-2 print:mb-4 px-6 print:px-0">
                    <h3 class="text-sm font-bold text-slate-700 uppercase mb-3 print:text-black">A. Rekapitulasi per
                        Program</h3>
                </div>
                <div class="overflow-x-auto border-t border-b border-slate-200 print:border-t-0">
                    <table
                        class="min-w-full divide-y divide-slate-200 print:divide-black/20 print:border-collapse print:border print:border-black">
                        <thead class="bg-slate-100 print:bg-slate-50">
                            <tr>
                                <th
                                    class="px-5 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-2/3 print:text-black print:border print:border-black">
                                    Uraian Program
                                </th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-1/3 print:text-black print:border print:border-black">
                                    Jumlah Anggaran (Rp)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 print:divide-black/20">
                            @foreach($rekap as $namaProgram => $dataProgram)
                            <tr class="bg-indigo-50/60 print:bg-gray-100">
                                <td
                                    class="px-5 py-2.5 font-bold text-indigo-900 uppercase text-sm print:text-black print:border print:border-black">
                                    {{ $namaProgram }}
                                </td>
                                <td
                                    class="px-5 py-2.5 font-bold text-indigo-900 text-right text-sm print:text-black print:border print:border-black">
                                    {{ number_format($dataProgram['total_program'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-800 print:bg-white">
                            <tr>
                                <td
                                    class="px-5 py-3 font-bold text-white text-right uppercase tracking-wider print:text-black print:border print:border-black">
                                    Total Program
                                </td>
                                <td
                                    class="px-5 py-3 font-black text-emerald-400 text-right text-[15px] print:text-black print:border print:border-black">
                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-10 mb-2 print:mb-4 px-6 print:px-0 print:break-inside-avoid">
                    <h3 class="text-sm font-bold text-slate-700 uppercase mb-3 print:text-black">B. Rekapitulasi per
                        Jenis Belanja (Kode Rekening)</h3>
                </div>
                <div
                    class="overflow-x-auto border-t border-b border-slate-200 print:border-t-0 print:break-inside-avoid">
                    <table
                        class="min-w-full divide-y divide-slate-200 print:divide-black/20 print:border-collapse print:border print:border-black">
                        <thead class="bg-amber-50 print:bg-slate-50">
                            <tr>
                                <th
                                    class="px-5 py-3 text-left text-xs font-bold text-amber-800 uppercase tracking-wider w-2/3 print:text-black print:border print:border-black">
                                    Kode Rekening & Jenis Belanja
                                </th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-bold text-amber-800 uppercase tracking-wider w-1/3 print:text-black print:border print:border-black">
                                    Jumlah Anggaran (Rp)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 print:divide-black/20">
                            @foreach($rekapRekening as $kodeRekening => $totalRekening)
                            <tr class="bg-white">
                                <td
                                    class="px-5 py-2.5 text-sm font-bold text-slate-700 print:text-black print:border print:border-black">
                                    {{ $kodeRekening }}
                                </td>
                                <td
                                    class="px-5 py-2.5 text-sm font-medium text-slate-600 text-right print:text-black print:border print:border-black">
                                    {{ number_format($totalRekening, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-800 print:bg-white">
                            <tr>
                                <td
                                    class="px-5 py-3 font-bold text-white text-right uppercase tracking-wider print:text-black print:border print:border-black">
                                    Total Belanja
                                </td>
                                <td
                                    class="px-5 py-3 font-black text-emerald-400 text-right text-[15px] print:text-black print:border print:border-black">
                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="hidden print:block mt-12 px-8 pb-8 print:break-inside-avoid">
                    <div class="flex justify-end">
                        <div class="text-center">
                            <p class="text-sm text-black mb-16">Jakarta, {{ date('d F Y') }}<br>Kepala SDN Tomang 03
                                Pagi,</p>
                            <p class="text-sm text-black font-bold underline">.........................................
                            </p>
                            <p class="text-sm text-black">NIP. </p>
                        </div>
                    </div>
                </div>

                @endif
            </div>

        </div>
    </div>
</x-manual-layout>