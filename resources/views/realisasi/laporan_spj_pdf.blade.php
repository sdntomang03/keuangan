<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan SPJ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h3,
        .header h4 {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background-color: #e2e8f0;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="header">
        <h3>LAPORAN SURAT PERTANGGUNGJAWABAN (SPJ) DAN RINCIAN PAJAK</h3>
        <h4>{{ $sekolah->nama_sekolah }}</h4>
        <p>Anggaran: {{ strtoupper($anggaran->singkatan) }} Tahun {{ $anggaran->tahun }} | Triwulan: {{
            $sekolah->triwulan_aktif }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="8%">Tanggal</th>
                <th width="10%">No Bukti</th>
                <th width="15%">Rekanan</th>
                <th>Uraian</th>
                <th width="12%">Nilai SPJ (Bruto)</th>

                @foreach($pajakUnik as $pajak)
                <th width="9%">{{ $pajak }}</th>
                @endforeach

                <th width="12%">Nilai Bersih<br>(Netto)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mappedData as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td class="text-nowrap">{{ $row['no_bukti'] ?? '-' }}</td>
                <td>{{ $row['rekanan'] }}</td>
                <td>{{ $row['uraian'] }}</td>
                <td class="text-right">{{ number_format($row['bruto'], 0, ',', '.') }}</td>

                @foreach($pajakUnik as $pajak)
                <td class="text-right">{{ $row['pajak'][$pajak] > 0 ? number_format($row['pajak'][$pajak], 0, ',', '.')
                    : '-' }}</td>
                @endforeach

                <td class="text-right">{{ number_format($row['netto'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 6 + count($pajakUnik) }}" class="text-center">Tidak ada data transaksi.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right" style="font-weight: bold;">GRAND TOTAL</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($totals['bruto'], 0, ',', '.') }}
                </td>

                @foreach($pajakUnik as $pajak)
                <td class="text-right" style="font-weight: bold;">{{ number_format($totals['pajak'][$pajak], 0, ',',
                    '.') }}</td>
                @endforeach

                <td class="text-right" style="font-weight: bold;">{{ number_format($totals['netto'], 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>