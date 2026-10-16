@extends('layouts.cetak')

@section('content')
@php
    $rupiah = fn ($nilai) => $nilai === null || (float) $nilai == 0.0
        ? '-'
        : number_format($nilai, 0, ',', '.');
    $previousMonth = $laporan->bulan > 1
        ? \Carbon\Carbon::createFromDate($laporan->tahun, $laporan->bulan - 1, 1)->locale('id')->translatedFormat('F')
        : 'Bulan Sebelumnya';
    $columns = [
        'budget' => 'Jumlah Anggaran',
        'previous' => 'Realisasi s.d. '.$previousMonth,
        'current' => 'Realisasi Bulan '.$realisasi['month_label'],
        'cumulative' => 'Jumlah Realisasi s.d. '.$realisasi['month_label'],
        'remaining' => 'Selisih',
    ];
@endphp

<style>
    :root { --base-font: 9pt; --table-padding: 2px; --page-margin: 0.5cm; }
    @page { size: A4 portrait; margin: var(--page-margin); }
    body { font-family: Arial, sans-serif; font-size: var(--base-font); line-height: 1.2; padding: var(--page-margin); }
    .report-title { margin: 0 0 10px; text-align: center; font-size: calc(var(--base-font) + 2pt); font-weight: bold; }
    .report-intro { margin-bottom: 8px; }
    .report-month { margin-bottom: 3px; text-align: right; font-size: 10pt; font-weight: bold; text-transform: uppercase; }
    .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: calc(var(--base-font) - 1pt); }
    .report-table th, .report-table td { border: 1px solid #000; padding: var(--table-padding) 4px; overflow-wrap: anywhere; }
    .report-table th { background: #f2f2f2; text-align: center; vertical-align: middle; }
    .report-table .amount { text-align: right; white-space: nowrap; }
    .report-table .bold { font-weight: bold; }
    .report-table .indent-1 { padding-left: 14px; }
    .report-table .indent-2 { padding-left: 25px; }
    .report-table .indent-3 { padding-left: 36px; }
    .report-footer { margin-top: 10px; text-align: justify; font-size: 8pt; }
    .signature { float: right; width: 250px; margin-top: 12px; text-align: center; font-size: 9pt; }
    #realisasi-print-controls {
        position: fixed; top: 10px; left: 10px; z-index: 10000; width: 220px;
        padding: 15px; border: 1px solid #7266ba; border-radius: 8px; background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,.15); font-family: Arial, sans-serif; font-size: 10pt;
    }
    #realisasi-print-controls .control-group { margin-bottom: 12px; }
    #realisasi-print-controls label { display: block; margin-bottom: 5px; color: #7266ba; font-size: 8pt; font-weight: bold; }
    #realisasi-print-controls input { width: 100%; }
    #realisasi-print-controls .btn-print-now { width: 100%; margin-top: 5px; padding: 8px 0; border: 0; border-radius: 4px; background: #27ae60; color: white; cursor: pointer; font-weight: bold; }
    #realisasi-print-controls.minimized { display: flex; width: 45px; height: 45px; padding: 0; align-items: center; justify-content: center; overflow: hidden; border-radius: 50%; cursor: pointer; }
    #realisasi-print-controls.minimized .control-content, #realisasi-print-controls.minimized .header-text { display: none; }
    #realisasi-print-controls .toggle-btn { float: right; cursor: pointer; }
    @media print {
        body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display: none !important; }
    }
</style>

<div id="realisasi-print-controls" class="no-print">
    <div style="margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #7266ba; color: #7266ba; font-weight: bold"
        onclick="if (document.getElementById('realisasi-print-controls').classList.contains('minimized')) toggleRealisasiControls()">
        <span class="header-text">PENGATURAN CETAK</span>
        <button type="button" class="toggle-btn" aria-label="Perkecil pengaturan cetak"
            onclick="event.stopPropagation(); toggleRealisasiControls()">−</button>
    </div>
    <div class="control-content">
        <div class="control-group">
            <label>Ukuran Font (<span id="realisasi-font-value">9</span>pt)</label>
            <input type="range" min="7" max="12" step="0.5" value="9" oninput="updateRealisasiPrint('font', this.value)">
        </div>
        <div class="control-group">
            <label>Kerapatan Baris (<span id="realisasi-pad-value">2</span>px)</label>
            <input type="range" min="0" max="10" step="1" value="2" oninput="updateRealisasiPrint('pad', this.value)">
        </div>
        <div class="control-group">
            <label>Margin Kertas (<span id="realisasi-margin-value">0.5</span>cm)</label>
            <input type="range" min="0.1" max="2" step="0.1" value="0.5" oninput="updateRealisasiPrint('margin', this.value)">
        </div>
        <button type="button" class="btn-print-now" onclick="window.print()">CETAK SEKARANG</button>
    </div>
</div>

<div class="report-title">LAPORAN REALISASI PENERIMAAN DAN PENGELUARAN DANA BOSP</div>
<div class="report-intro">
    Bersama ini saya laporkan realisasi atas penerimaan dan pengeluaran Dana BOSP<br>
    untuk semester {{ $realisasi['semester'] }} tahun {{ $laporan->tahun }} sebagai berikut:
</div>
<div class="report-month">BULAN : {{ $realisasi['month_label'] }}</div>

