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
    // 1. Deteksi apakah ini pekerjaan perbaikan atau bukan
    $isPerbaikan = str_contains(strtolower($belanja->uraian), 'perbaikan') ||
    str_contains(strtolower($belanja->uraian), 'pemeliharaan');

    // 2. MENGURUTKAN FOTO (Sebelum -> Proses -> Setelah)
    // Kita beri nilai bobot: sebelum=1, proses=2, setelah=3, umum=4
    $bobotStatus = [
    'sebelum' => 1,
    'proses' => 2,
    'setelah' => 3,
    'umum' => 4
    ];

    $allFotos = $belanja->fotos->sortBy(function($foto) use ($bobotStatus) {
    $status = strtolower($foto->status ?? 'umum');
    // Jika status ada di array $bobotStatus, gunakan angkanya. Jika tidak, taruh di paling akhir (5)
    return $bobotStatus[$status] ?? 5;
    })->values(); // values() digunakan untuk me-reset index array (0, 1, 2, dst) setelah diurutkan

    $totalFotos = $allFotos->count();

    // 3. Pisahkan Foto Pertama (Halaman 1) dan Sisanya (Halaman 2 dst)
    $fotoPertama = $allFotos->first();
    $fotoSisaChunks = $allFotos->slice(1)->chunk(2);

    // 4. Helper untuk Label Foto
    function getLabelFoto($foto, $isPerbaikan, $index) {
    if (!$isPerbaikan) return "FOTO PEKERJAAN/BARANG (Ke-" . ($index + 1) . ")";

    $status = strtolower($foto->status ?? '');
    if ($status == 'sebelum') return "FOTO SEBELUM PERBAIKAN";
    if ($status == 'proses') return "FOTO PROSES PERBAIKAN";
    if ($status == 'setelah') return "FOTO SETELAH PERBAIKAN";

    return "DOKUMENTASI PERBAIKAN (Ke-" . ($index + 1) . ")";
    }
    @endphp

    {{-- ========================================================== --}}
    {{-- HALAMAN 1: KOP, INFO, & HANYA 1 FOTO --}}
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

        {{-- Tabel Foto Pertama --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; page-break-inside: avoid;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    {{ getLabelFoto($fotoPertama, $isPerbaikan, 0) }}
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
                    @if($fotoPertama->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{ $fotoPertama->keterangan }}
                    </p>
                    @endif
                </td>
            </tr>
        </table>

        {{-- TTD Jika hanya ada 1 foto --}}
        @if($totalFotos == 1)
        <div class="ttd-container" style="margin-top: 20px; page-break-inside: avoid;">
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
    {{-- HALAMAN 2 DST: SISANYA (MAX 2 FOTO PER HALAMAN) --}}
    {{-- ========================================================== --}}
    @if($totalFotos > 1)
    <div class="page-break"></div>

    @foreach($fotoSisaChunks as $chunkIndex => $chunk)
    <div class="halaman">
        <div style="height: 10px;"></div>

        @foreach($chunk as $subIndex => $fotoSisa)
        @php
        // Hitung index asli untuk label
        $originalIndex = ($chunkIndex * 2) + $subIndex + 1;
        @endphp
        <table
            style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid;">
            <tr>
                <td
                    style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03;">
                    {{ getLabelFoto($fotoSisa, $isPerbaikan, $originalIndex) }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 8px; vertical-align: middle;">
                    @php
                    $pathSisa = storage_path('app/public/' . $fotoSisa->path);
                    $srcSisa = file_exists($pathSisa) ? $pathSisa : public_path('images/no-image.jpg');
                    @endphp
                    <img src="{{ $srcSisa }}"
                        style="max-width: 100%; height: auto; max-height: 270px; object-fit: contain;">
                    @if($fotoSisa->keterangan)
                    <p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{ $fotoSisa->keterangan }}
                    </p>
                    @endif
                </td>
            </tr>
        </table>
        @endforeach

        {{-- TTD Hanya di halaman paling terakhir --}}
        @if($loop->last)
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

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach
    @endif
</body>

</html>