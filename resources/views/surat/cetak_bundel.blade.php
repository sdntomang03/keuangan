<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Semua Dokumen</title>
    <style>
        /* Setup Kertas F4 */
        @page {
            size: 215mm 330mm;
            margin: 1cm 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.2;
        }

        /* Style Tabel Standar (Bisa dipakai di semua surat) */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10pt;
        }

        .table-items th,
        .table-items td {
            border: 1px solid black;
            padding: 5px;
        }

        /* === KUNCI PENTING: PAGE BREAK === */
        .page-break {
            page-break-after: always;
            /* Syntax Lama (tetap support) */
            break-after: page;
            /* Syntax Baru */
            display: block;
            height: 0;
            clear: both;
        }

        /* Tanda Tangan Wrapper */
        .ttd-wrapper {
            margin-top: 20px;
            float: right;
            width: 300px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ==================================================
    SURAT 1: PERMINTAAN HARGA
    ================================================== --}}
    <div class="surat-container">
        <x-kop :sekolah="$sekolah" />

        {{-- Header Surat 1 --}}
        <center>
            <h3>SURAT PERMINTAAN HARGA</h3>
        </center>
        <p>Nomor: {{ $dataSurat['permintaan']->nomor }}</p>
        <p>Tanggal: {{ $dataSurat['permintaan']->tanggal }}</p>

        {{-- Isi Surat 1 --}}
        <p>Mohon dikirimkan penawaran harga untuk barang berikut:</p>
        <table class="table-items">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Barang</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->qty }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tanda Tangan 1 --}}
        <div class="ttd-wrapper">
            <p>Kepala Sekolah</p>
            <br><br><br>
            <p><b>Nama Kepala</b></p>
        </div>
        <div style="clear:both;"></div>
    </div>

    {{-- PEMISAH HALAMAN --}}
    <div class="page-break"></div>


    {{-- ==================================================
    SURAT 2: NEGOSIASI HARGA
    ================================================== --}}
    <div class="surat-container">
        <x-kop :sekolah="$sekolah" />

        {{-- Header Surat 2 --}}
        <center>
            <h3>BERITA ACARA NEGOSIASI HARGA</h3>
        </center>
        <p>Nomor: {{ $dataSurat['negosiasi']->nomor }}</p>

        {{-- Isi Surat 2 --}}
        <p>Telah dilakukan negosiasi harga dengan hasil sebagai berikut:</p>
        <table class="table-items">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Harga Awal</th>
                    <th>Harga Nego</th>
                </tr>
            </thead>
            <tbody>
                {{-- Contoh Loop Item --}}
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->nama_barang }}</td>
                    <td>100.000</td>
                    <td>90.000</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Tanda Tangan 2 --}}
        <div class="ttd-wrapper">
            <p>Pejabat Pengadaan</p>
            <br><br><br>
            <p><b>Nama Pejabat</b></p>
        </div>
        <div style="clear:both;"></div>
    </div>

    {{-- PEMISAH HALAMAN --}}
    <div class="page-break"></div>


    {{-- ==================================================
    SURAT 3: PESANAN (PURCHASE ORDER)
    ================================================== --}}
    <div class="surat-container">
        <x-kop :sekolah="$sekolah" />

        <center>
            <h3>SURAT PESANAN</h3>
        </center>
        <p>Nomor: {{ $dataSurat['pesanan']->nomor }}</p>

        <p>Kami memesan barang sesuai rincian berikut:</p>
        {{-- Tabel Barang Pesanan --}}
        <table class="table-items">
        </table>

        <div class="ttd-wrapper">
            <p>Pemesan</p>
            <br><br><br>
            <p><b>Nama Kepala</b></p>
        </div>
        <div style="clear:both;"></div>
    </div>

    {{-- Script Print Otomatis --}}
    <script>
        window.addEventListener("load", function() {
            setTimeout(() => { window.print(); }, 1000);
        });
    </script>
</body>

</html>