<table class="report-table">
    <colgroup>
        <col style="width: 4%">
        <col style="width: 29%">
        <col style="width: 13%">
        <col style="width: 13%">
        <col style="width: 13%">
        <col style="width: 15%">
        <col style="width: 13%">
    </colgroup>
    <thead>
        <tr>
            <th>No.</th>
            <th>Uraian</th>
            @foreach($columns as $title)
            <th>{{ $title }}</th>
            @endforeach
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6 = 4+5</th>
            <th>7 = 3-6</th>
        </tr>
    </thead>
    <tbody>
        <tr class="bold"><td>I</td><td colspan="6">Penerimaan</td></tr>
        @foreach($realisasi['income_rows'] as $row)
        <tr>
            <td></td>
            <td class="indent-1">{{ $row['label'] }}</td>
            @foreach($columns as $key => $title)
            <td class="amount">{{ $rupiah($row[$key]) }}</td>
            @endforeach
        </tr>
        @endforeach
        <tr class="bold">
            <td></td><td style="text-align: right">Jumlah</td>
            @foreach($columns as $key => $title)
            <td class="amount">{{ $rupiah($realisasi['income_total'][$key]) }}</td>
            @endforeach
        </tr>

        <tr class="bold"><td>II</td><td colspan="6">Pengeluaran</td></tr>
        <tr class="bold"><td></td><td colspan="6" class="indent-1">a. Belanja Barang dan Jasa</td></tr>
        @foreach(['operasional'] as $category)
            @foreach(['reguler', 'kinerja'] as $source)
            <tr>
                <td></td>
                <td class="indent-2">{{ $realisasi['expense_rows'][$category][$source]['label'] }}</td>
                @foreach($columns as $key => $title)
                <td class="amount">{{ $rupiah($realisasi['expense_rows'][$category][$source][$key]) }}</td>
                @endforeach
            </tr>
            @endforeach
        @endforeach

        <tr class="bold"><td></td><td colspan="6" class="indent-1">b. Belanja Modal</td></tr>
        @foreach(['mesin' => '1)', 'aset lainnya' => '2)'] as $category => $number)
        <tr class="bold"><td></td><td colspan="6" class="indent-2">{{ $number }} {{ $realisasi['expense_rows'][$category]['label'] }}</td></tr>
            @foreach(['reguler', 'kinerja'] as $source)
            <tr>
                <td></td>
                <td class="indent-3">{{ $realisasi['expense_rows'][$category][$source]['label'] }}</td>
                @foreach($columns as $key => $title)
                <td class="amount">{{ $rupiah($realisasi['expense_rows'][$category][$source][$key]) }}</td>
                @endforeach
            </tr>
            @endforeach
        @endforeach

        <tr class="bold"><td></td><td colspan="6" class="indent-1">c. Belanja Non Program</td></tr>
        @foreach($realisasi['expense_rows']['non_program'] as $row)
        <tr>
            <td></td>
            <td class="indent-2">{{ $row['label'] }}</td>
            @foreach($columns as $key => $title)
            <td class="amount">{{ $rupiah($row[$key]) }}</td>
            @endforeach
        </tr>
        @endforeach
        <tr class="bold">
            <td></td><td style="text-align: right">Jumlah</td>
            @foreach($columns as $key => $title)
            <td class="amount">{{ $rupiah($realisasi['expense_total'][$key]) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

<div class="report-footer">
    Laporan Realisasi yang disampaikan telah sesuai dengan sasaran penggunaan yang ditetapkan dengan peraturan perundang-undangan dan telah didukung oleh kelengkapan dokumen yang sah sesuai ketentuan yang berlaku dan bertanggung jawab atas kebenarannya.
</div>
<div style="margin-top: 8px">Demikian laporan realisasi ini dibuat untuk digunakan sebagaimana mestinya.</div>

<div class="signature">
    {{ $laporan->kota_surat }}, {{ $laporan->tanggal_surat->locale('id')->translatedFormat('d F Y') }}<br>
    Kepala Satuan Pendidikan<br><br><br><br>
    <strong><u>{{ $sekolah->nama_kepala_sekolah }}</u></strong><br>
    NIP. {{ $sekolah->nip_kepala_sekolah }}
</div>

<script>
    (() => {
        const key = 'print_config_realisasi';
        const root = document.documentElement;
        const update = (type, value, save = true) => {
            if (type === 'font') {
                root.style.setProperty('--base-font', `${value}pt`);
                document.getElementById('realisasi-font-value').textContent = value;
            } else if (type === 'pad') {
                root.style.setProperty('--table-padding', `${value}px`);
                document.getElementById('realisasi-pad-value').textContent = value;
            } else {
                root.style.setProperty('--page-margin', `${value}cm`);
                document.getElementById('realisasi-margin-value').textContent = value;
            }
            if (save) {
                const settings = JSON.parse(localStorage.getItem(key) || '{}');
                settings[type] = value;
                localStorage.setItem(key, JSON.stringify(settings));
            }
        };
        window.updateRealisasiPrint = update;
        window.toggleRealisasiControls = () => document.getElementById('realisasi-print-controls').classList.toggle('minimized');
        const settings = JSON.parse(localStorage.getItem(key) || '{}');
        if (settings.font) update('font', settings.font, false);
        if (settings.pad) update('pad', settings.pad, false);
        if (settings.margin) update('margin', settings.margin, false);
    })();
</script>
@endsection
