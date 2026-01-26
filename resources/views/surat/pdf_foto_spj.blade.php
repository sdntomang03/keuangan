<!DOCTYPE html>
<html>

<head>
    <title>Dokumentasi Barang/Pekerjaan</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
        }

        .page-break {
            page-break-after: always;
        }

        /* Layout Tabel & Header */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .va-top {
            vertical-align: top;
        }

        /* Kop Surat (Standar Dinas) */
        .kop-container {
            border-bottom: 3px double black;
            margin-bottom: 15px;
            padding-bottom: 5px;
            position: relative;
        }

        .kop-logo {
            position: absolute;
            left: 0;
            top: 5px;
            width: 90px;
        }

        .kop-teks {
            text-align: center;
            margin-left: 80px;
            /* Memberi ruang untuk logo */
        }

        .kop-nama {
            font-size: 14pt;
            font-weight: bold;
        }

        .kop-alamat {
            font-size: 10pt;
        }

        /* Judul Halaman */
        .judul-dokumen {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 20px;
        }

        /* Tabel Informasi */
        .table-info td {
            padding: 3px;
            vertical-align: top;
        }

        .label-col {
            width: 140px;
        }

        .sep-col {
            width: 10px;
        }

        /* Container Foto */
        .foto-container {
            width: 100%;
            height: 500px;
            /* Tinggi fix agar layout tidak lari */
            border: 1px solid #000;
            display: table;
            /* Hack untuk centering vertical di PDF lama */
            margin-bottom: 20px;
            text-align: center;
        }

        .foto-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .foto-img {
            max-width: 95%;
            max-height: 430px;
            object-fit: contain;
        }

        /* Tanda Tangan */
        .ttd-container {
            margin-top: 10px;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    @foreach($belanja->fotos as $index => $foto)

    {{-- WRAPPER PER HALAMAN --}}
    <div class="halaman">

        {{-- 1. KOP SURAT (SESUAI TEMPLATE) --}}
        <div class="kop-container">
            {{-- Ganti path logo sesuai lokasi file Anda --}}
            <img src="{{ public_path('images/logo_dki.png') }}" class="kop-logo" alt="Logo">

            <div class="kop-teks">
                <div class="text-bold">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</div>
                <div class="text-bold">DINAS PENDIDIKAN</div>
                <div class="kop-nama">{{ strtoupper($sekolah->nama_sekolah) }}</div>
                <div class="kop-alamat">
                    {{ $sekolah->alamat }}<br>
                    Kel. {{ $sekolah->kelurahan ?? '-' }}, Kec. {{ $sekolah->kecamatan ?? '-' }}<br>
                    Telepon: {{ $sekolah->no_telp ?? '-' }}, E-mail: {{ $sekolah->email ?? '-' }}<br>
                    JAKARTA, Kode Pos : {{ $sekolah->kodepos ?? '-' }}
                </div>
            </div>
        </div>

        {{-- 2. JUDUL --}}
        <div class="judul-dokumen">DOKUMENTASI BARANG/PEKERJAAN</div>

        {{-- 3. TABEL INFORMASI --}}
        <table class="table-info">
            <tr>
                <td class="label-col">Nama Sekolah</td>
                <td class="sep-col">:</td>
                <td>{{ $sekolah->nama_sekolah }}</td>
            </tr>
            <tr>
                <td>Jenis Anggaran</td>
                <td>:</td>
                <td>{{ $belanja->anggaran->nama_anggaran ?? 'BOSP' }} Tahun {{ $belanja->anggaran->tahun }}</td>
            </tr>
            <tr>
                <td>Kode Rekening</td>
                <td>:</td>
                <td>{{ $belanja->korek->ket ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan Belanja</td>
                <td>:</td>
                <td>{{ $belanja->uraian }}</td>
            </tr>
            <tr>
                <td>Triwulan</td>
                <td>:</td>
                <td>{{ $triwulan }}</td>
            </tr>
            <tr>
                <td>Tahun Anggaran</td>
                <td>:</td>
                <td>{{ $tahun }}</td>
            </tr>
        </table>

        <br>

        {{-- 4. FOTO (DIBUAT KOTAK SESUAI TEMPLATE) --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    FOTO PEKERJAAN/BARANG
                </td>
            </tr>

            <tr>
                <td style="text-align: center; padding: 10px; vertical-align: middle;">
                    {{-- Logic Penentuan Path Gambar --}}
                    @php
                    // Pastikan path sesuai dengan konfigurasi filesystem Anda
                    $fullPath = storage_path('app/public/' . $foto->path);

                    // Cek file ada atau tidak
                    if (!file_exists($fullPath)) {
                    $fullPath = public_path('images/no-image.jpg');
                    }
                    @endphp

                    {{-- Render Gambar --}}
                    <img src="{{ $fullPath }}" style="max-width: 100%; height: auto; max-height: 300px;">
                </td>
            </tr>
        </table>
        <div class="ttd-container">
            <table style="width: 100%;">
                <tr>
                    <td width="50%"></td> {{-- Kiri Kosong --}}
                    <td width="50%" class="text-center">
                        Kepala {{ $sekolah->nama_sekolah }},
                        <br><br><br><br><br>
                        <b><u>{{ $sekolah->nama_kepala_sekolah }}</u></b><br>
                        NIP. {{ $sekolah->nip_kepala_sekolah }}
                    </td>
                </tr>
            </table>
        </div>



    </div>

    {{-- PAGE BREAK (Kecuali foto terakhir) --}}
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

    @endforeach

</body>

</html>