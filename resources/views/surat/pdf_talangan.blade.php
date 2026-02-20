@extends('layouts.cetak')

@section('content')
{{-- Komponen Kop Surat --}}
<x-kop :sekolah="$sekolah" />

<style>
    .content {
        padding: 0 1cm;
        font-size: 12pt;
        line-height: 1.5;
    }

    .title {
        text-align: center;
        font-weight: bold;
        text-decoration: underline;
        font-size: 14pt;
        margin-bottom: 5px;
    }

    .nomor {
        text-align: center;
        margin-bottom: 20px;
    }

    .info-table {
        margin-left: 20px;
        border: none;
        width: 100%;
    }

    .info-table td {
        border: none;
        padding: 2px 5px;
        vertical-align: top;
    }

    .table-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .table-items th,
    .table-items td {
        border: 1px solid black;
        padding: 6px;
    }

    .table-items th {
        background-color: #f2f2f2;
        text-align: center;
    }

    .signature-table {
        width: 100%;
        margin-top: 30px;
        border: none;
    }

    .signature-table td {
        border: none;
        text-align: center;
        vertical-align: top;
        padding: 0;
    }

    .signature-table p {
        margin: 0;
        /* Menghilangkan margin bawaan p */
        line-height: 1;
    }



    .content p {
        text-align: justify;
        margin-bottom: 10px;
    }
</style>

<div class="content">
    <div class="title">SURAT PERNYATAAN DANA TALANGAN</div>
    <div class="nomor">Nomor: {{ $surat->nomor_surat ?? '......./UD.02.04' }}</div>

    <p>Yang bertanda tangan di bawah ini:</p>
    <table class="info-table">
        <tbody>
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: <b>{{ $sekolah->nama_kepala_sekolah ?? '......................' }}</b></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: {{ $sekolah->nip_kepala_sekolah ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: Kepala Sekolah</td>
            </tr>
            <tr>
                <td>Instansi</td>
                <td>: {{ strtoupper($sekolah->nama_sekolah ?? '......................') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: justify;">
        Sehubungan dengan adanya tagihan atas penggunaan Kode Rekening <b>{{ $referensi->korek->ket ??
            '......................' }}</b> pada bulan <b>{{ $rentangBulan ?? '..............' }}</b> Tahun <b>{{
            $anggaran->tahun ?? date('Y') }}</b>
        yang mana telah dianggarkan dalam RKAS Sekolah pada <b>{{ strtoupper($anggaran->singkatan ?? 'BOP/BOS') }}</b>
        Alokasi dasar Tahun <b>{{ $anggaran->tahun ?? date('Y') }}</b>
        yang akan disalurkan oleh <b>{{ $sekolah->Sudin?->nama ?? 'Suku Dinas Pendidikan Wilayah Kota Administrasi
            Setempat' }}</b> pada tiap Triwulan di akhir bulan dalam Triwulan tersebut, adapun jumlah tagihan tiap
        bulannya sebagai berikut ;
    </p>

    <table class="table-items" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">No</th>
                <th style="width: 40%; text-align: left;">ID Pelanggan / Keperluan</th>
                <th style="width: 25%; text-align: center;">Bulan</th>
                <th style="width: 25%; text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalTalangan = 0; @endphp
            @foreach($items as $index => $item)
            @php
            $totalItem = $item->qty * $item->harga_satuan;
            $totalTalangan += $totalItem;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $item->nama_barang }}</td>
                <td style="text-align: center;">{{ $item->bulan ?? '-' }}</td>
                <td style="text-align: right;">
                    {{ number_format($totalItem, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTAL</td>
                <td style="text-align: right; font-weight: bold; ">
                    {{ number_format($totalTalangan, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: justify;">
        karena kondisi tersebut diatas maka biaya tagihan atas pemakaian tersebut telah kami tangguhkan/ditalangi oleh:
    </p>

    <table class="info-table">
        <tbody>
            <tr>
                <td style="width: 150px;">Nama</td>
                <td>: <b>{{ $sekolah->nama_bendahara ?? '....................' }}</b></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: {{ $sekolah->nip_bendahara ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: Bendahara</td>
            </tr>
            <tr>
                <td>Bank / No. Rek</td>
                <td>: {{ $sekolah->bank_bendahara ?? 'Bank .......' }} / {{ $sekolah->no_rekening ?? '................'
                    }}
                </td>
            </tr>
            <tr>
                <td>Atas Nama</td>
                <td>: {{ $sekolah->nama_bendahara ?? '....................' }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: justify;">
        Apabila dana {{ strtoupper($anggaran->singkatan ?? 'BOP') }} Alokasi Dasar Tahun {{
        $anggaran->tahun ?? date('Y') }} Triwulan {{ $romawiTriwulan ?? 'I' }} telah disalurkan oleh
        <b>{{ $sekolah->Sudin?->nama ?? 'Suku Dinas Pendidikan Wilayah Kota Administrasi Setempat' }}</b>, maka kami
        akan transfer ke Nama yang telah menangguhkan/menalangi biaya penggunaan <b>{{ $referensi->korek->ket ??
            '......................' }}</b>.
    </p>
    <p style="text-align: justify;">
        Demikian surat pernyataan ini kami buat agar dapat digunakan sebagai kelengkapan administrasi dan dapat
        digunakan sebagaimana mestinya.
    </p>

    <table class="signature-table">
        <tbody>
            <tr>
                <td>
                    <p>&nbsp;</p>
                    <p>Kepala {{ $sekolah->nama_sekolah ?? 'Sekolah' }}</p>
                    <p style="margin-bottom: 50px;">&nbsp;</p>
                    <p><b>{{ $sekolah->nama_kepala_sekolah ?? '......................' }}</b></p>
                    <p>NIP. {{ $sekolah->nip_kepala_sekolah ?? '-' }}</p>
                </td>
                <td width="25%"></td>
                <td>
                    <p>Jakarta, {{ $surat->tanggal_surat_format ?? \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                    <p>Yang Menangguhkan,</p>
                    <p style="margin-bottom: 50px;">&nbsp;</p>
                    <p><b>{{ $sekolah->nama_bendahara ?? '....................' }}</b></p>
                    <p>NIP. {{ $sekolah->nip_bendahara ?? '-' }}</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection