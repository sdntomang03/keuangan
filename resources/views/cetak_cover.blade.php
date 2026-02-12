<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cover & Pembatas SPJ - {{ $data['nama_sekolah'] }}</title>
    <style>
        /* Pengaturan Kertas F4 (Folio) */
        @page {
            size: 215mm 330mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            -webkit-print-color-adjust: exact;
            /* Agar background image ikut ter-print */
            print-color-adjust: exact;
        }

        .page {
            width: 215mm;
            height: 330mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            /* Background Gambar Cover */
            background: url('https://i.imgur.com/xOLLuVv.png') no-repeat center center;
            background-size: 100% 100%;
        }

        /* --- Elemen Konten --- */

        /* Logo Bulat di Tengah Atas */
        .logo-container {
            position: absolute;
            top: 199px;
            left: 50%;
            transform: translateX(-50%);
            width: 49mm;
            height: 49mm;
            background: #64748b;
            border: 1.5mm solid #94a3b8;
            border-radius: 50%;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -20mm;
            overflow: hidden;
        }

        .logo-container img {
            width: 90%;
            /* Disesuaikan agar pas */
            height: 90%;
            object-fit: contain;
            z-index: 11;
        }

        /* Judul Utama (BOP, TAHUN, TW) */
        .main-title {
            position: absolute;
            top: 360px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 80%;
            z-index: 10;
            line-height: 1;
        }

        .main-title h1 {
            font-size: 48pt;
            margin: 0;
            color: #0041a8;
            /* Biru sesuai gambar */
            font-weight: 800;
        }

        .main-title h2 {
            font-size: 36pt;
            margin: 10mm 0 5mm;
            font-weight: 700;
            color: #333;
        }

        .main-title h3 {
            font-size: 30pt;
            margin: 0;
            font-weight: 700;
            color: #333;
        }

        /* Label Pembatas Kanan (Miring) */
        .divider-label {
            position: absolute;
            top: 810px;
            right: -50px;
            /* Offset agar terlihat keluar dari kertas jika perlu */
            left: 360px;
            bottom: auto;
            /* Direset dari contoh awal agar text flow bagus */
            background: transparent;
            color: #fff;
            padding: 10mm 15mm;
            font-size: 16pt;
            font-weight: bold;
            text-align: right;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            min-height: 25mm;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
        }

        /* Info Sekolah di Bawah Kiri */
        .school-info-box {
            position: absolute;
            bottom: 95px;
            left: 20px;
            width: 465px;
            height: 135px;
            background: transparent;
            padding: 5mm;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .school-info-box h4 {
            margin: 0 0 2mm;
            font-size: 24pt;
            font-weight: 800;
            color: #0041a8;
            text-transform: uppercase;
        }

        .school-info-box p {
            margin: 0;
            font-size: 18pt;
            /* Sedikit diperkecil agar alamat panjang muat */
            line-height: 1.4;
            color: #333;
        }

        /* Nomor Halaman / SPJ Pojok Kanan Bawah */
        .spj-number {
            position: absolute;
            bottom: 35px;
            right: 15px;
            font-size: 60pt;
            font-weight: 900;
            color: #fff;
            z-index: 10;
        }

        /* --- Styles Khusus Punggung Buku --- */
        .spine-page {
            background-image: url('https://i.imgur.com/7lL1PmM.png');
            background-size: 100% 100%;
        }

        .spine-content {
            position: absolute;
            top: 100px;
            left: 20px;
            width: 215px;
            text-align: center;
            color: #fff;
            z-index: 20;
        }

        .spine-label {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            color: #fff;
            opacity: 0.9;
        }

        .spine-value {
            font-size: 32pt;
            font-weight: 900;
            margin: 5px 0 0;
            color: #fff;
            line-height: 1;
            word-wrap: break-word;
        }

        .spine-h1 {
            font-size: 24pt;
            margin: 0 0 10px;
            color: #fff;
            font-weight: 800;
            line-height: 1;
        }

        .spine-h2 {
            font-size: 22pt;
            margin: 0 0 5px;
            font-weight: 700;
            color: #fff;
        }

        .spine-h3 {
            font-size: 18pt;
            margin: 0 0 15px;
            font-weight: 700;
            color: #fff;
        }

        .spine-school {
            font-size: 14pt;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.2;
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        }
    </style>
</head>

<body>

    {{-- LOOPING HALAMAN COVER & PEMBATAS --}}
    @foreach($halaman_pembatas as $index => $judul_pembatas)
    <div class="page">
        <div class="logo-container">
            <img src="{{ $data['logo_url'] }}" alt="Logo">
        </div>

        <div class="main-title">
            <h1>{{ $data['sumber_dana'] }}</h1>
            <h2>TAHUN {{ $data['tahun'] }}</h2>
            <h3>{{ $data['triwulan'] }}</h3>
        </div>

        <div class="divider-label">
            {!! $judul_pembatas !!}
        </div>

        <div class="school-info-box">
            <h4>{{ $data['nama_sekolah'] }}</h4>
            <p>{{ $data['alamat'] }}</p>
        </div>

        <div class="spj-number">
            {{-- {{ $index + 1 }} --}}
        </div>
    </div>
    @endforeach

    {{-- HALAMAN PUNGGUNG BUKU (HALAMAN TERAKHIR) --}}
    <div class="page spine-page">
        <div class="spine-content">
            <div style="margin-bottom: 20px;">
                <p class="spine-label">NOMOR SPJ</p>
                <p class="spine-value" style="font-size: 28pt;">{{ $data['nomor_spj'] }}</p>
            </div>

            <h1 class="spine-h1">{{ $data['sumber_dana'] }}</h1>

            <h2 class="spine-h2">TAHUN {{ $data['tahun'] }}</h2>

            <h3 class="spine-h3">{{ $data['triwulan'] }}</h3>

            <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                <h4 class="spine-school">
                    {{ $data['nama_sekolah'] }}
                </h4>
            </div>
        </div>
    </div>

</body>

</html>