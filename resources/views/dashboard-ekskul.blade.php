<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Ekstrakurikuler') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <h3 class="text-lg font-bold text-gray-800 mb-4">Selamat datang, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600 mb-8">Berikut adalah ringkasan kegiatan ekstrakurikuler yang Anda bina.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="text-xs font-bold text-blue-500 uppercase">Total Ekskul</div>
                        <div class="text-2xl font-black text-gray-800">{{ $totalEkskul }}</div>
                    </div>

                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                        <div class="text-xs font-bold text-emerald-500 uppercase">Total Pertemuan</div>
                        <div class="text-2xl font-black text-gray-800">{{ $totalPertemuan }}</div>
                    </div>

                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                        <div class="text-xs font-bold text-purple-500 uppercase">Total Foto Laporan</div>
                        <div class="text-2xl font-black text-gray-800">{{ $totalFoto }}</div>
                    </div>
                </div>

                <h4 class="text-md font-bold text-gray-800 border-b pb-2 mb-4">Laporan Terbaru</h4>
                @if($laporanTerbaru->isEmpty())
                <p class="text-sm text-gray-500 italic">Belum ada laporan pertemuan.</p>
                @else
                <ul class="divide-y divide-gray-200">
                    @foreach($laporanTerbaru as $laporan)
                    <li class="py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ $laporan->ekskul->nama ?? 'Ekskul' }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($laporan->tanggal)->format('d M
                                Y') }} - {{ $laporan->materi }}</p>
                        </div>
                        <div class="text-xs px-2 py-1 bg-gray-100 rounded text-gray-600 font-bold">
                            {{ $laporan->fotos_count }} Foto
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>