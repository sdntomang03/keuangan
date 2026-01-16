<x-app-layout>
    <div class="min-h-screen bg-slate-50 pb-20 font-sans antialiased">
        
        <div class="bg-white border-b border-slate-200 sticky top-0 z-40 px-8 py-6 shadow-sm">
            <div class="max-w-[1600px] mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-600 rounded-2xl text-white shadow-lg shadow-blue-200">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rekapitulasi Tahunan</h1>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Anggaran Aktif: <span class="text-blue-600">{{ $anggaranAktif }}</span></p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"/></svg>
                        Print Laporan
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-[1600px] mx-auto px-8 mt-10">
            @php 
                $totalPagu = $results->sum('pagu_tahunan');
                $sumTw1 = $results->sum('realisasi_tw1');
                $sumTw2 = $results->sum('realisasi_tw2');
                $sumTw3 = $results->sum('realisasi_tw3');
                $sumTw4 = $results->sum('realisasi_tw4');
                $totalRealisasi = $sumTw1 + $sumTw2 + $sumTw3 + $sumTw4;
                $persenTotal = $totalPagu > 0 ? ($totalRealisasi / $totalPagu) * 100 : 0;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pagu RKAS</span>
                    <h2 class="text-2xl font-black text-slate-900 font-mono mt-1">Rp{{ number_format($totalPagu, 0, ',', '.') }}</h2>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Realisasi</span>
                    <h2 class="text-2xl font-black text-blue-600 font-mono mt-1">Rp{{ number_format($totalRealisasi, 0, ',', '.') }}</h2>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Sisa Saldo</span>
                    <h2 class="text-2xl font-black text-rose-600 font-mono mt-1">Rp{{ number_format($totalPagu - $totalRealisasi, 0, ',', '.') }}</h2>
                </div>
                <div class="bg-blue-600 p-6 rounded-[2rem] shadow-xl shadow-blue-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black text-blue-100 uppercase tracking-widest">Efisiensi</span>
                        <h2 class="text-3xl font-black text-white font-mono mt-1">{{ number_format($persenTotal, 1) }}%</h2>
                    </div>
                    <div class="w-14 h-14 rounded-full border-4 border-white/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1400px]">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-white border-b border-slate-100 z-20">Kegiatan / Akun</th>
                                <th class="px-6 py-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Total Pagu</th>
                                <th class="px-6 py-6 text-right text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-slate-100">TW 1</th>
                                <th class="px-6 py-6 text-right text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-slate-100 bg-blue-50/20">TW 2</th>
                                <th class="px-6 py-6 text-right text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-slate-100">TW 3</th>
                                <th class="px-6 py-6 text-right text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-slate-100 bg-blue-50/20">TW 4</th>
                                <th class="px-8 py-6 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest border-b border-slate-100">Total Serap</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-slate-900 uppercase tracking-widest border-b border-slate-100">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono text-xs">
                            @foreach($results as $row)
                            @php 
                                $rTotal = ($row->realisasi_tw1 ?? 0) + ($row->realisasi_tw2 ?? 0) + ($row->realisasi_tw3 ?? 0) + ($row->realisasi_tw4 ?? 0);
                                $rPercent = $row->pagu_tahunan > 0 ? ($rTotal / $row->pagu_tahunan) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-6 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-50">
                                    <div class="flex flex-col font-sans">
                                        <span class="text-[11px] font-black text-slate-900 tracking-tight">{{ $row->kodeakun }}</span>
                                        <span class="text-[10px] text-slate-400 truncate w-60 mt-0.5">{{ $row->namaakun }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-right text-slate-500 font-bold border-r border-slate-50/50">
                                    {{ number_format($row->pagu_tahunan, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-6 text-right text-blue-600 font-bold">{{ number_format($row->realisasi_tw1 ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-6 text-right text-blue-600 font-bold bg-blue-50/10">{{ number_format($row->realisasi_tw2 ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-6 text-right text-blue-600 font-bold">{{ number_format($row->realisasi_tw3 ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-6 text-right text-blue-600 font-bold bg-blue-50/10">{{ number_format($row->realisasi_tw4 ?? 0, 0, ',', '.') }}</td>
                                <td class="px-8 py-6 text-right border-l border-slate-50">
                                    <span class="text-sm font-black text-slate-900">{{ number_format($rTotal, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-[10px] font-black {{ $rPercent > 100 ? 'text-rose-600' : 'text-blue-600' }}">{{ round($rPercent) }}%</span>
                                        <div class="w-10 h-1 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full {{ $rPercent > 100 ? 'bg-rose-500' : 'bg-blue-600' }}" style="width: {{ min($rPercent, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-900 text-white font-mono">
                            <tr class="divide-x divide-slate-800">
                                <td class="px-8 py-8 font-sans font-black text-xs uppercase tracking-[0.2em] sticky left-0 bg-slate-900 z-10 border-r border-slate-800">Total Akumulasi</td>
                                <td class="px-6 py-8 text-right font-black text-white text-base bg-slate-800/50">{{ number_format($totalPagu, 0, ',', '.') }}</td>
                                <td class="px-6 py-8 text-right font-black text-blue-300">{{ number_format($sumTw1, 0, ',', '.') }}</td>
                                <td class="px-6 py-8 text-right font-black text-blue-300">{{ number_format($sumTw2, 0, ',', '.') }}</td>
                                <td class="px-6 py-8 text-right font-black text-blue-300">{{ number_format($sumTw3, 0, ',', '.') }}</td>
                                <td class="px-6 py-8 text-right font-black text-blue-300">{{ number_format($sumTw4, 0, ',', '.') }}</td>
                                <td class="px-8 py-8 text-right font-black text-emerald-400 text-lg">{{ number_format($totalRealisasi, 0, ',', '.') }}</td>
                                <td class="px-8 py-8 text-center bg-slate-800/50 text-blue-400 font-black text-sm">{{ round($persenTotal) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>