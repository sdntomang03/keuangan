<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Negosiasi Harga - {{ $surat->nomor_surat }}</title>
    <style>
        /* CSS Halaman Utama (Tanpa CSS Kop Surat lagi) */
        @page {
            size: 215mm 330mm;
            margin: 1cm 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.2;
            color: black;
            background: white;
            margin: 0;
            padding: 0;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10pt;
        }

        .table-items th,
        .table-items td {
            border: 1px solid black;
            padding: 5px;
        }

        .table-items th {
            background: #f0f0f0;
        }
    </style>
</head>

<body>

    {{-- PANGGIL KOMPONEN KOP --}}
    <x-kop :sekolah="$sekolah" />

    {{-- HEADER SURAT & TUJUAN --}}
    <table style="width:100%; margin-bottom:10px; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="width:55%; vertical-align:top;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody>
                            <tr>
                                <td style="width:85px; vertical-align:top;">Nomor</td>
                                <td style="width:10px; vertical-align:top;">:</td>
                                <td>{{ $surat->nomor_surat }}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top;">Sifat</td>
                                <td style="vertical-align:top;">:</td>
                                <td>{{ $surat->sifat ?? 'Segera' }}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top;">Lampiran</td>
                                <td style="vertical-align:top;">:</td>
                                <td>{{ $surat->lampiran ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top;">Perihal</td>
                                <td style="vertical-align:top;">:</td>
                                <td>{{ $surat->perihal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width:45%; vertical-align:top; text-align:right;">
                    <div style="text-align: left; display: inline-block; width: 85%;">
                        <div style="margin-bottom: 20px;">{{
                            \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</div>
                        <div style="margin-bottom: 5px;">Kepada :</div>
                        <table style="width: 100%; border-collapse: collapse; margin-left: -35px;">
                            <tbody>
                                <tr>
                                    <td style="width: 35px; vertical-align: top;">Yth.</td>
                                    <td style="vertical-align: top;"><b>{{ $rekanan->nama_perusahaan }}</b></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td style="vertical-align: top;">{{ $rekanan->alamat }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td style="vertical-align: top; padding-top: 5px;">di</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td style="vertical-align: top; padding-left: 20px;">{{ $rekanan->kota ?? 'Jakarta'
                                        }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ISI SURAT --}}
    <div style="padding-left: 95px; margin-top: 20px;">
        <p style="text-align: justify; margin: 0 0 15px 0; text-indent: 48px; line-height: 1.6;">
            Berdasarkan Surat Penawaran yang kami terima dari {{ $rekanan->nama_perusahaan }}, serta berdasarkan
            Anggaran yang kami miliki pada Kode Rekening {{ $surat->kode_rekening }} {{ $surat->nama_rekening }} pada
            kegiatan {{ $surat->nama_kegiatan }} di {{ $sekolah->nama_sekolah }} Tahun Anggaran {{
            $surat->tahun_anggaran }}.
            Maka dengan ini kami mengajukan negosiasi harga sesuai dengan Komponen Barang / Jasa yang kami perlukan
            sebagai berikut :
        </p>

        {{-- TABEL BARANG --}}
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th>Nama Barang/Jasa</th>
                    <th style="width: 10%; text-align: center;">Kuantitas</th>
                    <th style="width: 10%; text-align: center;">Satuan</th>
                    <th style="width: 15%; text-align: center;">Harga Penawaran</th>
                    <th style="width: 15%; text-align: center;">Harga Negosiasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">{{ $item->satuan }}</td>
                    <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td> {{-- Adjust
                    variable if needed --}}
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top:10px; text-indent: 48px; line-height: 1.6;">
            Demikian surat permohonan negosiasi harga ini kami sampaikan, atas perhatian dan kerja sama yang baik kami
            ucapkan terima kasih.
        </p>

        {{-- TANDA TANGAN --}}
        <div style="margin-top:-5px; float: right; width: 300px; text-align: center;">
            <p style="margin-bottom: 60px;">Kepala {{ $sekolah->nama_sekolah }}</p>
            <p><b>{{ $kepala_sekolah->nama }}</b><br>NIP. {{ $kepala_sekolah->nip }}</p>
        </div>
        <div style="clear:both;"></div>
    </div>

    <script>
        // window.print(); // Uncomment jika ingin otomatis print saat dibuka
    </script>
</body>

</html>
