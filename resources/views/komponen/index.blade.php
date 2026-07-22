<x-app-layout>
    {{-- Tambahkan CSS DataTables --}}
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap.min.css" rel="stylesheet">

    <div class="container-fluid">
        {{-- Page Title --}}
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h3 class="page-title text-primary"><i class="fa fa-cube"></i>&nbsp; Data Komponen</h3>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                <a href="{{ route('komponenrkas.import') }}"
                    class="btn btn-outline btn-info waves-effect waves-light m-t-5">
                    <i class="fa fa-upload m-r-5"></i> <span>Import JSON Komponen</span>
                </a>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <div class="row">
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <div class="white-box border-top-lg border-top-blue">

                    {{-- Form Filter Kode Rekening (Server-side) --}}
                    <div class="row m-b-20 p-b-20 b-b">
                        <form action="{{ route('komponenrkas.index') }}" method="GET" id="form-pencarian">
                            <div class="col-md-6">
                                <label class="font-bold">Filter berdasarkan Kode Rekening</label>
                                <select name="kode_rekening" class="form-control select2"
                                    onchange="document.getElementById('form-pencarian').submit();">
                                    <option value="">-- Tampilkan Semua Rekening --</option>
                                    @foreach($koreks as $korek)
                                    <option value="{{ $korek->kode }}" {{ request('kode_rekening')==$korek->kode ?
                                        'selected' : '' }}>
                                        {{ $korek->kode }} - {{ $korek->uraian_singkat }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    {{-- Tabel Data dengan DataTables --}}
                    <div class="table-responsive">
                        <table id="tabel-komponen" class="table table-hover table-striped m-t-10 w-100">
                            <thead>
                                <tr class="bg-blue-600">
                                    <th class="text-white text-center" width="80">ID</th>
                                    <th class="text-white" width="150">Kode Rekening</th>
                                    <th class="text-white">Nama Komponen</th>
                                    <th class="text-white">Spesifikasi</th>
                                    <th class="text-white" width="100">Satuan</th>
                                    <th class="text-white text-right" width="150">Harga</th>
                                    <th class="text-white text-center" width="80">Tahun</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($komponens as $komp)
                                <tr>
                                    <td class="text-center">{{ $komp->idkomponen }}</td>
                                    <td>{{ $komp->kode_rekening }}</td>
                                    <td>{{ $komp->namakomponen }}</td>
                                    <td>{{ $komp->spek }}</td>
                                    <td>{{ $komp->satuan }}</td>
                                    <td class="text-right">{{ number_format($komp->harga, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $komp->tahun }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Tambahkan jQuery dan JS DataTables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap.min.js"></script>

    {{-- Inisialisasi DataTables --}}
    <script>
        $(document).ready(function() {
            $('#tabel-komponen').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Terjemahan bahasa Indonesia
                },
                "pageLength": 25, // Jumlah data per halaman secara default
                "ordering": true, // Mengaktifkan fitur pengurutan klik di header
                "responsive": true
            });
        });
    </script>
</x-app-layout>