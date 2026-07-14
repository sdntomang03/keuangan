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

        /* Mencegah teks turun ke bawah (memaksa ke samping) */
        .text-nowrap {
            white-space: nowrap;
        }

        /* Pengaturan Lebar Kolom Tetap (Fixed Width) */
        .col-no {
            width: 20px;
        }

        .col-tgl {
            width: 55px;
        }

        .col-bukti {
            width: 80px;
        }

        .col-rekanan {
            width: 110px;
        }

        .col-uang {
            width: 75px;
        }

        .col-pajak {
            width: 70px;
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
                <th class="col-no">No</th>
                <th class="col-tgl">Tanggal</th>
                <th class="col-bukti">No Bukti</th>
                <th class="col-rekanan">Rekanan</th>

                {{-- Kolom Uraian sengaja TIDAK DIBERI CLASS WIDTH agar otomatis mengambil sisa ruang kertas
                seluas-luasnya --}}
                <th>Uraian</th>

                <th class="col-uang">Nilai SPJ<br>(Bruto)</th>

                @foreach($pajakUnik as $pajak)
                <th class="col-pajak">Potongan<br>{{ $pajak }}</th>
                @endforeach

                <th class="col-uang">Nilai Bersih<br>(Netto)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mappedData as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center text-nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td class="text-nowrap">
                    <strong>{{ $row['no_bukti'] ?? '-' }}</strong><br>
                    <span style="font-size: 8px; color: #555; text-transform: uppercase;">{{ $row['korek'] }}</span>
                </td>
                <td>{{ $row['rekanan'] }}</td>

                {{-- Uraian akan turun ke bawah secara natural jika teksnya sangat panjang --}}
                <td>{{ $row['uraian'] }}</td>

                {{-- Tambahkan text-nowrap pada angka agar format uang "Rp xxx.xxx" tidak terpisah/turun ke bawah --}}
                <td class="text-right text-nowrap">{{ number_format($row['bruto'], 0, ',', '.') }}</td>

                @foreach($pajakUnik as $pajak)
                <td class="text-right text-nowrap">{{ $row['pajak'][$pajak] > 0 ? number_format($row['pajak'][$pajak],
                    0, ',', '.') : '-' }}</td>
                @endforeach

                <td class="text-right text-nowrap">{{ number_format($row['netto'], 0, ',', '.') }}</td>
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
                <td class="text-right text-nowrap" style="font-weight: bold;">{{ number_format($totals['bruto'], 0, ',',
                    '.') }}</td>

                @foreach($pajakUnik as $pajak)
                <td class="text-right text-nowrap" style="font-weight: bold;">{{ number_format($totals['pajak'][$pajak],
                    0, ',', '.') }}</td>
                @endforeach

                <td class="text-right text-nowrap" style="font-weight: bold;">{{ number_format($totals['netto'], 0, ',',
                    '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>