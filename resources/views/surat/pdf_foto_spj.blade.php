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
    // 1. Hitung total foto
    $totalFotos = $belanja->fotos->count();

    // 2. Ambil foto pertama untuk Halaman 1
    $fotoPertama = $belanja->fotos->first();

    // 3. Ambil sisa foto, lalu pecah per kelompok isi 2 foto untuk Halaman 2 dst
    $fotoSisaChunks = $belanja->fotos->slice(1)->chunk(2);

    // 4. Format Tanggal dari Foto Pertama
    $tanggalRaw = $fotoPertama->tanggal ?? $fotoPertama->created_at;
    $tanggalFoto = \Carbon\Carbon::parse($tanggalRaw)->translatedFormat('d F Y');
    @endphp

    {{-- ========================================================== --}}
    {{-- HALAMAN 1: KOP SURAT, TABEL INFO, & FOTO PERTAMA --}}
    {{-- ========================================================== --}}
    <div class="halaman">

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
                <td>Triwulan / Tahun</td>
                <td>:</td>
                <td>{{ $triwulan }} / {{ $tahun }}</td>
            </tr>
            <tr>
                <td>Tanggal Dok.</td>
                <td>:</td>
                <td>{{ $tanggalFoto }}</td>
            </tr>
        </table>
        <br>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 4px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    FOTO PEKERJAAN/BARANG (Ke-1)
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 10px; vertical-align: middle;">
                    @php
                    $fullPath = storage_path('app/public/' . $fotoPertama->path);
                    if (!file_exists($fullPath)) {
                    $fullPath = public_path('images/no-image.jpg');
                    }
                    @endphp

                    {{-- Max height 350px agar pas disandingkan dengan Kop Surat --}}
                    <img src="{{ $fullPath }}"
                        style="max-width: 100%; height: auto; max-height: 350px; object-fit: contain;">

                    @if($fotoPertama->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Keterangan: {{
                        $fotoPertama->keterangan }}</p>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Jika HANYA ADA 1 FOTO secara total, Tanda Tangan langsung diletakkan di Halaman 1 --}}
        @if($totalFotos == 1)
        <div class="ttd-container" style="margin-top: 20px;">
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


    {{-- ========================================================== --}}
    {{-- HALAMAN 2 DST: FOTO KE-2, KE-3 Dst (MAX 2 FOTO PER HALAMAN)--}}
    {{-- ========================================================== --}}
    @if($totalFotos > 1)
    {{-- Berikan Page Break setelah Halaman 1 selesai --}}
    <div class="page-break"></div>

    @php $fotoCounter = 2; @endphp

    @foreach($fotoSisaChunks as $chunk)
    <div class="halaman">
        <div style="height: 10px;"></div> {{-- Spasi atas diperkecil --}}

        @foreach($chunk as $fotoSisa)
        {{-- page-break-inside: avoid mencegah 1 tabel foto terbelah dua --}}
        <table
            style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 4px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    FOTO PEKERJAAN/BARANG (Ke-{{ $fotoCounter++ }})
                </td>
            </tr>
            <tr>
                {{-- Padding diperkecil agar ruang untuk foto lebih banyak --}}
                <td style="text-align: center; padding: 8px; vertical-align: middle;">
                    @php
                    $fullPathSisa = storage_path('app/public/' . $fotoSisa->path);
                    if (!file_exists($fullPathSisa)) {
                    $fullPathSisa = public_path('images/no-image.jpg');
                    }
                    @endphp

                    {{-- DIPERKECIL: Max height 270px (Sebelumnya 320px)
                    270px x 2 foto = 540px.
                    A4 tinggi area cetak ~900px, jadi sisa ruang sangat lega untuk tanda tangan --}}
                    <img src="{{ $fullPathSisa }}"
                        style="max-width: 100%; height: auto; max-height: 270px; object-fit: contain;">

                    @if($fotoSisa->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Keterangan: {{
                        $fotoSisa->keterangan }}</p>
                    @endif
                </td>
            </tr>
        </table>
        @endforeach

        {{-- Jika ini adalah kelompok foto yang TERAKHIR, tampilkan Tanda Tangan --}}
        @if($loop->last)
        {{-- page-break-inside: avoid agar tanda tangan dan nama tidak terpisah halaman --}}
        <div class="ttd-container" style="margin-top: 15px; page-break-inside: avoid;">
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

    {{-- Berikan page break kecuali di kelompok (chunk) yang paling akhir --}}
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
    @endif

</body>

</html>