<x-manual-layout>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">Rekonsiliasi Data Dinas</h2>
                <p class="text-sm text-slate-500">Mencocokkan rincian kegiatan dengan file JSON resmi.</p>
            </div>
            <a href="{{ route('kegiatan.tambah_komponen', $kegiatan->id) }}"
                class="text-sm font-bold text-indigo-600 hover:underline">&larr; Kembali ke Rincian</a>
        </div>

        <div class="bg-white p-6 rounded-xl border-2 border-dashed border-slate-300 mb-8 text-center">
            <form action="{{ route('kegiatan.rekonsiliasi', $kegiatan->id) }}" method="POST"
                enctype="multipart/form-data" class="max-w-md mx-auto">
                @csrf
                <label class="block text-sm font-bold text-slate-700 mb-4">Pilih File JSON dari Dinas</label>
                <input type="file" name="file_json" accept=".json" required
                    class="block w-full text-sm text-slate-500 mb-4 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700">
                <button type="submit"
                    class="w-full py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors">Mulai
                    Cocokkan Data</button>
            </form>
        </div>

        @if($hasil)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-800 text-white flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-widest">Hasil Komparasi</span>
                <span class="text-xs bg-slate-700 px-2 py-1 rounded">ID Giat: {{ $kegiatan->id_kegiatan }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Komponen Dinas
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Pagu Dinas</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Input Lokal</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($hasil as $h)
                        <tr
                            class="{{ $h['status'] == 'Belum Ada' ? 'bg-rose-50/50' : '' }} hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800">{{ $h['nama'] }}</div>
                                <div class="text-[10px] text-slate-500 italic mt-0.5">{{ $h['spek'] }}</div>
                                <div class="text-[10px] font-bold text-indigo-600 mt-1 uppercase">Koefisien: {{
                                    $h['vol_dinas'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-sm font-bold text-slate-700">
                                Rp {{ number_format($h['total_dinas'], 0, ',', '.') }}
                            </td>
                            <td
                                class="px-6 py-4 text-right font-mono text-sm font-bold {{ $h['total_lokal'] > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                                Rp {{ number_format($h['total_lokal'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($h['status'] == 'Sesuai')
                                <span
                                    class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase rounded-full border border-emerald-200">OK</span>
                                @elseif($h['status'] == 'Belum Ada')
                                <span
                                    class="px-2.5 py-1 bg-rose-100 text-rose-700 text-[10px] font-black uppercase rounded-full border border-rose-200 animate-pulse">Missing</span>
                                @else
                                <span
                                    class="px-2.5 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-full border border-amber-200">Selisih</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-manual-layout>