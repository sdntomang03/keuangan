@extends('layouts.cetak')

@section('content')
@php
$bulanNama = \Carbon\Carbon::createFromDate($laporan->tahun, $laporan->bulan, 1)->locale('id')->translatedFormat('F');
$rupiah = fn ($nilai) => (float) $nilai == 0.0 ? '-' : number_format($nilai, 0, ',', '.');
@endphp
<style>
    :root {
        --base-font: 10.5pt;
        --page-margin: 0.5cm;
    }

    body {
        font-size: var(--base-font);
        line-height: 1.4;
        padding: var(--page-margin) 1cm;
    }

    .text-center {
        text-align: center;
    }

    .text-bold {
        font-weight: bold;
    }

    .header-title {
        font-size: calc(var(--base-font) + 0.5pt);
        margin-bottom: 5px;
    }

    .data-header {
        margin: 15px 0;
    }

    .data-header table {
        width: 100%;
        border: 0;
    }

    .data-header td {
        vertical-align: top;
        padding: 1px 0;
    }

    .content-para {
        text-align: justify;
        margin-bottom: 10px;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 2px;
    }

    .calc-row span:last-child {
        text-align: right;
        white-space: nowrap;
    }

    .calc-indent {
        padding-left: 20px;
    }

    .calc-indent-2 {
        padding-left: 40px;
    }

    .sum-line {
        border-top: 1px solid #000;
        width: 250px;
        margin-left: auto;
        margin-top: 2px;
    }

    .sig-container {
        margin-top: 30px;
        float: right;
        width: 300px;
        text-align: center;
    }

    .footer-note {
        font-size: 8pt;
        margin-top: 30px;
        clear: both;
    }

    #sptjm-print-controls {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 10000;
        width: 220px;
        padding: 15px;
        border: 1px solid #7266ba;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
        font-family: Arial, sans-serif;
        font-size: 10pt;
    }

    #sptjm-print-controls .control-group {
        margin-bottom: 12px;
    }

    #sptjm-print-controls label {
        display: block;
        margin-bottom: 5px;
        color: #7266ba;
        font-size: 8pt;
        font-weight: bold;
    }

    #sptjm-print-controls input {
        width: 100%;
    }

    #sptjm-print-controls .btn-print-now {
        width: 100%;
        margin-top: 5px;
        padding: 8px 0;
        border: 0;
        border-radius: 4px;
        background: #27ae60;
        color: white;
        cursor: pointer;
        font-weight: bold;
    }

    #sptjm-print-controls.minimized {
        display: flex;
        width: 45px;
        height: 45px;
        padding: 0;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 50%;
        cursor: pointer;
    }

    #sptjm-print-controls.minimized .control-content,
    #sptjm-print-controls.minimized .header-text {
        display: none;
    }

    #sptjm-print-controls.minimized .toggle-btn {
        margin: 0;
    }

    #sptjm-print-controls .toggle-btn {
        float: right;
        cursor: pointer;
    }

    @media print {
        @page {
            size: A4;
            margin: var(--page-margin);
        }

        body {
            padding: 0.5cm 1cm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<div id="sptjm-print-controls" class="no-print">
    <div style="margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #7266ba; color: #7266ba; font-weight: bold"
        onclick="if (document.getElementById('sptjm-print-controls').classList.contains('minimized')) toggleSptjmControls()">
        <span class="header-text">PENGATURAN CETAK</span>
        <button type="button" class="toggle-btn" aria-label="Perkecil pengaturan cetak"
            onclick="event.stopPropagation(); toggleSptjmControls()">−</button>
    </div>
    <div class="control-content">
        <div class="control-group">
            <label>Ukuran Font (<span id="sptjm-font-value">10.5</span>pt)</label>
            <input type="range" min="8" max="12" step="0.5" value="10.5" oninput="updateSptjmPrint('font', this.value)">
        </div>
        <div class="control-group">
            <label>Margin Kertas (<span id="sptjm-margin-value">0.5</span>cm)</label>
            <input type="range" min="0.1" max="2" step="0.1" value="0.5"
                oninput="updateSptjmPrint('margin', this.value)">
        </div>
        <button type="button" class="btn-print-now" onclick="window.print()">CETAK SEKARANG</button>
    </div>
</div>
@if($monthlyTotals['belanja_belum_diatur'])
<p class="no-print" style="color: #b45309">
    Lengkapi jenis belanja pada data Korek sebelum mencetak laporan.
</p>
@endif

<div class="surat-container">
    <div class="text-center text-bold header-title">SURAT PERNYATAAN TANGGUNGJAWAB MUTLAK</div>
    <div class="text-center text-bold">NOMOR : {{ $laporan->nomor_surat ?: '........................................' }}
    </div>

    <div class="data-header">
        <table>
            <tr>
                <td style="width: 30px">1)</td>
                <td style="width: 220px">Nama Satuan Pendidikan</td>
                <td style="width: 10px">:</td>
                <td>{{ $sekolah->nama_sekolah }}</td>
            </tr>
            <tr>
                <td>2)</td>
                <td>Nomor Induk Satuan Pendidikan</td>
                <td>:</td>
                <td>{{ $sekolah->npsn ?: '................................' }}</td>
            </tr>
            <tr>
                <td>3)</td>
                <td>Sumber Dana BOS/BOP PAUD/BOP Kesetaraan*</td>
                <td>:</td>
                <td>DANA BOSP</td>
            </tr>
        </table>
    </div>

    <p class="content-para">
        Saya yang bertanda tangan di bawah ini menyatakan bahwa bertanggung jawab secara formal dan material atas
        kebenaran realisasi penerimaan dan pengeluaran Dana BOS/BOP PAUD/BOP Kesetaraan* serta kebenaran perhitungan dan
        setoran pajak yang telah dipungut atas penggunaan Dana BOS/BOP PAUD/BOP Kesetaraan* pada bulan <strong>{{
            $bulanNama }}</strong> tahun anggaran {{ $laporan->tahun }} dengan rincian sebagai berikut :
    </p>

    <div style="margin-top: 15px">
        <div class="calc-row"><span>A. Saldo Awal Dana BOS/BOP PAUD/BOP Kesetaraan*(pilih salah satu)</span><span>{{
                $rupiah($laporan->saldo_awal) }}</span></div>
        <div class="calc-row text-bold" style="margin-top: 10px"><span>B. Penerimaan Dana BOS/BOP PAUD/BOP
                Kesetaraan*</span></div>
        <div class="calc-row calc-indent"><span>1. Tahap I</span><span>{{ $rupiah($laporan->penerimaan_tahap_i)
                }}</span></div>
        <div class="calc-row calc-indent"><span>2. Tahap II</span><span>{{ $rupiah($laporan->penerimaan_tahap_ii)
                }}</span></div>
        <div class="calc-row calc-indent"><span>3. Kinerja</span><span>{{ $rupiah($laporan->penerimaan_kinerja)
                }}</span></div>
        <div class="calc-row calc-indent"><span>4. Bunga Bank / Pungutan Pajak / Silpa</span><span>{{
                $rupiah($laporan->penerimaan_lain) }}</span></div>
        <div class="sum-line"></div>
        <div class="calc-row calc-indent text-bold"><span>Jumlah Penerimaan</span><span>{{
                $rupiah($laporan->jumlah_penerimaan) }}</span></div>

        <div class="calc-row text-bold" style="margin-top: 10px"><span>C. Pengeluaran Dana BOS/BOP PAUD/BOP
                Kesetaraan*</span></div>
        <div class="calc-row calc-indent"><span>1. Belanja Barang dan Jasa</span><span>{{
                $rupiah($laporan->pengeluaran_barang_jasa) }}</span></div>
        <div class="calc-row calc-indent"><span>2. Belanja Modal Peralatan dan Mesin</span><span>{{
                $rupiah($laporan->pengeluaran_modal_peralatan) }}</span></div>
        <div class="calc-row calc-indent"><span>3. Belanja Modal Aset Tetap Lainnya</span><span>{{
                $rupiah($laporan->pengeluaran_modal_aset_lainnya) }}</span></div>
        <div class="calc-row calc-indent"><span>4. Biaya Bank / Setoran Pajak / Setoran Bunga</span><span>{{
                $rupiah($laporan->pengeluaran_lain) }}</span></div>
        <div class="sum-line"></div>
        <div class="calc-row calc-indent text-bold"><span>Jumlah Pengeluaran</span><span>{{
                $rupiah($laporan->jumlah_pengeluaran) }}</span></div>

        <div class="calc-row text-bold" style="margin-top: 10px"><span>D. Sisa Dana BOS/BOP PAUD/BOP
                Kesetaraan*</span><span>{{ $rupiah($laporan->sisa_dana) }} (A+B-C)</span></div>
        <div class="calc-row calc-indent"><span>Terdiri atas :</span></div>
        <div class="calc-row calc-indent-2"><span>1. Kas Tunai</span><span>{{ $rupiah($laporan->kas_tunai) }}</span>
        </div>
        <div class="calc-row calc-indent-2"><span>2. Rekening Bank</span><span>{{ $rupiah($laporan->rekening_bank)
                }}</span></div>
        <div class="calc-row text-bold" style="margin-top: 10px"><span>E. Penjelasan Selisih</span></div>
        <div style="min-height: 20px">{{ $laporan->penjelasan_selisih }}</div>
    </div>

    <p class="content-para" style="margin-top: 20px">
        Bukti-bukti atas belanja tersebut pada huruf B di atas disimpan pada Satuan Pendidikan <strong>{{
            $sekolah->nama_sekolah }}</strong> untuk kelengkapan administrasi dan keperluan pemeriksaan sesuai peraturan
        perundang-undangan.
    </p>
    <p class="content-para">
        Apabila bukti-bukti tersebut tidak benar yang mengakibatkan kerugian daerah, saya bertanggung jawab sepenuhnya
        atas kerugian daerah dimaksud sesuai kewenangan saya berdasarkan ketentuan peraturan perundang-undangan.
    </p>
    <p class="content-para">Demikian surat pernyataan ini dibuat dengan sebenarnya.</p>

    <div class="sig-container">
        {{ $laporan->kota_surat }}, {{ $laporan->tanggal_surat->locale('id')->translatedFormat('d F Y') }}<br>
        Kepala Satuan Pendidikan<br><br><br><br><br>
        <strong>{{ $sekolah->nama_kepala_sekolah }}</strong><br>
        NIP. {{ $sekolah->nip_kepala_sekolah }}
    </div>
    <div class="footer-note">* ) pilih salah satu dana</div>
</div>

<script>
    (() => {
        const key = 'print_config_sptjm';
        const root = document.documentElement;
        const update = (type, value, save = true) => {
            if (type === 'font') {
                root.style.setProperty('--base-font', `${value}pt`);
                document.getElementById('sptjm-font-value').textContent = value;
                document.querySelector('#sptjm-print-controls input[oninput*="font"]').value = value;
            } else {
                root.style.setProperty('--page-margin', `${value}cm`);
                document.getElementById('sptjm-margin-value').textContent = value;
                document.querySelector('#sptjm-print-controls input[oninput*="margin"]').value = value;
            }
            if (save) {
                const settings = JSON.parse(localStorage.getItem(key) || '{}');
                settings[type] = value;
                localStorage.setItem(key, JSON.stringify(settings));
            }
        };
        window.updateSptjmPrint = update;
        window.toggleSptjmControls = () => document.getElementById('sptjm-print-controls').classList.toggle('minimized');
        const settings = JSON.parse(localStorage.getItem(key) || '{}');
        if (settings.font) update('font', settings.font, false);
        if (settings.margin) update('margin', settings.margin, false);
    })();
</script>
@endsection