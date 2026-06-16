<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Agenda Surat Keluar TW {{ $triwulan }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.5;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .uppercase {
            text-transform: uppercase;
        }

        /* KOP LAPORAN RESMI KEDINASAN */
        .kop-laporan {
            text-align: center;
            margin-bottom: 20px;
        }

        .kop-laporan h3 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .kop-laporan h2 {
            margin: 2px 0 5px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .kop-laporan p {
            margin: 0;
            font-size: 11px;
            font-weight: normal;
        }

        .garis-pembatas {
            border-bottom: 2px solid #000;
            border-top: 1px solid #000;
            height: 2px;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        /* PANEL STATISTIK / RESUME EKSEKUTIF */
        .summary-container {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-box {
            border: 1px solid #000;
            background-color: #fcfcfc;
            padding: 8px 12px;
            text-align: left;
        }

        .summary-title {
            font-size: 9px;
            color: #444;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* TABEL AGENDA RESMI */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            color: #000;
            padding: 8px 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            vertical-align: top;
        }

        .sub-info {
            font-size: 9px;
            color: #444;
            margin-top: 4px;
            font-style: italic;
        }

        /* FORMAT LEGALISASI / TANDA TANGAN (SIGNATURE) */
        .footer-legalisasi {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .footer-legalisasi table {
            width: 100%;
            border: none;
        }

        .footer-legalisasi td {
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
            padding: 0 15px;
        }

        .space-tanda-tangan {
            height: 75px;
            /* Jarak aman dan proporsional untuk tanda tangan fisik & stempel dinas */
        }

        .nama-pejabat {
            text-decoration: underline;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="kop-laporan">
        <h3>DAFTAR AGENDA REGISTRASI SURAT KELUAR</h3>
        <h2>{{ strtoupper($sekolah->nama_sekolah) }}</h2>
        <p>Periode: Triwulan {{ $triwulan }} — Tahun Anggaran {{ $tahun }}</p>
        <div class="garis-pembatas"></div>
    </div>


    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Nomor Surat</th>
                <th width="15%">Jenis Dokumen</th>
                <th>Uraian / Maksud Kegiatan Belanja</th>
                <th width="18%">Tujuan / Pihak Penerima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listSurat as $index => $surat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d/m/Y') }}
                </td>
                <td style="font-family: 'Courier New', Courier, monospace; font-size: 11px; font-weight: bold;">
                    {{ $surat->nomor_surat }}
                </td>
                <td>
                    <div style="font-weight: normal; text-align: justify;">
                        {{ $labelJenis[$surat->jenis_surat] ?? $surat->jenis_surat }}
                    </div>
                </td>
                <td>
                    <div style="font-weight: normal; text-align: justify;">
                        {{ $surat->belanja->uraian ?? ($surat->jenis_surat == 'NPD' ? 'Nota Permintaan Dana (NPD)
                        Triwulan ' . $triwulan . ' TA ' . $tahun : ($surat->jenis_surat == 'talangan' ? 'Pernyataan Dana
                        Talangan Triwulan ' . $triwulan . ' TA ' . $tahun : 'Sisa Tanda Setoran Triwulan ' . $triwulan .
                        ' TA ' . $tahun)) }}
                    </div>

                    @if(isset($surat->belanja->korek))
                    <div class="sub-info">
                        Kode Rekening: {{ $surat->belanja->korek->kode }} — {{ $surat->belanja->korek->uraian_singkat ??
                        $surat->belanja->korek->ket }}
                    </div>
                    @endif
                </td>
                <td>
                    @if($surat->belanja && $surat->belanja->rekanan)
                    {{ $surat->belanja->rekanan->nama_rekanan }}
                    @if($surat->belanja->rekanan->pimpinan)
                    <div style="font-size: 9px; color: #444;">u.p. {{ $surat->belanja->rekanan->pimpinan }}</div>
                    @endif
                    @else
                    {{ $sekolah->relasiSudin->nama ?? 'Suku Dinas Pendidikan' }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #444; font-style: italic;">
                    Belum ada riwayat registrasi surat keluar untuk periode Triwulan {{ $triwulan }} Tahun Anggaran {{
                    $tahun }}.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-legalisasi">
        <table width="100%">
            <tr>
                <td width="50%">
                    Mengetahui,<br>
                    Kepala {{ $sekolah->nama_sekolah }}<br>
                    <div class="space-tanda-tangan"></div>
                    <span class="nama-pejabat">{{ $sekolah->nama_kepala_sekolah }}</span><br>
                    NIP. {{ $sekolah->nip_kepala_sekolah }}
                </td>
                <td width="50%">
                    Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                    Bendahara Sekolah<br>
                    <div class="space-tanda-tangan"></div>
                    <span class="nama-pejabat">{{ $sekolah->nama_bendahara ?? $sekolah->nama_pengurus_barang
                        }}</span><br>
                    NIP. {{ $sekolah->nip_bendahara ?? $sekolah->nip_pengurus_barang }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>