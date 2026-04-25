<x-manual-layout>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white uppercase tracking-tight">Daftar Kegiatan
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Kelola daftar kegiatan dan susun rincian komponen
                    RKAS</p>
            </div>

            <div class="flex gap-2">
                <button onclick="document.getElementById('modalTambahKegiatan').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah
                </button>

            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-lg text-sm font-medium">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        @if($rekapAnggaran->count() > 0)
        <div class="mb-8">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rekapitulasi Anggaran Terinput
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($rekapAnggaran as $rekap)
                <div
                    class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div
                            class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">
                            TA. {{ $rekap->tahun_anggaran }} &bull; {{ $rekap->sumber_dana }}
                        </div>
                        <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            Rp {{ number_format($rekap->total_anggaran, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">ID Giat</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Nama Kegiatan
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">Sumber Dana</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">Total Anggaran
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($kegiatan as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $item->id_kegiatan }}
                            </td>
                            <td class="px-6 py-4">


                                <div class="mt-1.5 flex flex-col space-y-0.5">
                                    <div class="flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-1 text-indigo-500" fill="currentColor"
                                            viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        <span
                                            class="text-[10px] font-extrabold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                            {{ $item->program->nama_program ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center pl-3">
                                        <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span
                                            class="text-[10px] font-medium text-slate-500 dark:text-slate-500 uppercase tracking-tight italic">
                                            {{ $item->subProgram->nama_sub_program ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-xs">
                                    {{ $item->sumberDana->nama ?? 'Belum Diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-right text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($item->rkasManuals->sum('total_akhir'), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('kegiatan.tambah_komponen', $item->id) }}" title="Susun RKAS"
                                        class="inline-flex items-center p-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                            </path>
                                        </svg>
                                    </a>

                                    <form action="{{ route('kegiatan.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini? Semua rincian RKAS di dalamnya juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Kegiatan"
                                            class="inline-flex items-center p-2 bg-rose-50 text-rose-700 border border-rose-200 rounded-md hover:bg-rose-600 hover:text-white transition-all shadow-sm opacity-50 group-hover:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada data kegiatan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalTambahKegiatan" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/75 transition-opacity" aria-hidden="true"
                onclick="document.getElementById('modalTambahKegiatan').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg leading-6 font-bold text-slate-800 dark:text-white uppercase" id="modal-title">
                        Tambah Kegiatan Manual</h3>
                    <button type="button"
                        onclick="document.getElementById('modalTambahKegiatan').classList.add('hidden')"
                        class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('kegiatan.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Sumber Dana
                                <span class="text-rose-500">*</span></label>
                            <select name="sumber_dana_id" required
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm p-2.5">
                                <option value="">-- Pilih Sumber Dana --</option>
                                @php $sumberDanas = \App\Models\SumberDanaManual::all(); @endphp
                                @foreach($sumberDanas as $sd)
                                <option value="{{ $sd->id }}">[{{ $sd->kode }}] - {{ $sd->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Standar
                                Pendidikan (Program) <span class="text-rose-500">*</span></label>
                            <select id="program_select" name="program_id" required
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm p-2.5">
                                <option value="">-- Pilih Standar Pendidikan --</option>
                                @php $programs = \App\Models\Program::orderBy('nama_program')->get(); @endphp
                                @foreach($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->nama_program }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Komponen (Sub
                                Program) <span class="text-rose-500">*</span></label>
                            <select id="sub_program_select" name="sub_program_id" required disabled
                                class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-slate-100 text-slate-500 text-sm p-2.5 cursor-not-allowed">
                                <option value="">-- Pilih Program Terlebih Dahulu --</option>
                            </select>
                        </div>


                    </div>

                    <div
                        class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button"
                            onclick="document.getElementById('modalTambahKegiatan').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan
                            Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const programSelect = document.getElementById('program_select');
            const subProgramSelect = document.getElementById('sub_program_select');

            programSelect.addEventListener('change', function() {
                const programId = this.value;

                subProgramSelect.innerHTML = '<option value="">Loading...</option>';
                subProgramSelect.disabled = true;

                if (programId) {
                    // Pastikan route ajax.sub_programs sudah didaftarkan di web.php
                    fetch(`/ajax/sub-programs?program_id=${programId}`)
                        .then(response => response.json())
                        .then(data => {
                            subProgramSelect.innerHTML = '<option value="">-- Pilih Komponen --</option>';
                            if(data.length > 0) {
                                data.forEach(sub => {
                                    subProgramSelect.innerHTML += `<option value="${sub.id}">${sub.nama_sub_program}</option>`;
                                });
                                subProgramSelect.disabled = false;
                                subProgramSelect.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                            } else {
                                subProgramSelect.innerHTML = '<option value="">(Tidak ada komponen)</option>';
                            }
                        });
                } else {
                    subProgramSelect.innerHTML = '<option value="">-- Pilih Program Terlebih Dahulu --</option>';
                }
            });
        });
    </script>
</x-manual-layout>