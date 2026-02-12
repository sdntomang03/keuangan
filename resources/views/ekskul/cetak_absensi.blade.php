<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Dokumentasi - {{ $spj->ekskul->nama }}</title>

    <style>
        /* ==========================
           BASE STYLES
           ========================== */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        /* TOMBOL PRINT */
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 50px;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ==========================
           LAYOUT HALAMAN (PORTRAIT F4)
           ========================== */
        .page {
            /* UBAH KE UKURAN F4 (215mm x 330mm) */
            width: 215mm;
            min-height: 330mm;
            padding: 10mm 20mm 10mm 20mm;
            /* Atas Bawah Kiri Kanan */
            margin: 0 auto 10px auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* ==========================
           LAYOUT HALAMAN (LANDSCAPE F4 - REKAP)
           ========================== */
        .page-landscape {
            /* UBAH KE UKURAN F4 LANDSCAPE (330mm x 215mm) */
            width: 330mm;
            min-height: 215mm;
            padding: 10mm 20mm 10mm 20mm;
            margin: 0 auto 10px auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            border-top: 10px solid #1e3a8a;
            display: flex;
            flex-direction: column;
        }

        /* ==========================
           KOMPONEN UMUM
           ========================== */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .logo-box img {
            width: 80px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            flex-grow: 1;
            padding: 0 15px;
        }

        .kop-pemprov {
            font-size: 11pt;
            font-weight: 600;
            color: #334155;
            line-height: 1.1;
        }

        .kop-dinas {
            font-size: 13pt;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.1;
        }

        .kop-alamat {
            font-size: 9pt;
            font-weight: 400;
            color: #64748b;
            line-height: 1.1;
        }

        .separator-line {
            height: 4px;
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 50%, #1e3a8a 100%);
            border-radius: 2px;
            margin-bottom: 15px;
        }

        /* CONTENT STYLES */
        .content-wrapper {
            flex-grow: 1;
        }

        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .title-section h2 {
            display: inline-block;
            background-color: #1e3a8a;
            color: white;
            padding: 6px 40px;
            border-radius: 50px;
            font-size: 14pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        /* TABEL INFO & REKAP */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11pt;
        }

        .info-table td {
            padding: 3px 3px;
            vertical-align: top;
        }

        /* Tabel Rekap (Bordered) */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }

        .rekap-table th,
        .rekap-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .rekap-table th {
            background-color: #e2e8f0;
            text-transform: uppercase;
            font-weight: 700;
            text-align: center;
        }

        .rekap-table td {
            color: #333;
        }

        .text-center {
            text-align: center;
        }

        /* FOTO & MATERI */
        .photo-container {
            width: 100%;
            /* Tinggi foto disesuaikan sedikit karena F4 lebih panjang */
            height: 520px;
            background-color: #f1f5f9;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background-color: #000;
        }

        .materi-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 5px solid #1e3a8a;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .materi-label {
            font-size: 10pt;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .materi-text {
            font-size: 11pt;
            color: #334155;
            line-height: 1.4;
            font-weight: 500;
        }

        /* TANDA TANGAN */
        .signature-section {
            margin-top: auto;
            padding-top: 5px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            text-align: center;
        }

        .sign-role {
            font-size: 11pt;
            color: #0f172a;
            margin-bottom: 60px;
        }

        .sign-name {
            font-size: 12pt;
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }

        .sign-nip {
            font-size: 10pt;
            color: #0f172a;
        }

        /* ==========================
           PRINT CONFIGURATION F4
           ========================== */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            /* 1. ATURAN HALAMAN PORTRAIT (DEFAULT F4) */
            @page {
                /* Dimensi F4 Portrait */
                size: 215mm 330mm;
                margin: 0;
            }

            /* 2. ATURAN KHUSUS HALAMAN LANDSCAPE (F4) */
            @page landscape-page {
                /* Dimensi F4 Landscape */
                size: 330mm 215mm;
                margin: 0;
            }

            /* 3. CLASS PEMICU LANDSCAPE */
            .page-landscape {
                page: landscape-page;
                width: 100%;
                /* Tinggi F4 Landscape */
                height: 210mm;
                break-before: page;
                page-break-before: always;
                border-top: none;
                padding: 10mm 15mm 10mm 15mm;
            }

            /* STYLE HALAMAN UMUM (PORTRAIT) */
            .page {
                width: 100%;
                height: 325mm;
                /* Sedikit kurang dari 330mm agar tidak bleed */
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 10mm 15mm 10mm 15mm;
                border-top: none;
                break-after: page;
                page-break-after: always;
            }

            /* Styling elemen cetak */
            .title-section h2 {
                background-color: #1e3a8a !important;
                color: white !important;
            }

            .materi-box {
                background-color: #f8fafc !important;
                border-color: #cbd5e1 !important;
            }

            .rekap-table th {
                background-color: #e2e8f0 !important;
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

    {{-- BAGIAN 1: HALAMAN DOKUMENTASI (PORTRAIT) - LOOPING --}}
    @forelse($spj->details as $index => $detail)
    <div class="page">
        <x-kop-surat :sekolah="$sekolah" />

        <div class="content-wrapper">
            <div class="title-section">
                <h2>Dokumentasi Pertemuan Ke-{{ $index + 1 }}</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label" style="width: 150px;">Hari / Tanggal</td>
                    <td style="width: 10px;">:</td>
                    <td style="font-weight: bold;">{{
                        \Carbon\Carbon::parse($detail->tanggal_kegiatan)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Pelatih</td>
                    <td>:</td>
                    <td>{{$spj->rekanan->nama_rekanan}}</td>
                </tr>
            </table>

            <div class="photo-container">
                @if($detail->foto_kegiatan)
                <img src="{{ asset('storage/' . $detail->foto_kegiatan) }}" alt="Dokumentasi">
                @else
                <div style="color: #999; font-style: italic;">Foto tidak tersedia</div>
                @endif
            </div>

            <div class="materi-box">
                <div class="materi-label">Uraian Kegiatan:</div>
                <div class="materi-text">{!! $detail->materi !!}</div>
            </div>
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="50%">
                        <div class="sign-role">Mengetahui,<br>Kepala Sekolah</div>
                        <div class="sign-name">{{ $sekolah->nama_kepala_sekolah }}</div>
                        <div class="sign-nip">NIP. {{ $sekolah->nip_kepala_sekolah }}</div>
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
    {{-- Jika data kosong --}}
    @endforelse


    {{-- BAGIAN 2: HALAMAN REKAP ABSENSI (LANDSCAPE) - HALAMAN TERAKHIR --}}
    <div class="page-landscape">

        <x-kop :sekolah="$sekolah" />

        <div class="content-wrapper">
            <div class="title-section">
                <h2>Rekapitulasi Kehadiran & Kegiatan</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Nama Kegiatan</td>
                    <td>:</td>
                    <td style="font-weight: bold;">{{ $spj->ekskul->nama }}</td>
                    <td class="label">Triwulan/tahun</td>
                    <td>:</td>
                    <td style="font-weight: bold;">
                        @php
                        $tgl = \Carbon\Carbon::parse($spj->belanja->tanggal);
                        $triwulan = match ($tgl->quarter) {
                        1 => 'I',
                        2 => 'II',
                        3 => 'III',
                        4 => 'IV',
                        default => '-'
                        };
                        @endphp
                        {{ $triwulan }} / {{ $tgl->year }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Nama Pelatih</td>
                    <td>:</td>
                    <td style="font-weight: bold;">{{ $spj->rekanan->nama_rekanan }}</td>
                    <td class="label">Jumlah Pertemuan</td>
                    <td>:</td>
                    <td style="font-weight: bold;">{{ $spj->details->count() }} Kali</td>
                </tr>
            </table>

            <table class="rekap-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Hari / Tanggal</th>
                        <th style="width: 45%;">Materi / Uraian Kegiatan</th>
                        <th style="width: 15%;">Dokumentasi</th>
                        <th style="width: 15%;">Paraf Pelatih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spj->details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($detail->tanggal_kegiatan)->locale('id')->translatedFormat('l, d F
                            Y') }}
                        </td>
                        <td>{!! $detail->materi !!}</td>
                        <td class="text-center">
                            @if($detail->foto_kegiatan) Ada @else - @endif
                        </td>
                        <td></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Data Kosong</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="33%">
                        <div class="sign-role">
                            Mengetahui,<br>
                            Kepala Sekolah
                        </div>
                        <div style="height: 10px;"></div>
                        <div class="sign-name">{{ $sekolah->nama_kepala_sekolah }}</div>
                        <div class="sign-nip">NIP. {{ $sekolah->nip_kepala_sekolah }}</div>
                    </td>
                    <td width="33%">
                    </td>
                    <td width="33%">
                        <div class="sign-role">
                            Jakarta, {{ \Carbon\Carbon::parse($spj->belanja->tanggal)->translatedFormat('d F Y') }}<br>
                            Pelatih / Instruktur
                        </div>
                        <div style="height: 10px;"></div>
                        <div class="sign-name">{{ $spj->rekanan->nama_rekanan }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>