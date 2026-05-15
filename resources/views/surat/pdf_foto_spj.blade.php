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

    @php
    // Mengelompokkan foto berdasarkan atribut 'tanggal'.
    // Jika nama kolom di database Anda berbeda (misal: created_at), sesuaikan di bawah ini.
    $groupedFotos = $belanja->fotos->groupBy(function($item) {
    // Memastikan waktu (jam/menit) diabaikan, hanya mengelompokkan berdasarkan format Y-m-d
    return date('Y-m-d', strtotime($item->tanggal ?? $item->created_at));
    });
    @endphp

    {{-- LOOPING PERTAMA: Berdasarkan Kelompok Tanggal (1 Halaman per Tanggal) --}}
    @foreach($groupedFotos as $tanggal => $fotosGroup)

    {{-- WRAPPER PER HALAMAN --}}
    <div class="halaman">

        {{-- 1. KOP SURAT --}}
        <x-kop :sekolah="$sekolah" />

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
                <td>Tanggal Dok.</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <br>

        {{-- 4. FOTO (Satu Tabel, Berisi Banyak Foto Berdasarkan Tanggal yang Sama) --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    FOTO PEKERJAAN/BARANG
                </td>
            </tr>

            {{-- LOOPING KEDUA: Menampilkan semua foto dalam kelompok tanggal ini --}}
            @foreach($fotosGroup as $foto)
            <tr>
                <td style="text-align: center; padding: 10px; border-bottom: 1px solid #000; vertical-align: middle;">
                    @php
                    // Pastikan path sesuai dengan konfigurasi filesystem Anda
                    $fullPath = storage_path('app/public/' . $foto->path);

                    // Cek file ada atau tidak
                    if (!file_exists($fullPath)) {
                    $fullPath = public_path('images/no-image.jpg');
                    }
                    @endphp

                    {{-- Render Gambar. Max-height sedikit dikurangi agar jika ada 2-3 foto di tanggal yang sama, TTD
                    tidak terdorong ke halaman baru --}}
                    <img src="{{ $fullPath }}"
                        style="max-width: 100%; height: auto; max-height: 250px; margin-bottom: 5px;">
                </td>
            </tr>
            @endforeach
        </table>

        {{-- 5. TANDA TANGAN --}}
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

    {{-- PAGE BREAK (Kecuali kelompok foto terakhir) --}}
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

    @endforeach

</body>

</html>