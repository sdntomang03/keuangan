<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Realisasi Belanja') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <form action="{{ route('belanja.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Cari Transaksi</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari uraian atau nomor bukti..." class="mt-1 block w-full rounded-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700 text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</label>
                        <div class="flex gap-2 mt-1">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold flex-1">Filter</button>
                            <a href="{{ route('belanja.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm font-bold">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            @forelse($dataBelanja as $belanja)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/50">
                    <div class="flex flex-col md:flex-row justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded uppercase">{{ $belanja->no_bukti }}</span>
                                <span class="text-xs text-gray-400 font-medium">{{ \Carbon\Carbon::parse($belanja->tanggal)->format('d M Y') }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $belanja->uraian }}</h3>
                            <p class="text-sm text-gray-500 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2" stroke-linecap="round"/></svg>
                                Rekanan: <span class="ml-1 font-semibold text-gray-700 dark:text-gray-300">{{ $belanja->rekanan->nama_rekanan ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Total Transfer (Netto)</p>
                            <p class="text-2xl font-black text-emerald-600">Rp {{ number_format($belanja->transfer, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 text-[10px] uppercase font-bold tracking-tighter">
                            <tr>
                                <th class="px-6 py-3">Komponen Barang/Jasa</th>
                                <th class="px-6 py-3 text-center">Volume</th>
                                <th class="px-6 py-3 text-right">Harga Satuan</th>
                                <th class="px-6 py-3 text-right">Total Bruto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($belanja->rincian as $rinci)
                            <tr class="dark:text-gray-300">
                                <td class="px-6 py-4 font-medium">{{ $rinci->namakomponen }}</td>
                                <td class="px-6 py-3 text-center text-xs">{{ $rinci->volume }} {{ $rinci->satuan ?? 'Unit' }}</td>
                                <td class="px-6 py-3 text-right">Rp {{ number_format($rinci->harga_satuan, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right font-semibold">Rp {{ number_format($rinci->total_bruto, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50/50 dark:bg-gray-900/20 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex flex-wrap gap-4 items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Potongan Pajak:</span>
                        @forelse($belanja->pajaks as $pajak)
                            <div class="flex items-center bg-white dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-600">
                                <span class="text-[10px] font-bold text-orange-600 mr-2">{{ $pajak->jenis_pajak }}</span>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">Rp {{ number_format($pajak->nominal, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <span class="text-xs text-gray-400 italic">Tidak ada potongan pajak</span>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <p class="text-gray-400">Belum ada data belanja ditemukan.</p>
            </div>
            @endforelse

            <div class="mt-4">
                {{ $dataBelanja->links() }}
            </div>
        </div>
    </div>
</x-app-layout>