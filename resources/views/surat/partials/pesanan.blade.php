@php
// Deteksi otomatis via Route
$is_parsial = request()->routeIs('*.cetakParsialPdf');
$is_penggandaan = $surat->is_penggandaan ?? false;
@endphp

<x-kop :sekolah="$sekolah" />

<table style="width:100%; margin-bottom:10px; border-collapse:collapse;">
    {{-- BAGIAN HEADER / METADATA SURAT --}}
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
                            <td>
                                {{ $surat->perihal }}

                            </td>
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
                                <td style="vertical-align: top; padding-top: 5px;">di</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="vertical-align: top; padding-left: 20px;">{{ $rekanan->provinsi ?? 'Tempat'
                                    }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<div style="padding-left: 95px; margin-top: 20px;">

    @if($is_parsial)
    {{-- PARAGRAF PEMBUKA (TAMPIL DI KEDUA KONDISI) --}}
    <p style="text-align: justify; text-indent: 48px; line-height: 1.6;">
        Berdasarkan kebutuhan sekolah yang tertuang pada Anggaran {{ $surat->anggaran->nama_anggaran }} ({{
        Str::upper($surat->anggaran->singkatan) }}) {{ $surat->periode }} Tahun
        Anggaran {{ $surat->anggaran->tahun }} dengan Kode Rekening {{ $surat->kode_rekening }} pada
        kegiatan {{ $surat->nama_kegiatan }} di {{ $sekolah->nama_sekolah }}. Maka dengan ini kami bermaksud untuk
        melakukan pemesanan Barang/Jasa sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :
    </p>
    @else
    <p style="text-align: justify; text-indent: 48px; line-height: 1.6;">
        Berdasarkan Surat Kesepakatan Negosiasi yang kami terima dari {{ $rekanan->nama_rekanan }}, serta berdasarkan
        Anggaran {{ $surat->anggaran->nama_anggaran }} ({{
        Str::upper($surat->anggaran->singkatan) }}) {{ $surat->periode }} Tahun Anggaran {{ $surat->anggaran->tahun }}
        dengan Kode Rekening {{ $surat->kode_rekening }} di {{ $sekolah->nama_sekolah }} pada kegiatan
        {{ $surat->nama_kegiatan }}. Maka dengan ini kami bermaksud untuk melakukan pemesanan Komponen
        Barang / Jasa yang kami perlukan sebagai berikut :
    </p>
    @endif

    {{-- TABEL BARANG (DINAMIS) --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">No</th>

                {{-- KONDISI 1: JIKA PARSIAL, TAMBAH HEADER TANGGAL --}}
                @if($is_parsial && !$is_penggandaan)
                <th style="width: 20%; text-align: center;">Tanggal Kirim</th>
                @endif

                <th>Komponen Barang/Jasa</th>
                <th style="width: 15%; text-align: center;">Kuantitas</th>
                <th style="width: 15%; text-align: center;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>

                {{-- KONDISI 2: JIKA PARSIAL, TAMPILKAN DATANYA --}}
                @if($is_parsial && !$is_penggandaan)
                <td class="text-center">{{ $item->tanggal_kirim ?? '-' }}
                </td>
                @endif


                <td>{{ $item->nama_barang }} @if($is_parsial && $is_penggandaan)
                    <br>
                    Keterangan: {{ $belanja->rincian }}
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->satuan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-align: justify; text-indent: 48px; line-height: 1.6; margin-bottom: 0px;">
        Pentingnya Komponen Barang / Pekerjaan tersebut demi kelancaran pada kegiatan sekolah kami, maka diharapkan
        dapat di kirim dan kami terima paling lambat 5 (lima) hari kerja.
    </p>

    <div style="width: 100%; page-break-inside: avoid;">
        <p style="text-align: justify; text-indent: 48px; line-height: 1.6; margin-top: 5px;">
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