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

    @foreach($belanja->fotos as $index => $foto)

    <div class="halaman">
        {{-- 1. KOP SURAT & INFO HANYA DI HALAMAN PERTAMA --}}
        @if($loop->first)
        <x-kop :sekolah="$sekolah" />

        <div class="judul-dokumen">DOKUMENTASI BARANG/PEKERJAAN</div>

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
                <td>Kegiatan Belanja</td>
                <td>:</td>
                <td>{{ $belanja->uraian }}</td>
            </tr>
            <tr>
                <td>Triwulan / Tahun</td>
                <td>:</td>
                <td>{{ $triwulan }} / {{ $tahun }}</td>
            </tr>
        </table>
        <br>
        @else
        {{-- Berikan jarak sedikit di halaman kedua dst agar foto tidak terlalu menempel ke atas --}}
        <div style="height: 30px;"></div>
        @endif

        {{-- 2. TABEL FOTO (Muncul di setiap halaman untuk setiap foto) --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    FOTO PEKERJAAN/BARANG (Ke-{{ $index + 1 }})
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 15px; vertical-align: middle;">
                    @php
                    $fullPath = storage_path('app/public/' . $foto->path);
                    if (!file_exists($fullPath)) {
                    $fullPath = public_path('images/no-image.jpg');
                    }
                    @endphp

                    {{-- Sesuaikan max-height. Di halaman 1 lebih kecil karena ada KOP,
                    di halaman 2 bisa lebih besar karena KOP hilang --}}
                    <img src="{{ $fullPath }}"
                        style="max-width: 100%; height: auto; max-height: {{ $loop->first ? '350px' : '650px' }}; object-fit: contain;">

                    @if($foto->keterangan)
                    <p style="margin-top: 10px; font-style: italic;">Keterangan: {{ $foto->keterangan }}</p>
                    @endif
                </td>
            </tr>
        </table>

        {{-- 3. TANDA TANGAN HANYA DI HALAMAN TERAKHIR --}}
        @if($loop->last)
        <div class="ttd-container">
            <table style="width: 100%;">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" class="text-center">
                        Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                        Kepala {{ $sekolah->nama_sekolah }},
                        <br><br><br><br><br>
                        <b><u>{{ $sekolah->nama_kepala_sekolah }}</u></b><br>
                        NIP. {{ $sekolah->nip_kepala_sekolah }}
                    </td>
                </tr>
            </table>
        </div>
        @endif
    </div>

    {{-- PAGE BREAK: Pindah halaman jika bukan foto terakhir --}}
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

    @endforeach

</body>

</html>