<x-app-layout>
    {{-- CSS DataTables Integrasi Tailwind --}}
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" rel="stylesheet">

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Page Title & Action Button --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fa fa-cube text-blue-600 mr-3"></i> Data Komponen
            </h3>
            <a href="{{ route('komponenrkas.import') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fa fa-upload mr-2"></i> Import JSON Komponen
            </a>
        </div>

        {{-- Alert Messages --}}
        @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border-l-4 border-green-500 shadow-sm flex items-start"
            role="alert">
            <i class="fa fa-check-circle text-green-500 mt-0.5 mr-3"></i>
            <div>
                <h3 class="text-sm font-medium text-green-800">Berhasil</h3>
                <div class="mt-1 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            </div>
        </div>
        @endif

        {{-- Main Card --}}
        <div class="bg-white rounded-xl shadow-md border-t-4 border-blue-600 overflow-hidden">

            {{-- Form Filter --}}
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <form action="{{ route('komponenrkas.index') }}" method="GET" id="form-pencarian" class="max-w-xl">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter berdasarkan Kode
                        Rekening</label>
                    <div class="relative">
                        <select name="kode_rekening"
                            class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm bg-white"
                            onchange="document.getElementById('form-pencarian').submit();">
                            <option value="">-- Tampilkan Semua Rekening --</option>
                            @foreach($koreks as $korek)
                            <option value="{{ $korek->kode }}" {{ request('kode_rekening')==$korek->kode ? 'selected' :
                                '' }}>
                                {{ $korek->kode }} - {{ $korek->uraian_singkat }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="p-6 overflow-x-auto">
                {{-- Tambahkan table-fixed agar lebar kolom konsisten --}}
                <table id="tabel-komponen" class="w-full text-left border-collapse table-fixed">
                    <thead>
                        <tr class="bg-blue-600 text-white text-sm uppercase tracking-wider">
                            {{-- Atur lebar secara proporsional dengan persentase --}}
                            <th class="px-4 py-3 rounded-tl-lg font-semibold text-center w-[8%]">ID</th>
                            <th class="px-4 py-3 font-semibold w-[15%]">Kode Rekening</th>
                            <th class="px-4 py-3 font-semibold w-[22%]">Nama Komponen</th>
                            <th class="px-4 py-3 font-semibold w-[25%]">Spesifikasi</th>
                            <th class="px-4 py-3 font-semibold w-[10%]">Satuan</th>
                            <th class="px-4 py-3 font-semibold text-right w-[12%]">Harga</th>
                            <th class="px-4 py-3 rounded-tr-lg font-semibold text-center w-[8%]">Tahun</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($komponens as $komp)
                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm text-center text-gray-900 font-medium">{{ $komp->idkomponen }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 break-words">{{ $komp->kode_rekening }}</td>

                            {{-- break-words dan whitespace-normal akan memaksa teks panjang turun ke bawah --}}
                            <td class="px-4 py-3 text-sm text-gray-900 break-words whitespace-normal">{{
                                $komp->namakomponen }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 break-words whitespace-normal">{{ $komp->spek }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">{{
                                    $komp->satuan }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                Rp {{ number_format($komp->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $komp->tahun }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script Dependencies --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabel-komponen').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "pageLength": 25,
                "ordering": true,
                "responsive": true,
                "autoWidth": false, // WAJIB: Mencegah DataTables mengabaikan lebar tabel Tailwind Anda
                "dom": '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>'
            });
        });
    </script>
</x-app-layout>
