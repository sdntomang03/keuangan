<x-manual-layout>
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white uppercase tracking-tight">Master Sumber Dana
            </h2>
            <p class="text-sm text-slate-500 font-medium mt-1">SI-KEUANGAN - Kelola Kategori Anggaran</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase mb-4">Tambah Sumber Dana
                    </h3>

                    <form action="{{ route('sumber_dana.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Kode</label>
                            <input type="text" name="kode" placeholder="Contoh: BOS-REG" required
                                class="block w-full rounded-lg border-slate-300 dark:bg-slate-900 text-sm focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Nama Sumber
                                Dana</label>
                            <input type="text" name="nama" placeholder="Contoh: BOS Reguler" required
                                class="block w-full rounded-lg border-slate-300 dark:bg-slate-900 text-sm focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tahun
                                Anggaran</label>
                            <input type="number" name="tahun" value="{{ date('Y') }}" min="2020" max="2099" required
                                class="block w-full rounded-lg border-slate-300 dark:bg-slate-900 text-sm focus:ring-indigo-500">
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-colors">
                            Simpan Data
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Nama Sumber
                                    Dana</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase">Lingkup</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($sumberDanas as $sd)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors text-sm">
                                <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{
                                    $sd->kode }}</td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $sd->nama }}</td>
                                <td class="px-6 py-4">
                                    @if($sd->school_id)
                                    <span
                                        class="px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full uppercase">Sekolah</span>
                                    @else
                                    <span
                                        class="px-2 py-1 text-[10px] font-bold bg-emerald-100 text-emerald-700 rounded-full uppercase">Global</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">Belum ada data
                                    sumber dana.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-manual-layout>