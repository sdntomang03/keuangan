<x-app-layout>
    <div class="py-12 bg-gray-50" x-data="{ openModal: false, selectedPajak: {}, actionUrl: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Pajak Siap Setor</h2>
                    <p class="text-sm text-gray-500">Daftar pajak yang telah dipungut dan harus disetorkan ke kas negara.</p>
                </div>
                <a href="{{ route('bku.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke BKU
                </a>
            </div>

            <div class="bg-white border border-gray-200 shadow-sm rounded-3xl overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-widest font-black text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Tgl. Pungut</th>
                            <th class="px-6 py-4">Jenis Pajak</th>
                            <th class="px-6 py-4">Referensi Belanja</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pajaks as $pajak)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ \Carbon\Carbon::parse($pajak->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $pajak->masterPajak->nama_pajak ?? 'Pajak' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $pajak->belanja->uraian ?? '-' }} 
                                <span class="block text-[10px] text-blue-500 font-bold uppercase">No: {{ $pajak->belanja->no_bukti ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-orange-600">
                                Rp {{ number_format($pajak->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button 
                                    @click="openModal = true; 
                                            selectedPajak = { name: '{{ $pajak->masterPajak->nama_pajak }}', amount: '{{ number_format($pajak->nominal, 0, ',', '.') }}' };
                                            actionUrl = '{{ route('pajak.proses-setor', $pajak->id) }}'"
                                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-black uppercase rounded-xl transition shadow-md shadow-orange-100">
                                    Setor Pajak
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Tidak ada antrean pajak yang perlu disetor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="openModal = false"></div>

                <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full p-8">
                    <div class="mb-6">
                        <h3 class="text-xl font-black text-gray-800 uppercase italic">Konfirmasi Penyetoran</h3>
                        <p class="text-sm text-gray-500">Anda akan menyetorkan <span class="font-bold text-orange-600" x-text="selectedPajak.name"></span> sebesar <span class="font-bold text-orange-600" x-text="'Rp ' + selectedPajak.amount"></span> ke Kas Negara.</p>
                    </div>

                    <form :action="actionUrl" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Tanggal Setor (Sesuai Bukti)</label>
                                <input type="date" name="tanggal_setor" required class="w-full border-gray-200 rounded-xl focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Nomor NTPN</label>
                                <input type="text" name="ntpn" placeholder="Input 16 Digit NTPN" required class="w-full border-gray-200 rounded-xl focus:ring-orange-500 font-mono">
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="openModal = false" class="flex-1 px-4 py-3 text-gray-400 text-xs font-black uppercase">Batal</button>
                            <button type="submit" class="flex-1 px-4 py-3 bg-orange-500 text-white text-xs font-black uppercase rounded-xl shadow-lg shadow-orange-200">Kirim ke BKU</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>