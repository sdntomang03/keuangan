@props(['sekolah'])

<style>
    /* CSS Khusus untuk Komponen KOP */
    .kop-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 3px solid #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
        width: 100%;
    }

    .logo-box {
        width: 85px;
        text-align: left;
    }

    .logo-box img {
        width: 80px;
        height: auto;
    }

    .kop-text {
        text-align: center;
        flex: 1;
        /* Mengisi ruang tengah */
        padding: 0 10px;
    }

    .kop-pemprov {
        font-size: 11pt;
        font-weight: bold;
        text-transform: uppercase;
    }

    .kop-dinas {
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;
    }

    .kop-sekolah {
        font-size: 18pt;
        font-weight: 800;
        text-transform: uppercase;
        margin: 2px 0;
        line-height: 1.1;
    }

    .kop-alamat {
        font-size: 9pt;

    }
</style>

<div class="kop-container">
    {{-- LOGO --}}
    <div class="logo-box">
        @if(!empty($sekolah->logo))
        <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah">
        @else
        {{-- Pastikan path logo default benar --}}
        <img src="{{ asset('storage/logo_jakarta.png') }}" alt="Logo DKI">
        @endif
    </div>

    {{-- TEKS TENGAH --}}
    <div class="kop-text">
        <div class="kop-pemprov">PEMERINTAH PROVINSI DKI JAKARTA</div>
        <div class="kop-dinas">DINAS PENDIDIKAN</div>
        <div class="kop-sekolah">{{ $sekolah->nama_sekolah }}</div>
        <div class="kop-alamat">
            {{ $sekolah->alamat ?? 'Alamat belum diisi' }}
            @if(!empty($sekolah->kelurahan))
            , Kel. {{ $sekolah->kelurahan }}
            @endif
            @if(!empty($sekolah->kecamatan))
            , Kec. {{ $sekolah->kecamatan }}
            @endif
            <br>
            @if(!empty($sekolah->telp))
            Telepon: {{ $sekolah->telp }}
            @endif
            @if(!empty($sekolah->email))
            E-mail: {{ $sekolah->email }}
            @endif
            <br>
            JAKARTA
        </div>
    </div>

    {{-- Penyeimbang Kanan (Agar teks benar-benar di tengah) --}}
    <div style="width: 85px;"></div>
</div>
