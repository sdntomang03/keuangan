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

    // 2. Urutkan Foto (Sebelum -> Proses -> Setelah -> Umum)
    $bobotStatus = ['sebelum' => 1, 'proses' => 2, 'setelah' => 3, 'umum' => 4];
    $allFotos = $belanja->fotos->sortBy(function($foto) use ($bobotStatus) {
    $status = strtolower($foto->status ?? 'umum');
    return $bobotStatus[$status] ?? 5;
    })->values();

    $totalFotos = $allFotos->count();

    // 3. LOGIKA PEMBAGIAN HALAMAN DINAMIS (KERTAS F4 - 33cm)
    if ($totalFotos <= 2) { $limitPage1=1; // Halaman 1 isi 1 foto $chunkSize=1; // Halaman 2 dst isi 1 foto
        $maxHeight1='400px' ; // F4 lebih tinggi, foto bisa jauh lebih besar $maxHeight2='420px' ; // F4 lebih tinggi,
        foto bisa jauh lebih besar } else { $limitPage1=2; // Halaman 1 isi 2 foto $chunkSize=3; // Halaman 2 dst isi 3
        foto $maxHeight1='265px' ; // Diperbesar agar muat 2 foto + Kop Surat di F4 $maxHeight2='245px' ; // Diperbesar
        agar muat 3 foto + TTD di F4 } $fotoPage1=$allFotos->take($limitPage1);
        $fotoSisaChunks = $allFotos->slice($limitPage1)->chunk($chunkSize);

        // 4. Helper Judul TABEL
        $getJudulTabel = function($status, $isPerbaikan) {
        $status = strtolower($status ?? 'umum');
        if (!$isPerbaikan) return "FOTO PEKERJAAN/BARANG";
        if ($status == 'sebelum') return "FOTO SEBELUM PERBAIKAN";
        if ($status == 'proses') return "FOTO PROSES PERBAIKAN";
        if ($status == 'setelah') return "FOTO SETELAH PERBAIKAN";
        return "DOKUMENTASI PERBAIKAN";
        };

        // 5. Variabel Pelacak Status Lintas Halaman
        $lastStatus = '';
        @endphp

        {{-- ========================================================== --}}
        {{-- HALAMAN 1: KOP, INFO, & FOTO (Bisa 1 atau 2 Tergantung Total)--}}
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

            {{-- Menampilkan Foto Halaman 1 --}}
            @if($fotoPage1->isNotEmpty())
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; page-break-inside: avoid;">
                @foreach($fotoPage1 as $index => $foto)
                @php $currentStatus = strtolower($foto->status ?? 'umum'); @endphp

                {{-- HEADER HANYA MUNCUL JIKA STATUS BERUBAH --}}
                @if($currentStatus !== $lastStatus)
                <tr>
                    <td
                        style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03; {{ $index > 0 ? 'border-top: 1px solid #000;' : '' }}">
                        {{ $getJudulTabel($currentStatus, $isPerbaikan) }}
                    </td>
                </tr>
                @php $lastStatus = $currentStatus; @endphp
                @endif

                <tr>
                    <td
                        style="text-align: center; padding: 10px; vertical-align: middle; {{ !$loop->last ? 'border-bottom: 1px dashed #888;' : '' }}">
                        @php
                        $path1 = storage_path('app/public/' . $foto->path);
                        $src1 = file_exists($path1) ? $path1 : public_path('images/no-image.jpg');
                        @endphp
                        <img src="{{ $src1 }}"
                            style="max-width: 100%; height: auto; max-height: {{ $maxHeight1 }}; object-fit: contain;">
                        @if($foto->keterangan)<p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket: {{
                            $foto->keterangan }}</p>@endif
                    </td>
                </tr>
                @endforeach
            </table>
            @endif

            {{-- Tanda Tangan jika fotonya hanya ada 1 secara total --}}
            @if($fotoSisaChunks->isEmpty())
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
        {{-- HALAMAN 2 DST: SISANYA (Bisa 1 atau 3 Foto Per Halaman) --}}
        {{-- ========================================================== --}}
        @if($fotoSisaChunks->isNotEmpty())
        <div class="page-break"></div>

        @foreach($fotoSisaChunks as $chunk)
        <div class="halaman">
            <div style="height: 10px;"></div>

            <table
                style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 15px; page-break-inside: avoid;">
                @foreach($chunk as $index => $fotoSisa)
                @php $currentStatus = strtolower($fotoSisa->status ?? 'umum'); @endphp

                {{-- HEADER HANYA MUNCUL JIKA STATUS BERUBAH --}}
                @if($currentStatus !== $lastStatus)
                <tr>
                    <td
                        style="text-align: center; font-weight: bold; padding: 5px; border-bottom: 1px solid #000; background-color: #5adb03; {{ $index > 0 ? 'border-top: 1px solid #000;' : '' }}">
                        {{ $getJudulTabel($currentStatus, $isPerbaikan) }}
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
                            style="max-width: 100%; height: auto; max-height: {{ $maxHeight2 }}; object-fit: contain;">
                        @if($fotoSisa->keterangan)<p style="margin-top: 5px; font-style: italic; font-size: 10pt;">Ket:
                            {{ $fotoSisa->keterangan }}</p>@endif
                    </td>
                </tr>
                @endforeach
            </table>

            {{-- Tanda Tangan dipastikan berada di urutan paling akhir dokumen --}}
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

        @if(!$loop->last)<div class="page-break"></div>@endif
        @endforeach
        @endif
</body>

</html>