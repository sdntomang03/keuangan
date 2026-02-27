<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
        }

        th {
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 30px;
        }

        .footer table {
            border: none;
        }

        .footer td {
            border: none;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header text-center">
        <h3 style="margin:0">DAFTAR AGENDA SURAT KELUAR</h3>
        <h2 style="margin:5px 0">{{ strtoupper($sekolah->nama_sekolah) }}</h2>
        <p style="margin:0">Triwulan {{ $triwulan }} Tahun Anggaran {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="11%">Tanggal</th>
                <th width="12%">Nomor Surat</th>
                <th width="22%">Jenis Dokumen</th>
                <th>Uraian Kegiatan</th>
                <th width="20%">Tujuan / Rekanan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listSurat as $index => $surat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d/m/Y') }}
                </td>
                <td>{{ $surat->nomor_surat }}</td>
                <td>{{ $labelJenis[$surat->jenis_surat] ?? $surat->jenis_surat }}</td>
                <td>{{ $surat->belanja->uraian ?? ($surat->jenis_surat == 'NPD' ? 'NPD Triwulan ' . $triwulan . ' Tahun
                    '
                    . $tahun : ($surat->jenis_surat ==
                    'talangan' ? 'Talangan Triwulan ' . $triwulan . ' Tahun
                    '
                    . $tahun : 'Sisa Tanda Setoran Triwulan ' . $triwulan . ' Tahun ' . $tahun)) }}</td>
                <td>{{ $surat->belanja->rekanan->nama_rekanan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada surat yang dibuat/generate pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="50%">
                    Mengetahui,<br>Kepala Sekolah<br><br><br><br>
                    <strong>{{ $sekolah->nama_kepala_sekolah }}</strong><br>
                    NIP. {{ $sekolah->nip_kepala_sekolah }}
                </td>
                <td width="50%">
                    Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                    Bendahara<br><br><br><br>
                    <strong>{{ $sekolah->nama_bendahara ?? $sekolah->nama_pengurus_barang }}</strong><br>
                    NIP. {{ $sekolah->nip_bendahara ?? $sekolah->nip_pengurus_barang }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
