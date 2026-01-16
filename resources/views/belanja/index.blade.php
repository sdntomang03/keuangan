<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Halaman Belanja') }}
            </h2>
            <a href="{{ route('belanja.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Transaksi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-800 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-300 uppercase tracking-widest">Info Transaksi</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-300 uppercase tracking-widest">Sub Total</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-bold text-blue-400 uppercase tracking-widest">PPN</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-bold text-red-400 uppercase tracking-widest">PPh</th>
                                    <th class="px-4 py-3 text-right text-[10px] font-bold text-green-400 uppercase tracking-widest">Total Transfer</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-300 uppercase tracking-widest">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($belanjas as $belanja)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $belanja->no_bukti }}</div>
                                        <div class="text-[11px] text-gray-500 uppercase">{{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F Y') }}</div>
                                        <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium truncate w-48">{{ $belanja->rekanan->nama_rekanan ?? '-' }}</div>
                                        <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium truncate w-48">{{ $belanja->uraian ?? '-' }}</div>
                                    </td>

                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        {{ number_format($belanja->subtotal, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-4 text-right text-sm text-blue-600 dark:text-blue-400 font-bold">
                                        {{ $belanja->ppn > 0 ? number_format($belanja->ppn, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-right text-sm text-red-600 dark:text-red-400 font-bold">
                                        {{ $belanja->pph > 0 ? number_format($belanja->pph, 0, ',', '.') : '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">
                                            Rp {{ number_format($belanja->transfer, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <div class="flex justify-center items-center space-x-3">
                                            <a href="{{ route('belanja.show', $belanja->id) }}" 
   class="text-gray-400 hover:text-blue-600 transition-colors" 
   title="Lihat Detail">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>
</a>
                                            <a href="#" target="_blank" class="text-gray-400 hover:text-green-600 transition-colors" title="Cetak Kuitansi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('belanja.destroy', $belanja->id) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span class="text-gray-500 text-sm">Belum ada data transaksi yang dicatat.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

             <div class="mt-4 px-6 py-4 border-t border-gray-100 bg-gray-50">
        {{ $belanjas->links() }}
    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>