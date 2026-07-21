<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi & Dokumentasi - {{ $spj->ekskul->nama ?? 'Ekstrakurikuler' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap"
        rel="stylesheet">

    <style>
        /* BASE STYLES & VARIABLES (Diadaptasi dari Desain Sebelumnya) */
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-page: #ffffff;
            --bg-body: #f1f5f9;
            --border-color: #cbd5e1;
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

        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-size: 12pt;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page {
            width: 215mm;
            min-height: 330mm;
            padding: 20mm;
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
            margin: 0;
        }

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
            padding: 4px 8px;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11pt;
            margin-bottom: 30px;
        }

        .rekap-table th,
        .rekap-table td {
            border: 1px solid var(--border-color);
            padding: 8px;
            vertical-align: top;
        }

        .rekap-table th {
            background-color: var(--primary);
            color: white;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }

        .signature-section {
            margin-top: auto;
            padding-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            text-align: center;
            font-size: 12pt;
        }

        .sign-role {
            margin-bottom: 80px;
            line-height: 1.4;
        }

        .sign-name {
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .sign-nip {
            color: var(--text-muted);
        }

        /* Gaya Khusus Grid Foto */
        .photo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .photo-item {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #f8fafc;
            page-break-inside: avoid;
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
            background: #e2e8f0;
        }

        .photo-caption {
            font-size: 10pt;
            font-weight: 600;
            color: var(--primary);
        }

        /* PRINT CONFIGURATION */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: 215mm 330mm;
                margin: 0;
            }

            .page {
                width: 100%;
                height: auto;
                min-height: 330mm;
                border-top: 8px solid var(--primary) !important;
                padding: 15mm;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .title-section h2 {
                background: var(--primary) !important;
                color: white !important;
            }

            .rekap-table th {
                background-color: var(--primary) !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
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

    {{-- HALAMAN 1: TABEL ABSENSI & MATERI --}}
    <div class="page">
        <div class="title-section">
            <h2>LAPORAN ABSENSI & MATERI EKSTRAKURIKULER <br> {{ $spj->ekskul->nama }}</h2>
        </div>

        <table class="info-table">
            <tr>
                <td style="width: 120px; font-weight: 600; color: var(--text-muted);">Nama Pelatih</td>
                <td style="width: 10px;">:</td>
                <td style="font-weight: bold; color: var(--primary);">{{ $spj->rekanan->nama_rekanan }}</td>
                <td style="width: 120px; font-weight: 600; color: var(--text-muted);">Triwulan / Tahun</td>
                <td style="width: 10px;">:</td>
                <td style="font-weight: bold;">
                    @php
                    $tgl = \Carbon\Carbon::parse($spj->belanja->tanggal);
                    $triwulan = match ($tgl->quarter) { 1=>'I', 2=>'II', 3=>'III', 4=>'IV', default=>'-' };
                    @endphp
                    {{ $triwulan }} / {{ $tgl->year }}
                </td>
            </tr>
        </table>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Hari / Tanggal</th>
                    <th style="width: 50%;">Uraian Materi Kegiatan</th>
                    <th style="width: 20%;">TTD Pelatih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($spj->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{
                        \Carbon\Carbon::parse($detail->tanggal_kegiatan)->locale('id')->translatedFormat('l, d F Y') }}
                    </td>
                    <td>{!! strip_tags($detail->materi) !!}</td>
                    <td></td> {{-- Kolom TTD Kosong untuk ditandatangani manual --}}
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">Belum ada data
                        pertemuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        <div class="sign-role">Mengetahui,<br>Kepala {{ $sekolah->nama_sekolah ?? 'Sekolah' }}</div>
                        <div class="sign-name">{{ $sekolah->nama_kepala_sekolah ?? 'Nama Kepala Sekolah' }}</div>
                        <div class="sign-nip">NIP. {{ $sekolah->nip_kepala_sekolah ?? '-' }}</div>
                    </td>
                    <td width="50%">
                        <div class="sign-role">Jakarta, {{
                            \Carbon\Carbon::parse($spj->belanja->tanggal)->translatedFormat('d F Y') }}<br>Pelatih /
                            Instruktur</div>
                        <div class="sign-name">{{ $spj->rekanan->nama_rekanan }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- HALAMAN 2 & SETERUSNYA: LAMPIRAN FOTO --}}
    @php
    // Saring hanya detail yang memiliki foto
    $photos = $spj->details->whereNotNull('foto_kegiatan');
    @endphp

    @if($photos->count() > 0)
    {{-- Bagi kumpulan foto menjadi kelompok dengan maksimal 2 foto per halaman --}}
    @foreach($photos->chunk(2) as $chunk)
    <div class="page">
        <div class="title-section">
            <h2>LAMPIRAN DOKUMENTASI <br> {{ $spj->ekskul->nama }}</h2>
        </div>

        <div class="photo-grid">
            @foreach($chunk as $index => $detail)
            <div class="photo-item">
                <img src="{{ asset('storage/' . $detail->foto_kegiatan) }}" alt="Foto Kegiatan">
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    @endif

</body>

</html>