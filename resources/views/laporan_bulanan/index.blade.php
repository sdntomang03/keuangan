<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-black text-xl text-gray-800 leading-tight">Laporan Bulanan</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $anggaran->nama_anggaran }} · Tahun {{ $anggaran->tahun }}</p>
            </div>
            @can('kelola-anggaran')
            <a href="{{ route('laporan-bulanan.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                + Isi Laporan Bulanan
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-600">
                            <tr>
                                <th class="px-5 py-3">Bulan</th>
                                <th class="px-5 py-3">Nomor Surat</th>
                                <th class="px-5 py-3 text-right">Jumlah Penerimaan</th>
                                <th class="px-5 py-3 text-right">Jumlah Pengeluaran</th>
                                <th class="px-5 py-3 text-right">Sisa Dana</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($laporans as $laporan)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::createFromDate($laporan->tahun, $laporan->bulan, 1)->locale('id')->translatedFormat('F Y') }}
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $laporan->nomor_surat ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">{{ number_format($laporan->jumlah_penerimaan, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">{{ number_format($laporan->jumlah_pengeluaran, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-bold">{{ number_format($laporan->sisa_dana, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if($laporan->belanja_belum_diatur)
                                    <span title="Atur jenis belanja Korek sebelum cetak" class="mr-3 text-xs font-bold text-amber-700">Korek belum lengkap</span>
                                    @endif
                                    <a href="{{ route('laporan-bulanan.print', $laporan->id) }}" target="_blank"
                                        class="font-semibold text-indigo-600 hover:text-indigo-800">Cetak</a>
                                    <a href="{{ route('laporan-bulanan.realisasi', $laporan->id) }}" target="_blank"
                                        class="ml-3 font-semibold text-emerald-700 hover:text-emerald-900">Realisasi</a>
                                    @can('kelola-anggaran')
                                    <a href="{{ route('laporan-bulanan.edit', $laporan->id) }}"
                                        class="ml-3 font-semibold text-amber-600 hover:text-amber-800">Ubah</a>
                                    <form action="{{ route('laporan-bulanan.destroy', $laporan->id) }}" method="POST" class="ml-3 inline"
                                        onsubmit="return confirm('Hapus laporan bulan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                    Belum ada laporan bulanan untuk anggaran ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($laporans->hasPages())
                <div class="border-t border-gray-100 px-5 py-3">{{ $laporans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
