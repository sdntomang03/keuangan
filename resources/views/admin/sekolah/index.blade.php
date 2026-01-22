<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight italic">
                {{ __('Data Master Instansi / Sekolah') }}
            </h2>
            <a href="{{ route('admin.sekolah.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-md transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Sekolah
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div
                class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 text-sm font-bold shadow-sm rounded-r-lg">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50/50 border-b text-gray-400 text-[10px] uppercase tracking-widest font-extrabold">
                            <th class="p-4">Identitas Sekolah</th>
                            <th class="p-4">NPSN</th>
                            <th class="p-4">Anggaran Aktif</th>
                            <th class="p-4">Detail Pengelola</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sekolahs as $sekolah)
                        <tr class="hover:bg-indigo-50/30 transition duration-150">
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($sekolah->logo)
                                        <img src="{{ asset('storage/'.$sekolah->logo) }}"
                                            class="h-10 w-10 rounded-lg object-cover shadow-sm border border-gray-100">
                                        @else
                                        <div
                                            class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                            {{ substr($sekolah->nama_sekolah, 0, 2) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $sekolah->nama_sekolah }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $sekolah->email ?? 'Email tidak
                                            tersedia' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs font-mono text-gray-600">{{ $sekolah->npsn }}</td>
                            <td class="p-4 text-center">
                                @php
                                $anggaranAktif = $sekolah->anggarans ? $sekolah->anggarans->where('is_aktif',
                                true)->first() : null;
                                @endphp
                                @if($anggaranAktif)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    {{ $anggaranAktif->tahun }} - {{ strtoupper($anggaranAktif->singkatan) }}
                                </span>
                                @else
                                <span class="text-[10px] text-gray-400 italic">Belum Ada</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="text-[11px] font-bold text-gray-700 uppercase tracking-tighter">KS: {{
                                    $sekolah->nama_kepala_sekolah }}</div>
                                <div class="text-[10px] text-gray-400">BEN: {{ $sekolah->nama_bendahara }}</div>
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.sekolah.edit', $sekolah->id) }}"
                                    class="inline-flex items-center px-3 py-1 bg-white border border-indigo-200 rounded-md font-bold text-[10px] text-indigo-600 uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition shadow-sm">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic text-sm">
                                Belum ada data sekolah yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 bg-gray-50 border-t">
                    {{ $sekolahs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>