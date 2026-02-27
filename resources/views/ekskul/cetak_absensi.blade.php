<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Dokumentasi - {{ $spj->ekskul->nama ?? 'Ekstrakurikuler' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    <style>
        /* ==========================
            BASE STYLES & VARIABLES
           ========================== */
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-page: #ffffff;
            --bg-body: #f1f5f9;
            --border-color: #cbd5e1;
            /* Update Font Variable */
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-main);
            font-size: 12pt;
            color: var(--text-dark);
            background-color: var(--bg-body);
            margin: 0;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        /* TOMBOL PRINT */
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-family: var(--font-main);
            font-size: 12pt;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-print:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(37, 99, 235, 0.5);
        }

        /* ==========================
            LAYOUT HALAMAN (PORTRAIT F4)
           ========================== */
        .page {
            width: 215mm;
            min-height: 330mm;
            padding: 20mm 20mm;
            margin: 0 auto 20px auto;
            background: var(--bg-page);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            border-top: 8px solid var(--primary);
            border-radius: 8px;
        }

        /* ==========================
            LAYOUT HALAMAN (LANDSCAPE F4)
           ========================== */
        .page-landscape {
            width: 330mm;
            min-height: 215mm;
            padding: 20mm 20mm;
            margin: 0 auto 20px auto;
            background: var(--bg-page);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            border-top: 8px solid var(--primary);
            display: flex;
            flex-direction: column;
            border-radius: 8px;
        }

        .content-wrapper {
            flex-grow: 1;
        }

        .title-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .title-section h2 {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), #2a4365);
            color: white;
            padding: 10px 40px;
            border-radius: 8px;
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1.5;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* TABEL INFO */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 10px;
            border: 1px solid var(--border-color);
        }

        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .info-table td.label {
            color: var(--text-muted);
            font-weight: 600;
        }

        /* TABEL REKAP (LANDSCAPE) */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11pt;
        }

        .rekap-table th,
        .rekap-table td {
            border: 1px solid var(--border-color);
            padding: 5px;
            vertical-align: middle;
        }

        .rekap-table th {
            background-color: var(--primary);
            color: white;
            text-transform: uppercase;
            font-weight: 700;
            text-align: center;
            font-size: 11pt;
            letter-spacing: 0.5px;
        }

        .rekap-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        /* FOTO & MATERI */
        .photo-container {
            width: 100%;
            height: 480px;
            background-color: #e2e8f0;
            border: 3px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background-color: #f1f5f9;
        }

        .materi-box {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-left: 6px solid var(--primary-light);
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
        }

        .materi-label {
            font-size: 11pt;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 5px;
            display: inline-block;
        }

        .materi-text {
            font-size: 10.5pt;
            color: var(--text-dark);
            line-height: 1.2;
            text-align: justify;
        }

        /* TANDA TANGAN */
        .signature-section {
            margin-top: auto;
            padding-top: 20px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            text-align: center;
            font-size: 12pt;
            font-family: var(--font-main);
        }

        .sign-role {
            color: var(--text-dark);
            margin-bottom: 70px;
            line-height: 1.4;
        }

        .sign-name {
            font-size: 12pt;
            font-weight: 700;
            text-decoration: underline;
            color: var(--text-dark);
            margin-bottom: 3px;
        }

        .sign-nip {
            font-size: 12pt;
            color: var(--text-muted);
        }

        /* PRINT CONFIGURATION */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: 215mm 330mm;
                margin: 0;
            }

            @page landscape-page {
                size: 330mm 215mm;
                margin: 0;
            }

            .page,
            .page-landscape {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .page {
                width: 100%;
                height: 325mm;
                border-top: 8px solid var(--primary) !important;
                padding: 15mm 15mm;
                break-after: page;
                page-break-after: always;
            }

            .page-landscape {
                page: landscape-page;
                width: 100%;
                height: 210mm;
                break-before: page;
                page-break-before: always;
                border-top: 8px solid var(--primary) !important;
                padding: 15mm 15mm;
            }

            .title-section h2 {
                background: var(--primary) !important;
                color: white !important;
                box-shadow: none !important;
            }

            .rekap-table th {
                background-color: var(--primary) !important;
                color: white !important;
            }
        }
    </style>
</head>

