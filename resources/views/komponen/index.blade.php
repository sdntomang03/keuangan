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

            {{-- Filter Area (Tidak butuh form submit lagi, ditangani jQuery) --}}
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <div class="max-w-xl">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter berdasarkan Kode
                        Rekening</label>
                    <div class="relative">
                        <select id="filter-rekening" name="kode_rekening"
                            class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm bg-white">
                            <option value="">-- Tampilkan Semua Rekening --</option>
                            @foreach($koreks as $korek)
                            <option value="{{ $korek->kode }}">{{ $korek->kode }} - {{ $korek->uraian_singkat }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="p-6 overflow-x-auto">
                <table id="tabel-komponen" class="w-full text-left border-collapse table-fixed">
                    <thead>
                        <tr class="bg-blue-600 text-white text-sm uppercase tracking-wider">
                            <th class="px-4 py-3 font-semibold w-[15%]">Kode Rekening</th>
                            <th class="px-4 py-3 font-semibold w-[22%]">Nama Komponen</th>
                            <th class="px-4 py-3 font-semibold w-[35%]">Spesifikasi</th>
                            <th class="px-4 py-3 font-semibold w-[8%]">Satuan</th>
                            <th class="px-4 py-3 font-semibold text-right w-[12%]">Harga</th>
                            <th class="px-4 py-3 rounded-tr-lg font-semibold text-center w-[8%]">Tahun</th>
                        </tr>
                    </thead>
                    {{-- <tbody> dikosongkan karena akan diisi oleh DataTables Server-Side --}}
                    <tbody class="bg-white divide-y divide-gray-200">
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
            // Inisialisasi DataTables
            var table = $('#tabel-komponen').DataTable({
                "processing": true,  // Memunculkan tulisan "Processing..." saat loading
                "serverSide": true,  // Mengaktifkan Server-Side
                "ajax": {
                    "url": "{{ route('komponenrkas.index') }}", // Panggil route yang mengarah ke CariKomponen
                    "type": "GET",
                    "data": function (d) {
                        // Kirim data filter dropdown ke server secara dinamis setiap kali reload
                        d.kode_rekening = $('#filter-rekening').val();
                    }
                },
                "columns": [
                    { data: 'kode_rekening', name: 'kode_rekening', className: 'px-4 py-3 text-sm text-gray-600 break-words hover:bg-blue-50 transition-colors' },
                    { data: 'namakomponen', name: 'namakomponen', className: 'px-4 py-3 text-sm text-gray-900 break-words whitespace-normal' },
                    { data: 'spek', name: 'spek', className: 'px-4 py-3 text-sm text-gray-500 break-words whitespace-normal' },
                    { data: 'satuan', name: 'satuan', className: 'px-4 py-3 text-sm text-gray-600' },
                    { data: 'harga', name: 'harga', className: 'px-4 py-3 text-sm text-gray-900 text-right font-semibold' },
                    { data: 'tahun', name: 'tahun', className: 'px-4 py-3 text-sm text-center text-gray-600' }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "pageLength": 25,
                "ordering": true,
                "responsive": true,
                "autoWidth": false,
                "columnDefs": [
                    { "width": "15%", "targets": 0 },
                    { "width": "22%", "targets": 1 },
                    { "width": "35%", "targets": 2 },
                    { "width": "8%",  "targets": 3 },
                    { "width": "12%", "targets": 4 },
                    { "width": "8%",  "targets": 5 }
                ],
                "dom": '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>'
            });

            // Trigger reload DataTables jika dropdown diubah
            $('#filter-rekening').change(function(){
                table.draw();
            });
        });
    </script>
</x-app-layout>
