<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Dokumen</title>
    <style>
        /* =========================================
           1. LOAD FONT ARIAL FISIK (WAJIB UTK DOMPDF)
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
           2. SETUP KERTAS & MARGIN AMAN
           ========================================= */
        @page {
            size: 215mm 330mm;
            /* Ukuran F4 */

            /* Margin: Atas 1.5cm, Kanan 2cm, Bawah 1.5cm, Kiri 2.5cm */
            /* Margin Kiri 2.5cm disarankan untuk area jilid/lubang kertas */
            margin: 1.5cm 1.5cm 1.5cm 2.5cm;
        }

        body {
            font-family: 'Arial', sans-serif;
            /* Panggil font custom di atas */
            font-size: 11pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }

        /* =========================================
           3. CSS TABEL ANTI-NABRAK KANAN
           ========================================= */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10pt;

            /* KUNCI PENTING: Agar tabel tidak melar melebihi kertas */
            table-layout: fixed;
            word-wrap: break-word;
        }

        .table-items th {
            border: 1px solid black;
            padding: 5px;
            background-color: #f0f0f0;

            /* AGAR TENGAH VERTIKAL & HORIZONTAL */
            vertical-align: middle;
            text-align: center;
        }

        .table-items td {
            border: 1px solid black;
            padding: 4px 5px;
            /* Padding sedikit dikecilkan biar muat banyak */
            vertical-align: middle;
            overflow: hidden;
            /* Potong teks jika memaksa keluar */
        }

        .table-items th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        /* =========================================
           4. UTILITIES
           ========================================= */
        /* Page Break */
        .page-break {
            page-break-after: always;
            clear: both;
        }

        /* Helper Text */
        .text-center {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-left {
            text-align: left !important;
        }

        .bold {
            font-weight: bold;
        }

        /* Container Surat (Agar padding aman) */
        .surat-container {
            width: 100%;
            /* Opsional: Tambah padding dalam jika teks terlalu mepet garis */
            /* padding-right: 5px; */
        }

        /* Sembunyikan saat print */
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    {{-- AREA KONTEN --}}
    @yield('content')

</body>

</html>