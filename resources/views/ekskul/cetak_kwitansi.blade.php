<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kwitansi - {{ $spj->belanja->no_bukti }}</title>
    <style>
        /* =========================================
           CSS UNTUK TAMPILAN CETAK (PRINT FRIENDLY)
           ========================================= */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
            color: #000;
        }

        /* Container Kertas A4 (Setengah Halaman / A5 Landscape) */
        .page {
            width: 330mm;
            /* Lebar standar A4 */
            min-height: 140mm;

            /* PADDING: Atas 15mm, Kanan 30mm (Dilebarkan), Bawah 15mm, Kiri 20mm */
            padding: 15mm 35mm 15mm 20mm;

            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            /* Agar padding tidak menambah lebar total */
            border: 2px solid #040405;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
            text-decoration: underline;
        }

        .header p {
            margin: 2px 0;
            font-size: 9pt;
            font-weight: bold;
        }

        .no-bukti {
            position: absolute;
            top: 0;
            right: 0;
            /* Akan mengikuti padding kanan container .page */
            font-size: 9pt;
            border: 1px solid #000;
            padding: 2px 5px;
        }

        /* Tabel Konten Utama */
        .table-content {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table-content td {
            vertical-align: top;
            padding: 3px 0;
        }

        .label {
            width: 160px;
        }

        .sep {
            width: 15px;
            text-align: center;
        }

        .titik-titik {
            border-bottom: 1px dotted #000;

        }

        /* Kotak Terbilang */
        .terbilang-box {
            background-color: #eeeeee;
            padding: 8px 15px;
            font-style: italic;
            font-weight: bold;
            border: 2px solid #000;
            text-align: center;
            font-size: 12pt;
            margin: 5px 0;
            transform: skewX(-10deg);
        }

        .terbilang-text {
            transform: skewX(10deg);
            display: block;
        }

        /* Rincian Hitungan */
        .rincian-box {
            margin-left: 175px;
            padding: 5px;
            font-size: 10pt;
        }

        .table-rincian {
            border-collapse: collapse;
            width: 100%;
            /* Pastikan tabel rincian selebar container parent */
        }

        .table-rincian td {
            padding: 2px 5px;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .text-right {
            text-align: right;
        }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
            /* Jangan terpotong halaman */
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 33%;
        }

        .ttd-space {
            height: 65px;
        }

        .nip {
            font-size: 9pt;
            margin-top: 2px;
        }

        /* Tombol Cetak (Hanya layar) */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        /* @MEDIA PRINT */
        @media print {

            /* Atur Margin Kertas Printer agar tidak memotong konten */
            @page {
                size: auto;
                /* auto is the initial value */
                margin: 20mm;
                /* Margin tipis dari tepi kertas fisik */
            }

            body {
                background: none;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
            }

            .page {

                box-shadow: none;
                margin: 0;

                /* PENTING: Ubah width jadi 100% atau auto saat print agar fit di kertas */
                width: 100%;
                max-width: 100%;

                /* Pertahankan Padding Kanan Besar */
                padding-right: 35mm;
                padding-left: 15mm;
            }

            .no-print {
                display: none;
            }

            /* Pastikan background kotak terbilang tetap tercetak */
            .terbilang-box {
                background-color: #eeeeee !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            🖨️ Cetak Kwitansi
        </button>
        <button onclick="window.close()" class="btn-print" style="background-color: #6b7280; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="page">
        <div class="header">
            <div class="no-bukti">Nomor: {{ $spj->belanja->no_bukti }}</div>
            <h1>KWITANSI</h1>

        </div>

        <table class="table-content">
            <tr>
                <td class="label">Sudah Terima dari</td>
                <td class="sep">:</td>
                <td class="titik-titik">
                    {{ $sumberDana }}
                </td>
            </tr>
            <tr>
                <td class="label">Uang Sejumlah</td>
                <td class="sep">:</td>
                <td>
                    <div class="terbilang-box">
                        <span class="terbilang-text"># {{ $terbilang }} #</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td class="sep">:</td>
                <td class="titik-titik" style="line-height: 1.5;">
                    {{ strtoupper('Honorarium') }}<br>

                    <span style="font-weight: normal; font-size: 10pt;">
                        INSTRUKTUR {{ strtoupper($spj->ekskul->nama) }} di {{ strtoupper($sekolah->nama_sekolah) }}<br>

                        {{-- Menampilkan Kode Rekening --}}
                        Kode Rekening : {{ $spj->belanja->korek->ket ?? '-' }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="rincian-box">
            <div style="margin-bottom: 5px; font-weight: bold;">Dengan rincian:</div>
            <table class="table-rincian">
                <tr>
                    <td>Jumlah Honor dibayarkan</td>
                    <td>:</td>
                    <td>{{ $spj->jumlah_pertemuan }} Pertemuan</td>
                    <td>x</td>
                    <td>Rp {{ number_format($spj->honor, 0, ',', '.') }}</td>
                    <td>=</td>
                    <td class="text-right" style="width: 120px;">Rp {{ number_format($spj->total_honor, 0, ',', '.') }}
                    </td>
                </tr>

                <tr>
                    <td class="border-bottom">Potongan PPh. Pasal 21</td>
                    <td class="border-bottom">:</td>
                    <td class="border-bottom">50% x 5% </td>
                    <td class="border-bottom">x</td>
                    <td class="border-bottom">Rp {{ number_format($spj->total_honor, 0, ',', '.') }}</td>
                    <td class="border-bottom">=</td>
                    <td class="border-bottom text-right">Rp {{ number_format($spj->pph_nominal, 0, ',', '.') }}</td>
                </tr>

                <tr>
                    <td colspan="5" style="font-weight: bold; padding-top: 5px;">Jumlah Diterima</td>
                    <td style="font-weight: bold; padding-top: 5px;">=</td>
                    <td class="text-right" style="font-weight: bold; font-size: 11pt; padding-top: 5px;">Rp {{
                        number_format($spj->total_netto, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <table class="signature-table">
            <tr>
                <td>
                    <br>
                    Kepala Sekolah
                    <div class="ttd-space"></div>
                    <b><u>{{ $sekolah->nama_kepala_sekolah }}</u></b><br>
                    <span class="nip">NIP. {{ $sekolah->nip_kepala_sekolah }}</span>
                </td>

                <td>
                    <br>
                    Juru Bayar
                    <div class="ttd-space"></div>
                    <b><u>{{ $sekolah->nama_bendahara }}</u></b><br>
                    <span class="nip">NIP. {{ $sekolah->nip_bendahara }}</span>
                </td>

                <td>
                    Jakarta, {{ \Carbon\Carbon::parse($spj->belanja->tanggal)->translatedFormat('d F Y') }}<br>
                    Yang Menerima,
                    <div class="ttd-space"></div>
                    <b><u>{{ ucwords(strtolower($spj->rekanan->nama_rekanan)) }}</u></b><br>

                </td>
            </tr>
        </table>

        <div style="margin-top: 30px; font-weight: bold; border-top: 1px solid #ccc; padding-top: 5px;">
            {{ strtoupper($sekolah->nama_sekolah) }}
        </div>
    </div>

</body>

</html>
