<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Agenda Surat Keluar TW {{ $triwulan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
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

        .header {
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .header h3 {
            margin: 0;
            font-size: 14px;
        }

        .header h2 {
            margin: 3px 0;
            font-size: 18px;
            color: #1a237e;
        }

        .header p {
            margin: 0;
            font-size: 11px;
            color: #555;
            font-weight: bold;
        }

        /* KARTU STATISTIK RINGKASAN */
        .summary-container {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-box {
            border: 1px solid #dcdcdc;
            background-color: #f8f9fa;
            padding: 6px 10px;
            border-radius: 4px;
            text-align: left;
        }

        .summary-title {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            margin-top: 2px;
        }

        /* TABEL UTAMA */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th {
            border: 1px solid #444;
            background-color: #e8eaf6;
            color: #1a237e;
            padding: 8px 5px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.data-table td {
            border: 1px solid #666;
            padding: 6px 6px;
            vertical-align: top;
        }

        .sub-info {
            font-size: 9px;
            color: #555;
            margin-top: 3px;
            font-style: italic;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            background-color: #eee;
            color: #333;
            border: 1px solid #ccc;
        }

        /* FOOTER SIGNATURE */
        .footer {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .footer table {
            width: 100%;
            border: none;
        }

        .footer td {
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header text-center">
        <h3>DAFTAR AGENDA REGISTRASI SURAT KELUAR</h3>
        <h2>{{ strtoupper($sekolah->nama_sekolah) }}</h2>
        <p>Triwulan {{ $triwulan }} — Tahun Anggaran {{ $tahun }}</p>
    </div>

    <table class="summary-container">
        <tr>
            <td width="20%" style="padding-right: 10px;">
                <div class="summary-box">
                    <div class="summary-title">Total Surat Keluar</div>
                    <div class="summary-value">{{ $totalSurat }} Dokumen</div>
                </div>
            </td>
            <td width="80%">
                <div class="summary-box" style="font-size: 10px; padding: 7px 12px;">
                    <span class="summary-title" style="display:block; margin-bottom:4px;">Rincian Berdasarkan Jenis
                        Dokumen:</span>
                    <div style="margin-top: 3px;">
                        @foreach($labelJenis as $key => $label)
                        @if(($statistik[$key] ?? 0) > 0)
                        <span style="margin-right: 15px; display: inline-block;">
                            <strong>{{ $key }}:</strong> {{ $statistik[$key] }} berkas
                        </span>
                        @endif
                        @endforeach
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal Surat</th>
                <th width="18%">Nomor Agenda Surat</th>
                <th width="18%">Jenis Dokumen</th>
                <th width="32%">Uraian & Deskripsi Kegiatan</th>
                <th width="18%">Tujuan / Pihak Rekanan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listSurat as $index => $surat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d M Y') }}
                </td>
                <td style="font-family: monospace; font-size: 11px; font-weight: bold;">
                    {{ $surat->nomor_surat }}
                </td>
                <td>
                    <span class="badge">{{ $surat->jenis_surat }}</span>
                    <div style="margin-top:3px; font-size:10px;">{{ $labelJenis[$surat->jenis_surat] ??
                        $surat->jenis_surat }}</div>
                </td>
                <td>
                    <div style="font-weight: 500;">
                        {{ $surat->belanja->uraian ?? ($surat->jenis_surat == 'NPD' ? 'Nota Permintaan Dana (NPD)
                        Triwulan ' . $triwulan . ' TA ' . $tahun : ($surat->jenis_surat == 'talangan' ? 'Pernyataan Dana
                        Talangan Triwulan ' . $triwulan . ' TA ' . $tahun : 'Sisa Tanda Setoran Triwulan ' . $triwulan .
                        ' TA ' . $tahun)) }}
                    </div>

                    @if(isset($surat->belanja->korek))
                    <div class="sub-info">
                        Kode Akun: {{ $surat->belanja->korek->kode }} — {{ $surat->belanja->korek->uraian_singkat ??
                        $surat->belanja->korek->ket }}
                    </div>
                    @endif
                </td>
                <td>
                    @if($surat->belanja && $surat->belanja->rekanan)
                    <strong>{{ $surat->belanja->rekanan->nama_rekanan }}</strong>
                    @if($surat->belanja->rekanan->pimpinan)
                    <div style="font-size: 9px; color: #555;">u.p. {{ $surat->belanja->rekanan->pimpinan }}</div>
                    @endif
                    @else
                    <strong>{{ $sekolah->sudin ?? 'Suku Dinas Pendidikan' }}</strong>
                    <div style="font-size: 9px; color: #777; font-style: italic;">Internal / Sekolah</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #777; font-style: italic;">
                    Belum ada riwayat surat keluar yang dibuat atau digenerate pada periode Triwulan {{ $triwulan }}
                    Tahun {{ $tahun }}.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="50%">
                    Mengetahui,<br>
                    Kepala Sekolah<br><br><br><br><br>
                    <strong style="text-decoration: underline;">{{ $sekolah->nama_kepala_sekolah }}</strong><br>
                    NIP. {{ $sekolah->nip_kepala_sekolah }}
                </td>
                <td width="50%">
                    Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                    Bendahara Sekolah<br><br><br><br><br>
                    <strong style="text-decoration: underline;">{{ $sekolah->nama_bendahara ??
                        $sekolah->nama_pengurus_barang }}</strong><br>
                    NIP. {{ $sekolah->nip_bendahara ?? $sekolah->nip_pengurus_barang }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>