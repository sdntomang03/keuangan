<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Bundel PDF</title>
    <style>
        /* =========================================
           1. SETUP FONT ARIAL (WAJIB ADA)
           ========================================= */
        @font-face {
            font-family: 'Arial';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/arial.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Arial';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/arialbd.ttf') }}") format("truetype");
        }

        /* =========================================
           2. SETUP KERTAS F4 & MARGIN
           ========================================= */
        @page {
            size: 215mm 330mm;
            /* F4 */
            margin: 1.5cm 2cm 2cm 2cm;
            /* Atas Kanan Bawah Kiri */
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.3;
        }

        /* =========================================
           3. CSS GLOBAL UNTUK TABLE & SURAT
           ========================================= */
        /* Pastikan class ini ada agar partial Anda rapi di PDF */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .table-items th,
        .table-items td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* PEMISAH HALAMAN (PAGE BREAK) */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- ==================================================
    HALAMAN 1: SURAT PERMINTAAN
    ================================================== --}}
    <div class="surat-container">
        {{-- Kita panggil Partial yang sudah Anda punya --}}
        {{-- Pastikan variable yang dikirim sesuai dengan kebutuhan partial --}}
        @include('surat.partials.permintaan', [
        'data' => $dataSurat['permintaan'],
        'items' => $items,
        'sekolah' => $sekolah,
        'rekanan' => $rekanan
        ])
    </div>

    <div class="page-break"></div>

    {{-- ==================================================
    HALAMAN 2: SURAT NEGOSIASI
    ================================================== --}}
    <div class="surat-container">
        @include('surat.partials.negosiasi', [
        'data' => $dataSurat['negosiasi'],
        'items' => $items,
        'sekolah' => $sekolah,
        'rekanan' => $rekanan
        ])
    </div>

    <div class="page-break"></div>

    {{-- ==================================================
    HALAMAN 3: SURAT PESANAN
    ================================================== --}}
    <div class="surat-container">
        @include('surat.partials.pesanan', [
        'data' => $dataSurat['pesanan'],
        'items' => $items,
        'sekolah' => $sekolah,
        'rekanan' => $rekanan
        ])
    </div>

    <div class="page-break"></div>

    {{-- ==================================================
    HALAMAN 4: BERITA ACARA
    ================================================== --}}
    <div class="surat-container">
        @include('surat.partials.berita_acara', [
        'data' => $dataSurat['pemeriksaan'], // Perhatikan key array controller
        'items' => $items,
        'sekolah' => $sekolah,
        'rekanan' => $rekanan
        ])
    </div>

</body>

</html>