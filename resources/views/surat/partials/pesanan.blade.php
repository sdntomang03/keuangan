<x-kop :sekolah="$sekolah" />

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
                            <td>Segera</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:top;">Lampiran</td>
                            <td style="vertical-align:top;">:</td>
                            <td>-</td>
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
                    <div style="margin-bottom: 20px;">
                        {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}
                    </div>
                    <div style="margin-bottom: 5px;">Kepada :</div>
                    <table style="width: 100%; border-collapse: collapse; margin-left: -35px;">
                        <tbody>
                            <tr>
                                <td style="width: 35px; vertical-align: top;">Yth.</td>
                                <td style="vertical-align: top;"><b>{{ $rekanan->nama_rekanan }}</b></td>
                            </tr>
                            <tr>

                                <td></td>
                                <td style="vertical-align: top;">{{ $rekanan->alamat }} </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="vertical-align: top;">{{ $rekanan->alamat2 }} </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="vertical-align: top; padding-top: 5px;">di</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="vertical-align: top; padding-left: 20px;">{{ $rekanan->provinsi }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<div style="padding-left: 95px; margin-top: 20px;">
    <p style="text-align: justify; text-indent: 48px; line-height: 1.6;">
        Berdasarkan kebutuhan sekolah yang tertuang pada Anggaran Biaya Operasional Pendidikan (BOP) Triwulan I Tahun
        Anggaran 2026 dengan Kode Rekening 5.1.02.01.01.0012 Belanja Bahan-Bahan Lainnya pada kegiatan Pembelian
        Pelengkapan UKS di SD NEGERI SUKA SUKA 05. Maka dengan ini kami bermaksud untuk melakukan pemesanan Barang/Jasa
        sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :
    </p>

    {{-- TABEL BARANG --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">No</th>
                <th>Kompomem Barang/Jasa</th>
                <th style="width: 15%; text-align: center;">Kuantitas</th>
                <th style="width: 15%; text-align: center;">Satuan</th>

            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-center">{{ $item->satuan }}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="text-align: justify; text-indent: 48px; line-height: 1.6;">
        Pentingnya Komponen Barang / Pekerjaan tersebut demi kelancaran pada kegiatan sekolah kami, maka diharapkan
        dapat di kirim dan kami terima paling lambat 5 (lima) hari kerja.


    </p>
    <div style="width: 100%; page-break-inside: avoid;">
        <p style="text-align: justify; text-indent: 48px; line-height: 1.6;">
            Demikian surat pesanan ini kami sampaikan, atas perhatian dan kerja sama yang baik, kami ucapkan terima
            kasih.


        </p>
        <div style="margin-top:20px; float: right; width: 300px; text-align: center;">
            <p style="margin-bottom: 60px;">Kepala {{ $sekolah->nama_sekolah }}</p>
            <p><b>{{ $kepala_sekolah->nama }}</b><br>NIP. {{ $kepala_sekolah->nip }}</p>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>