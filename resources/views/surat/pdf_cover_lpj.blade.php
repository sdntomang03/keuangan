<!DOCTYPE html>
<html>

<head>
    <title>Cover & Pembatas SPJ</title>
    <style>
        @page {
            size: 215mm 330mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }

        .page {
            width: 215mm;
            height: 330mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            background-size: 100% 100%;
        }

        /* Content Elements */
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
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 11;
        }

        .main-title {
            position: absolute;
            top: 360px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 80%;
            z-index: 10;
            line-height: 0.5;
        }

        .main-title h1 {
            font-size: 48pt;
            margin: 0;
            color: #0041a8;
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

        .divider-label {
            position: absolute;
            top: 810px;
            right: -50px;
            left: 360px;
            bottom: 500px;
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
            height: 25mm;
        }

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
            margin: 0 0 5mm;
            font-size: 24pt;
            font-weight: 800;
            color: #0041a8;
            margin-top: 12px;
        }

        .school-info-box p {
            margin: 0;
            font-size: 20pt;
            line-height: 1.2;
            color: #333;
        }

        .spj-number {
            position: absolute;
            bottom: 35px;
            right: 25px;
            font-size: 60pt;
            font-weight: 900;
            color: #fff;
            z-index: 10;
        }
    </style>
</head>

<body>

    @php
    $romawiMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
    $romawiTriwulan = $romawiMap[(int) $triwulanAktif] ?? $triwulanAktif;
    @endphp

    {{-- LOOPING HALAMAN COVER --}}
    @foreach($rekeningTerpilih as $rekening)
    <div class="page">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/backgroundspj.png'))) }}"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover;">
        @if($logoBase64)
        <div class="logo-container">
            <img src="{{ $logoBase64 }}" alt="Logo">
        </div>
        @endif

        <div class="main-title">
            <h1>{{ $jenisBantuan }}</h1>
            <h2>TAHUN {{ $tahun }}</h2>
            <h3>TRIWULAN {{ $romawiTriwulan }}</h3>
        </div>

        <div class="divider-label">
            {{ $rekening }}
        </div>

        <div class="school-info-box">
            <h4>{{ strtoupper($sekolah->nama_sekolah) }}</h4>
            <p>{{ $sekolah->alamat}}</p>
            <p>Kel. {{ $sekolah->kelurahan }}, Kec. {{ $sekolah->kecamatan }}</p>
            <p>Kota {{ $sekolah->kota }}</p>
        </div>

        <div class=" spj-number">
            {{ str_pad($nomorSpj, 2, '0', STR_PAD_LEFT) }}
        </div>
    </div>
    @endforeach

    {{-- HALAMAN PUNGGUNG BINDER --}}
    @php
    // Cek dan convert gambar binder.png ke Base64
    $binderPath = public_path('images/binder.png');
    $binderBase64 = '';

    if (file_exists($binderPath)) {
    $binderBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($binderPath));
    }
    @endphp

    {{-- HALAMAN PUNGGUNG BINDER --}}
    <div class="page" style="position: relative; page-break-after: auto;">

        @if($binderBase64 != '')
        <img src="{{ $binderBase64 }}"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover;">
        @endif
        <div
            style="position: absolute; top: 100px; left: 20px; width: 215px; text-align: center; color: #fff; z-index: 20;">
            <div style="margin-bottom: 20px;">
                <p style="font-size: 14pt; font-weight: bold; margin: 0; color: #fff; opacity: 0.9;">NOMOR SPJ</p>
                <p style="font-size: 42pt; font-weight: 900; margin: 5px 0 0; color: #fff; line-height: 1;">
                    {{ str_pad($nomorSpj, 2, '0', STR_PAD_LEFT) }}
                </p>
            </div>

            <h1 style="font-size: 24pt; margin: 0 0 10px; color: #fff; font-weight: 800; line-height: 1;">{{
                $jenisBantuan }}</h1>

            <h2 style="font-size: 22pt; margin: 0 0 5px; font-weight: 700; color: #fff;">TAHUN {{ $tahun }}</h2>
            <h3 style="font-size: 18pt; margin: 0 0 15px; font-weight: 700; color: #fff;">TRIWULAN {{ $romawiTriwulan }}
            </h3>

            <div style="padding-top: -10px; width: 100%; display: flex; flex-direction: column; align-items: center;">
                <h4
                    style="font-size: 14pt; font-weight: 800; color: #fff; text-transform: uppercase; margin: 0; line-height: 1.2; text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;">
                    {!! nl2br(e(wordwrap(strtoupper($sekolah->nama_sekolah), 12, "\n"))) !!}
                </h4>
            </div>
        </div>
    </div>

</body>

</html>