<body>

    <button onclick="window.print()" class="btn-print no-print">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
            </path>
        </svg>
        Cetak Dokumen
    </button>

    {{-- BAGIAN 1: HALAMAN DOKUMENTASI (PORTRAIT) --}}
    @forelse($spj->details as $index => $detail)
    <div class="page">
        <div class="content-wrapper">
            <div class="title-section">
                <h2>DOKUMENTASI EKSTRAKURIKULER {{ $spj->ekskul->nama }}<br>PERTEMUAN KE-{{ $index + 1 }}</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label" style="width: 130px;">Hari / Tanggal</td>
                    <td style="width: 10px;">:</td>
                    <td style="font-weight: bold;">{{
                        \Carbon\Carbon::parse($detail->tanggal_kegiatan)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Pelatih</td>
                    <td>:</td>
                    <td style="font-weight: bold; color: var(--primary);">{{ $spj->rekanan->nama_rekanan }}</td>
                </tr>
            </table>

            <div class="photo-container">
                @if($detail->foto_kegiatan)
                <img src="{{ asset('storage/' . $detail->foto_kegiatan) }}" alt="Dokumentasi">
                @else
                <div style="color: var(--text-muted); font-style: italic; font-size: 12pt;">📸 Foto dokumentasi tidak
                    tersedia</div>
                @endif
            </div>

            <div class="materi-box">
                <div class="materi-label">Uraian / Materi Kegiatan:</div>
                <div class="materi-text">{!! $detail->materi !!}</div>
            </div>
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        <div class="sign-role">Mengetahui,<br>Kepala {{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}
                        </div>
                        <div class="sign-name">{{ $sekolah->nama_kepala_sekolah ?? 'Nama Kepala Sekolah' }}</div>
                        <div class="sign-nip">NIP. {{ $sekolah->nip_kepala_sekolah ?? '-' }}</div>
                    </td>
                    <td width="50%">
                        <div class="sign-role">Pelatih / Instruktur</div>
                        <div class="sign-name">{{ $spj->rekanan->nama_rekanan }}</div>

                    </td>
                </tr>
            </table>
        </div>
    </div>
    @empty
    <div class="page">
        <h3 class="text-center" style="margin-top: 50px; color: var(--text-muted);">Data Dokumentasi Kosong</h3>
    </div>
    @endforelse

    {{-- BAGIAN 2: HALAMAN REKAP ABSENSI (LANDSCAPE) --}}
    @if($spj->details->count() > 0)
    <div class="page">
        <div class="content-wrapper">
            <div class="title-section">
                <h2>REKAPITULASI KEGIATAN & KEHADIRAN <br> EKSTRAKURIKULER {{ $spj->ekskul->nama }}</h2>
            </div>

            <table class="info-table" style="background-color: transparent; border: none; margin-bottom: 5px;">
                <tr>
                    <td class="label" style="width: 120px;">Ekskul</td>
                    <td style="width: 10px;">:</td>
                    <td style="font-weight: bold; width: 33%;">{{ $spj->ekskul->nama }}</td>
                    <td class="label" style="width: 160px;">Triwulan / Tahun</td>
                    <td style="width: 10px;">:</td>
                    <td style="font-weight: bold;">
                        @php
                        $tgl = \Carbon\Carbon::parse($spj->belanja->tanggal);
                        $triwulan = match ($tgl->quarter) { 1=>'I', 2=>'II', 3=>'III', 4=>'IV', default=>'-' };
                        @endphp
                        {{ $triwulan }} / {{ $tgl->year }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Nama Pelatih</td>
                    <td>:</td>
                    <td style="font-weight: bold; color: var(--primary);">{{ $spj->rekanan->nama_rekanan }}</td>
                    <td class="label">Jumlah Pertemuan</td>
                    <td>:</td>
                    <td style="font-weight: bold;">{{ $spj->details->count() }} Pertemuan</td>
                </tr>
            </table>

            <table class="rekap-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Hari / Tanggal</th>
                        <th style="width: 45%;">Materi / Uraian Kegiatan</th>
                        <th style="width: 15%;">Dokumentasi</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($spj->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{
                            \Carbon\Carbon::parse($detail->tanggal_kegiatan)->locale('id')->translatedFormat('l, d F Y')
                            }}</td>
                        <td>{!! strip_tags($detail->materi) !!}</td>
                        <td class="text-center">@if($detail->foto_kegiatan) <span
                                style="color: green; font-weight: bold;">✓ Ada</span> @else <span
                                style="color: red;">-</span> @endif</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="45%">
                        <div class="sign-role">Mengetahui,<br>Kepala {{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}
                        </div>
                        <div class="sign-name">{{ $sekolah->nama_kepala_sekolah ?? 'Nama Kepala Sekolah' }}</div>
                        <div class="sign-nip">NIP. {{ $sekolah->nip_kepala_sekolah ?? '-' }}</div>
                    </td>
                    <td width="10%"></td>
                    <td width="45%">
                        <div class="sign-role">Jakarta, {{
                            \Carbon\Carbon::parse($spj->belanja->tanggal)->translatedFormat('d F Y') }}<br>Pelatih /
                            Instruktur</div>
                        <div class="sign-name">{{ $spj->rekanan->nama_rekanan }}</div>

                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

</body>

</html>
