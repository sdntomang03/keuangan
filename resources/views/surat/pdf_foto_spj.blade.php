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
    // 1. Identifikasi Perbaikan
    $isPerbaikan = str_contains(strtolower($belanja->uraian), 'perbaikan') ||
    str_contains(strtolower($belanja->uraian), 'pemeliharaan');

    // 2. Urutkan Foto
    $bobotStatus = ['sebelum' => 1, 'proses' => 2, 'setelah' => 3, 'umum' => 4];
    $allFotos = $belanja->fotos->sortBy(function($foto) use ($bobotStatus) {
    $status = strtolower($foto->status ?? 'umum');
    return $bobotStatus[$status] ?? 5;
    })->values();

    $totalFotos = $allFotos->count();

    // 3. Pisahkan Foto Halaman 1 dan Sisanya
    $fotoPertama = $allFotos->first();
    $fotoSisaChunks = $allFotos->slice(1)->chunk(2);

    // 4. Variabel Pelacak Status (Kunci agar header tidak mengulang)
    $lastStatus = '';
    @endphp

    {{-- ========================================================== --}}
    {{-- HALAMAN 1: KOP, INFO, & FOTO PERTAMA --}}
    {{-- ========================================================== --}}
    <div class="halaman">
        <x-kop :sekolah="$sekolah" />

        <div class="judul-dokumen">DOKUMENTASI {{ $isPerbaikan ? 'PERBAIKAN' : 'BARANG/PEKERJAAN' }}</div>

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

        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; page-break-inside: avoid;">
            @php $lastStatus = strtolower($fotoPertama->status ?? 'umum'); @endphp
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    {{ getJudulTabel($lastStatus, $isPerbaikan) }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 10px; vertical-align: middle;">
                    @php
                    $path1 = storage_path('app/public/' . $fotoPertama->path);
                    $src1 = file_exists($path1) ? $path1 : public_path('images/no-image.jpg');
                    @endphp
                    <img src="{{ $src1 }}"
                        style="max-width: 100%; height: auto; max-height: 350px; object-fit: contain;">
                    @if($fotoPertama->keterangan)<p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket:
                        {{ $fotoPertama->keterangan }}</p>@endif
                </td>
            </tr>
        </table>

        @if($totalFotos == 1)
        @include('surat.partials.ttd_dokumentasi') {{-- Gunakan partial agar rapi --}}
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- HALAMAN 2 DST: SISANYA (KONTINU) --}}
    {{-- ========================================================== --}}
    @if($totalFotos > 1)
    <div class="page-break"></div>

    @foreach($fotoSisaChunks as $chunk)
    <div class="halaman">
        <div style="height: 10px;"></div>

        <table
            style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid;">
            @foreach($chunk as $fotoSisa)
            @php $currentStatus = strtolower($fotoSisa->status ?? 'umum'); @endphp

            {{-- HEADER HANYA MUNCUL JIKA STATUS BERUBAH --}}
            @if($currentStatus !== $lastStatus)
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03; {{ !$loop->first ? 'border-top: 1px solid #000;' : '' }}">
                    {{ getJudulTabel($currentStatus, $isPerbaikan) }}
                </td>
            </tr>
            @php $lastStatus = $currentStatus; @endphp
            @endif

            <tr>
                <td
                    style="text-align: center; padding: 10px; vertical-align: middle; {{ !$loop->last ? 'border-bottom: 1px dashed #888;' : '' }}">
                    @php
                    $pathSisa = storage_path('app/public/' . $fotoSisa->path);
                    $srcSisa = file_exists($pathSisa) ? $pathSisa : public_path('images/no-image.jpg');
                    @endphp
                    <img src="{{ $srcSisa }}"
                        style="max-width: 100%; height: auto; max-height: 270px; object-fit: contain;">
                    @if($fotoSisa->keterangan)<p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{
                        $fotoSisa->keterangan }}</p>@endif
                </td>
            </tr>
            @endforeach
        </table>

        @if($loop->last)
        {{-- Bagian Tanda Tangan --}}
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

    @if(!$loop->last)<div class="page-break"></div>@endif
    @endforeach
    @endif
</body>

</html>