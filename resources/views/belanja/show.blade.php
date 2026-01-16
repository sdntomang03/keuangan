<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Detail BKU Belanja</h2>
                    <p class="text-sm text-gray-500">Nomor Bukti: {{ $belanja->no_bukti }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('belanja.index') }}" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-xl font-bold hover:bg-gray-50 transition shadow-sm">
                        KEMBALI
                    </a>
                    <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">
                        CETAK
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Informasi Kegiatan</label>
                                <p class="text-sm font-bold text-blue-600 mb-1">{{ $belanja->kodeakun }}</p>
                                <p class="text-lg font-black text-gray-800 leading-tight">{{ $kegiatan->namagiat ?? 'Kegiatan Tidak Ditemukan' }}</p>
                                <p class="text-xs text-gray-500 mt-2 italic">IDBL: {{ $belanja->idbl }}</p>
                            </div>
                            <div class="md:border-l md:pl-8">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Rekanan / Toko</label>
                                <p class="text-lg font-black text-gray-800">{{ $belanja->rekanan->nama_rekanan ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ $belanja->rekanan->alamat ?? '-' }}</p>
                                <div class="mt-4 p-3 bg-gray-50 rounded-2xl">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Uraian Transaksi</p>
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $belanja->uraian }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50">
                            <h3 class="font-black text-gray-800 uppercase tracking-tighter">Rincian Komponen Belanja</h3>
                        </div>
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Komponen / Spesifikasi</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Volume</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga Satuan</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Bruto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($belanja->rincis as $rinci)
                                <tr>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-bold text-gray-800">{{ $rinci->namakomponen }}</p>
                                        <p class="text-[11px] text-blue-500 italic">{{ $rinci->spek }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-black text-gray-600">
                                        {{ $rinci->volume }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm font-bold text-gray-700">
                                        Rp {{ number_format($rinci->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-5 text-right text-sm font-black text-gray-900">
                                        Rp {{ number_format($rinci->total_bruto, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-900 p-8 rounded-[2rem] shadow-2xl text-white">
                        <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-6">Ringkasan Pembayaran</h3>
                        
                        <div class="space-y-4 border-b border-gray-800 pb-6 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-400">Subtotal Belanja</span>
                                <span class="font-bold">Rp {{ number_format($belanja->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-blue-400 font-bold">PPN</span>
                                <span class="font-bold text-blue-400">+ Rp {{ number_format($belanja->ppn, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-blue-600/20 p-5 rounded-2xl border border-blue-500/30 mb-6">
                            <span class="text-[10px] text-blue-300 font-black uppercase tracking-widest block mb-1">Nilai Bruto (Kwitansi)</span>
                            <span class="text-3xl font-black text-white">Rp {{ number_format($belanja->subtotal + $belanja->ppn, 0, ',', '.') }}</span>
                        </div>

                        <div class="space-y-3 mb-8">
                            <div class="flex justify-between items-center text-orange-400">
                                <span class="text-[10px] font-black uppercase tracking-widest">Total PPh</span>
                                <span class="font-bold">- Rp {{ number_format($belanja->pph, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-800">
                            <span class="text-[10px] text-emerald-400 font-black uppercase tracking-widest block mb-2">Netto Diterima Rekanan</span>
                            <span class="text-4xl font-black text-emerald-400 italic">Rp {{ number_format($belanja->transfer, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($belanja->pajaks->count() > 0)
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Rincian Potongan Pajak</h3>
                        <div class="space-y-3">
                            @foreach($belanja->pajaks as $pajak)
                            <div class="flex justify-between items-center p-3 bg-orange-50 rounded-xl border border-orange-100">
                                <span class="text-xs font-bold text-orange-800">{{ $pajak->masterPajak->nama_pajak ?? 'Pajak' }}</span>
                                <span class="text-sm font-black text-orange-700">Rp {{ number_format($pajak->nominal, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="p-6 bg-blue-50 rounded-3xl border border-blue-100">
                        <div class="flex items-center gap-4 text-blue-800">
                            <div class="p-2 bg-blue-200 rounded-lg text-xs font-black">
                                INFO
                            </div>
                            <div class="text-[11px] leading-tight">
                                <p>Diposting oleh: <strong>{{ $belanja->user->name ?? 'System' }}</strong></p>
                                <p>Pada: {{ $belanja->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>