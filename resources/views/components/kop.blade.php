{{-- 1. Berikan nilai default null pada props --}}
@props(['sekolah' => null])

@php
// 2. Logika Pengambilan Data Otomatis
// Jika variabel $sekolah kosong (tidak dikirim dari view),
// maka ambil dari relasi user yang sedang login.
if (!$sekolah && auth()->check()) {
$sekolah = auth()->user()->sekolah;
}
@endphp

{{-- 3. Cek apakah data sekolah ditemukan untuk mencegah error --}}
@if($sekolah)
<style>
    .kop-table {
        width: 100%;
        border-bottom: 3px solid black;
        padding-bottom: 10px;
        margin-bottom: 5px;
        border-collapse: collapse;
    }

    .kop-table td {
        vertical-align: top;
    }

    .kop-logo-cell {
        width: 80px;
        padding-top: 5px;
    }

    .kop-text-cell {
        text-align: center;
        padding: 0 10px;
    }

    .kop-spacer-cell {
        width: 40px;
    }

    .kop-text-cell h2 {
        margin: 0;
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;

        line-height: 1.2;
    }

    .kop-text-cell h3 {
        margin: 5px 0;
        font-size: 16pt;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .kop-text-cell p {
        margin: 0;
        font-size: 11pt;
        line-height: 1.4;
    }
</style>

<table class="kop-table">
    <tbody>
        <tr>
            <td class="kop-logo-cell">
                {{-- Pastikan path storage benar --}}
                <img src="{{ asset('storage/' . ($sekolah->logo ?? 'logo_jakarta.png')) }}" alt="Logo Sekolah"
                    style="width: 100px; height: auto;">
            </td>
            <td class="kop-text-cell">
                <h2>PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</h2>
                <h2>DINAS PENDIDIKAN</h2>
                <h3>{{ $sekolah->nama_sekolah }}</h3>
                <p>{{ $sekolah->alamat }} Kel. {{ $sekolah->kelurahan }} Kec. {{ $sekolah->kecamatan }}</p>
                <p>Telp: {{ $sekolah->telp ?? '-' }} | E-mail: {{ $sekolah->email ?? '-' }}</p>
                <p style="letter-spacing: 3px;">{{ strtoupper($sekolah->provinsi ?? 'JAKARTA') }}</p>
                <p style="text-align:right; font-size:10pt; margin-top:0px; margin-right: -50px;">
                    Kode Pos: {{ $sekolah->kodepos ?? '-' }}
                </p>
            </td>
            <td class="kop-spacer-cell"></td>
        </tr>
    </tbody>
</table>
@else
<div style="color: red; text-align: center; border: 1px solid red; padding: 10px;">
    <strong>Error:</strong> Data profil sekolah tidak ditemukan. Pastikan User sudah login dan memiliki data sekolah.
</div>
@endif