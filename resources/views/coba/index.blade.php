<x-app-layout>
    <div class="min-h-screen bg-[#F4F7FA] pb-20 font-sans antialiased">
        
        <div class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-8 py-5 flex flex-col lg:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="h-12 w-1.5 bg-indigo-600 rounded-full"></div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Monitoring Anggaran</h1>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded uppercase border border-indigo-100">{{ $anggaranAktif }}</span>
                            <span class="text-slate-300 text-sm">/</span>
                            <span class="text-slate-500 text-xs font-medium">Laporan Realisasi Triwulan {{ $tw }}</span>
                        </div>
                    </div>
                </div>

                <div class="inline-flex bg-slate-100 p-2 rounded-2xl border border-slate-200 gap-2">
    @foreach([1, 2, 3, 4] as $t)
        <a href="{{ request()->fullUrlWithQuery(['tw' => $t]) }}" 
           class="relative px-10 py-3 rounded-xl text-xs font-black tracking-widest transition-all duration-300 group
           {{ $tw == $t ? 'bg-white text-blue-600 shadow-sm shadow-slate-200' : 'text-slate-500 hover:text-slate-900' }}">
            
            TW {{ $t }}

            @if($tw == $t)
                <span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-8 h-1 bg-blue-600 rounded-full transition-all duration-500"></span>
            @else
                <span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-0 h-1 bg-slate-300 rounded-full transition-all duration-300 group-hover:w-4"></span>
            @endif
        </a>
    @endforeach
</div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-8 mt-10">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="group">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 group-hover:text-indigo-500 transition-colors">Alokasi Kas (AKB)</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 font-mono">Rp{{ number_format($results->sum('total_akb'), 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-4 h-1 w-12 bg-indigo-600 rounded-full"></div>
                </div>

                <div class="group">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 group-hover:text-emerald-500 transition-colors">Realisasi Belanja</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-emerald-600 font-mono">Rp{{ number_format($results->sum('total_realisasi'), 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-4 h-1 w-12 bg-emerald-500 rounded-full"></div>
                </div>

                <div class="group">
                    @php $sisaTotal = $results->sum('total_akb') - $results->sum('total_realisasi'); @endphp
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 group-hover:text-rose-500 transition-colors">Sisa Saldo Kas</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black {{ $sisaTotal < 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono">Rp{{ number_format($sisaTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-4 h-1 w-12 {{ $sisaTotal < 0 ? 'bg-rose-600' : 'bg-slate-300' }} rounded-full"></div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-10 py-6 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Informasi Kegiatan</th>
                            <th class="px-10 py-6 text-right text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Anggaran Kas</th>
                            <th class="px-10 py-6 text-right text-[11px] font-black text-emerald-600 uppercase tracking-widest border-b border-slate-100">Realisasi</th>
                            <th class="px-10 py-6 text-right text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 w-48">Keterserapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($results as $row)
                            @php 
                                $percent = $row->total_akb > 0 ? ($row->total_realisasi / $row->total_akb) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-10 py-8">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[10px] font-mono font-bold text-indigo-500 tracking-tighter">#{{ $row->idbl }}</span>
                                            <h4 class="text-sm font-bold text-slate-800 tracking-tight">{{ $row->kodeakun }}</h4>
                                        </div>
                                        <p class="text-xs text-slate-400 font-medium leading-relaxed max-w-md group-hover:text-slate-600 transition-colors">{{ $row->namaakun }}</p>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <span class="text-sm font-bold text-slate-600 font-mono tracking-tighter">{{ number_format($row->total_akb, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <span class="text-sm font-black text-emerald-600 font-mono tracking-tighter">{{ number_format($row->total_realisasi, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="flex flex-col items-end gap-2">
    <div class="w-full h-1.5 bg-blue-50 rounded-full overflow-hidden border border-blue-100/50">
        <div class="h-full {{ $percent > 100 ? 'bg-rose-500' : 'bg-blue-600' }} transition-all duration-1000" 
             style="width: {{ min($percent, 100) }}%"></div>
    </div>
    
    <span class="text-[10px] font-black {{ $percent > 100 ? 'text-rose-500' : 'text-blue-600' }}">
        {{ number_format($percent, 1) }}%
    </span>
</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-10 py-32 text-center text-slate-300 font-medium italic">Data tidak ditemukan untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>