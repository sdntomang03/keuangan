<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BA Pemeriksaan Barang - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            size: 215mm 330mm;
            /* F4 */
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
            margin-top: 5px;
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

        .signature-table td {
            vertical-align: top;
        }
    </style>
</head>

<body>

    {{-- PANGGIL KOMPONEN KOP --}}
    <x-kop :sekolah="$sekolah" />

    {{-- JUDUL SURAT --}}
    <div style="text-align:center; font-weight:bold; font-size:12pt; margin-bottom:2px;">
        BERITA ACARA PEMERIKSAAN BARANG/PEKERJAAN
    </div>
    <div style="text-align:center; margin-bottom:10px;">
        Nomor : {{ $surat->nomor_surat }}
    </div>

    {{-- PEMBUKA --}}
    <p style="margin: 0 0 10px 0; line-height: 1.6;">
        Pada hari ini, {{ $hari_ini }} Tanggal {{ $tanggal_terbilang }}, sesuai dengan :
    </p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:10px; margin-left:10px;">
        <tbody>
            <tr>
                <td style="width:150px;">No. Faktur/Surat Jalan</td>
                <td style="width:10px;">:</td>
                <td>{{ $belanja->no_bast}}</td>
            </tr>
            <tr>
                <td style="width:150px;">Nama Pekerjaan</td>
                <td style="width:10px;">:</td>
                <td>{{ $surat->nama_pekerjaan }}</td>
            </tr>
            <tr>
                <td>Tahun</td>
                <td>:</td>
                <td>{{ $surat->tahun_anggaran }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin: 0 0 10px 0; line-height: 1.6;">Yang bertandatangan di bawah ini :</p>

    {{-- PIHAK PERTAMA --}}
    <div style="margin-left: 15px; margin-bottom: 15px;">
        <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
            <tbody>
                <tr>
                    <td style="width:30px; vertical-align:top; font-weight:bold;">1.</td>
                    <td style="vertical-align:top;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tbody>
                                <tr>
                                    <td style="width:180px; vertical-align:top;">Nama</td>
                                    <td style="width:20px; vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $pengurus_barang->nama }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">NIP</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $pengurus_barang->nip }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Jabatan</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $pengurus_barang->jabatan ?? 'Pengurus Barang
                                        Sekolah' }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Nama Instansi</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $sekolah->nama_sekolah }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Alamat</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $sekolah->alamat_singkat ?? 'Jl. Bahagia selalu
                                        No 7' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="vertical-align:top; padding-top:5px;">
                                        Sebagai pihak yang <b>menerima hasil pekerjaan</b>, selanjutnya disebut <b>PIHAK
                                            PERTAMA</b>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- PIHAK KEDUA --}}
        <table style="width:100%; border-collapse:collapse;">
            <tbody>
                <tr>
                    <td style="width:30px; vertical-align:top; font-weight:bold;">2.</td>
                    <td style="vertical-align:top;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tbody>
                                <tr>
                                    <td style="width:180px; vertical-align:top;">Nama</td>
                                    <td style="width:20px; vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $rekanan->nama_pimpinan }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Jabatan</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $rekanan->jabatan ?? 'Direktur' }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Nama Perusahaan</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $rekanan->nama_perusahaan }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">Alamat Perusahaan</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $rekanan->alamat }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">No. Telepon</td>
                                    <td style="vertical-align:top;">:</td>
                                    <td style="vertical-align:top;">{{ $rekanan->no_telp }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="vertical-align:top; padding-top:5px;">
                                        Sebagai pihak yang <b>menyerahkan hasil pekerjaan</b>, selanjutnya disebut
                                        <b>PIHAK KEDUA</b>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ISI BERITA ACARA --}}
    <p style="text-align: justify; margin-bottom: 15px; line-height: 1.6;">
        PIHAK KEDUA mengirim bukti hasil pengiriman barang/pekerjaan atas kegiatan {{ $surat->nama_kegiatan }} kepada
        PIHAK PERTAMA, dan PIHAK PERTAMA telah menerima hasil pengiriman barang/pekerjaan tersebut dalam jumlah yang
        lengkap
        dan kondisi yang baik sesuai dengan rincian berikut :
    </p>

    {{-- TABEL BARANG --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Nama Barang/Jasa</th>
                <th style="width:60px;">Jumlah Dipesan</th>
                <th style="width:60px;">Satuan</th>
                <th style="width:60px;">Jumlah Diterima</th>
                <th style="width:60px;">Jumlah Tidak Sesuai</th>
                <th style="width:60px;">Jumlah Sesuai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="text-align:center;">{{ $loop->iteration }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td style="text-align:center;">{{ $item->qty_pesan }}</td>
                <td style="text-align:center;">{{ $item->satuan }}</td>
                <td style="text-align:center;">{{ $item->qty_terima }}</td>
                <td style="text-align:center;">{{ $item->qty_tolak > 0 ? $item->qty_tolak : '-' }}</td>
                <td style="text-align:center;">{{ $item->qty_sesuai }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PENUTUP --}}
    <p style="margin-top: 15px; margin-bottom: 15px; line-height: 1.6;">
        Berita Acara Pemeriksaan Barang/Pekerjaan ini berfungsi sebagai bukti serah terima hasil pekerjaan kepada PIHAK
        PERTAMA.
    </p>
    <p style="margin-bottom: 15px; line-height: 1.6;">
        Demikian Berita Acara Pemeriksaan Barang/Pekerjaan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana
        mestinya.
    </p>

    {{-- TANDA TANGAN --}}
    <table style="width:100%; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="width:50%; text-align:center; vertical-align:top;">
                    <p style="margin: 0 0 60px 0;">Pihak Pertama</p>
                    <p style="margin: 0;"><b>{{ $pengurus_barang->nama }}</b></p>
                    <p style="margin: 0;">NIP {{ $pengurus_barang->nip }}</p>
                </td>
                <td style="width:50%; text-align:center; vertical-align:top;">
                    <p style="margin: 0 0 60px 0;">Pihak Kedua</p>
                    <p style="margin: 0;"><b>{{ $rekanan->nama_pimpinan }}</b></p>
                    <p style="margin: 0;">{{ $rekanan->nama_perusahaan }}</p>
                </td>
            </tr>
        </tbody>
    </table>

    <script>
        // window.print();
    </script>
</body>

</html>