<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan RKAS - {{ $anggaran->nama_anggaran ?? 'Anggaran' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2,
        .header h3 {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Pewarnaan hierarki */
        .row-kegiatan {
            background-color: #d9e1f2;
            font-weight: bold;
        }

        .row-keterangan {
            background-color: #fff2cc;
            font-weight: bold;
            font-style: italic;
        }

        .row-rekening {
            background-color: #e2efda;
            font-weight: bold;
        }

        .row-komponen {
            background-color: #fff;
        }

        /* Indentasi berjenjang */
        .indent-1 {
            padding-left: 20px !important;
        }

        .indent-2 {
            padding-left: 35px !important;
        }

        .indent-3 {
            padding-left: 50px !important;
        }

        @media print {
            body {
                padding: 0;
            }

            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }

            th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }

            .row-kegiatan {
                background-color: #d9e1f2 !important;
                -webkit-print-color-adjust: exact;
            }

            .row-keterangan {
                background-color: #fff2cc !important;
                -webkit-print-color-adjust: exact;
            }

            .row-rekening {
                background-color: #e2efda !important;
                -webkit-print-color-adjust: exact;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h2>RENCANA KEGIATAN DAN ANGGARAN SEKOLAH (RKAS)</h2>
        <h3>TAHUN ANGGARAN {{ $anggaran->tahun ?? date('Y') }}</h3>
        <p style="margin-top: 5px; text-transform: uppercase;">{{ $anggaran->nama_anggaran ?? 'TIDAK
            DIKETAHUI' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>

                <th width="37%">Uraian / Keterangan</th>
                <th width="8%">Vol</th>
                <th width="8%">Satuan</th>
                <th width="13%">Harga Satuan</th>
                <th width="15%">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($laporan as $keg)
            {{-- LEVEL 1: KEGIATAN --}}
            <tr class="row-kegiatan">
                <td class="text-center">{{ $no++ }}</td>

                <td colspan="4">Kegiatan: {{ $keg->nama_kegiatan }}</td>
                <td class="text-right font-bold">{{ number_format($keg->total_kegiatan, 0, ',', '.') }}</td>
            </tr>

            @foreach ($keg->keterangan_list as $ket)
            {{-- LEVEL 2: KETERANGAN (Tabel RKAS) --}}
            <tr class="row-keterangan">
                <td></td>
                <td class="indent-1" colspan="4">{{ $ket->nama_keterangan }}</td>
                <td class="text-right">{{ number_format($ket->total_keterangan, 0, ',', '.') }}</td>
            </tr>

            @foreach ($ket->rekening as $rek)
            {{-- LEVEL 3: KODE REKENING --}}
            <tr class="row-rekening">
                <td></td>

                <td class="indent-2" colspan="4">Belanja: {{ $rek->nama_rekening }}</td>
                <td class="text-right">{{ number_format($rek->total_rekening, 0, ',', '.') }}</td>
            </tr>

            {{-- LEVEL 4: KOMPONEN (Rincian) --}}
            @foreach ($rek->komponen as $komp)
            <tr class="row-komponen">
                <td></td>

                <td class="indent-3">
                    {{ $komp->namakomponen }}
                    @if(!empty($komp->spek)) <br><small><i>Spek: {{ $komp->spek }}</i></small> @endif
                </td>
                <td class="text-center">{{ $komp->koefisien ?? 1 }}</td>
                <td class="text-center">{{ $komp->satuan }}</td>
                <td class="text-right">{{ number_format($komp->hargasatuan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($komp->totalharga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endforeach
            @endforeach
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data RKAS untuk anggaran ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="5" class="text-right" style="padding-right: 15px;">TOTAL KESELURUHAN</td>
                <td class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>