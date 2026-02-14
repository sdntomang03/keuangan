<x-kop :sekolah="$sekolah" />

{{-- JUDUL SURAT --}}
{{-- JUDUL SURAT --}}
<div style="text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 2px; text-transform: uppercase;">
    BERITA ACARA PEMERIKSAAN BARANG/PEKERJAAN
</div>

{{-- NOMOR SURAT --}}
<div style="text-align: center; margin-bottom: 20px;">
    Nomor : {{ $surat->nomor_surat }}
</div>

{{-- PEMBUKA --}}
<p style="margin: 0 0 10px 0; line-height: 1.2;">
    Pada hari ini, {{ $surat->hari_ini ?? '-' }} tanggal {{ $surat->tanggal_terbilang ?? '-' }}, sesuai dengan :
</p>

<table style="width:100%; border-collapse:collapse; margin-bottom:10px; margin-left:10px;">
    <tbody>
        <tr>
            <td style="width:150px;">No. Faktur/Surat Jalan</td>
            <td style="width:10px;">:</td>
            <td>{{ $surat->no_bast ?? '-' }}</td>
        </tr>
        <tr>
            <td style="width:150px;">Tanggal</td>
            <td style="width:10px;">:</td>
            {{-- Menggunakan Carbon parse & translatedFormat --}}
            <td>{{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="width:150px;">Nama Pekerjaan</td>
            <td style="width:10px;">:</td>
            <td>{{ $surat->nama_pekerjaan }}</td>
        </tr>
        <tr>
            <td>Tahun</td>
            <td>:</td>
            <td>{{ $surat->anggaran->tahun }}</td>
        </tr>
    </tbody>
</table>

<p style="margin: 0 0 10px 0; line-height: 1.2;">Yang bertandatangan di bawah ini :</p>

{{-- PIHAK PERTAMA & KEDUA --}}
<div style="margin-left: 15px; margin-bottom: 15px;">

    {{-- 1. PIHAK PERTAMA (SEKOLAH) --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <tbody>
            <tr>
                <td style="width:30px; vertical-align:top; font-weight:bold;">1.</td>
                <td style="vertical-align:top;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:180px;">Nama</td>
                            <td style="width:10px;">:</td>
                            <td>{{ $pengurus_barang->nama }}</td>
                        </tr>
                        <tr>
                            <td>NIP</td>
                            <td>:</td>
                            <td>{{ $pengurus_barang->nip }}</td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>:</td>
                            <td>{{ $pengurus_barang->jabatan ?? 'Pengurus Barang' }}</td>
                        </tr>
                        <tr>
                            <td>Nama Instansi</td>
                            <td>:</td>
                            <td>{{ $sekolah->nama_sekolah }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td>{{ $sekolah->alamat }} Kel.{{ $sekolah->kelurahan }}, Kec. {{ $sekolah->kecamatan }}, {{
                                $sekolah->kota }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding-top:5px;">
                                Sebagai pihak yang <b>menerima hasil pekerjaan</b>, selanjutnya disebut <b>PIHAK
                                    PERTAMA</b>.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 2. PIHAK KEDUA (REKANAN) --}}
    <table style="width:100%; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="width:30px; vertical-align:top; font-weight:bold;">2.</td>
                <td style="vertical-align:top;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:180px;">Nama</td>
                            <td style="width:10px;">:</td>
                            <td>{{ $rekanan->nama_pimpinan }}</td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>:</td>
                            <td>{{ $rekanan->jabatan ?? 'Direktur' }}</td>
                        </tr>
                        <tr>
                            <td>Nama Perusahaan</td>
                            <td>:</td>
                            <td>{{ $rekanan->nama_rekanan }}</td>
                        </tr>
                        <tr>
                            <td>Alamat Perusahaan</td>
                            <td>:</td>
                            <td>{{ $rekanan->alamat }}, {{ $rekanan->alamat2 }}, {{ $rekanan->kota }}</td>
                        </tr>
                        <tr>
                            <td>No. Telepon</td>
                            <td>:</td>
                            <td>{{ $rekanan->no_telp }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding-top:5px;">
                                Sebagai pihak yang <b>menyerahkan hasil pekerjaan</b>, selanjutnya disebut <b>PIHAK
                                    KEDUA</b>.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ISI BERITA ACARA --}}
<p style="text-align: justify; margin-bottom: 15px; line-height: 1.2;">
    PIHAK KEDUA mengirim bukti hasil pengiriman barang/pekerjaan atas kegiatan {{ $surat->nama_kegiatan }} kepada
    PIHAK PERTAMA, dan PIHAK PERTAMA telah menerima hasil pengiriman barang/pekerjaan tersebut dalam jumlah yang lengkap
    dan kondisi yang baik sesuai dengan rincian berikut :
</p>

{{-- TABEL BARANG --}}
<table class="table-items">
    <thead>
        <tr>
            <th style="width:5%;">No</th>
            <th>Nama Barang/Jasa</th>
            <th style="width:10%;">Jumlah Dipesan</th>
            <th style="width:10%;">Satuan</th>
            <th style="width:10%;">Jumlah Diterima</th>
            <th style="width:10%;">Jumlah Tidak Sesuai</th>
            <th style="width:10%;">Jumlah Sesuai</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $item->nama_barang }}</td>
            {{-- Asumsi controller mengirim qty_pesan, qty_terima, dll --}}
            <td class="text-center">{{ $item->qty_pesan ?? $item->qty }}</td>
            <td class="text-center">{{ $item->satuan }}</td>
            <td class="text-center">{{ $item->qty_terima ?? $item->qty }}</td>
            <td class="text-center">{{ $item->qty_tolak ?? '-' }}</td>
            <td class="text-center">{{ $item->qty_sesuai ?? $item->qty }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- PENUTUP --}}
<p style="text-align: justify; text-indent: 48px; line-height: 1.6; margin-bottom: 0px;">
    Berita Acara Pemeriksaan Barang/Pekerjaan ini berfungsi sebagai bukti serah terima hasil pekerjaan kepada PIHAK
    PERTAMA.
</p>
{{-- BUNGKUS DENGAN DIV INI AGAR TIDAK TERPOTONG --}}
<div style="page-break-inside: avoid; break-inside: avoid;">

    <p style="text-align: justify; text-indent: 48px; line-height: 1.6; margin-top: 5px; margin-bottom: 15px;">
        Demikian Berita Acara Pemeriksaan Barang/Pekerjaan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana
        mestinya.
    </p>

    {{-- TANDA TANGAN --}}
    <table style="width:100%; border-collapse:collapse; margin-top: 20px;">
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
                    <p style="margin: 0;">{{ $rekanan->nama_rekanan }}</p>
                </td>
            </tr>
        </tbody>
    </table>

</div>
