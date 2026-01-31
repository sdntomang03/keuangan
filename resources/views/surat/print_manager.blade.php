@extends('layouts.cetak')

@section('content')

@if(isset($mode) && $mode == 'bundel')

{{-- MODE BUNDEL (CETAK SEMUA) --}}

{{-- 1. Surat Permintaan --}}
<div class="surat-container">
    @include('surat.partials.permintaan', ['data' => $dataSurat['permintaan']])
</div>
<div class="page-break"></div>

{{-- 2. Surat Negosiasi --}}
<div class="surat-container">
    @include('surat.partials.negosiasi', ['data' => $dataSurat['negosiasi']])
</div>
<div class="page-break"></div>

{{-- 3. Surat Pesanan --}}
<div class="surat-container">
    @include('surat.partials.pesanan', ['data' => $dataSurat['pesanan']])
</div>
<div class="page-break"></div>

{{-- 4. Berita Acara --}}
<div class="surat-container">
    @include('surat.partials.berita_acara', ['data' => $dataSurat['pemeriksaan']])
</div>

@else

{{-- MODE SATUAN (CETAK 1 SURAT SAJA) --}}
{{-- Kita panggil partial sesuai variabel $jenis_surat --}}

@include('surat.partials.' . $jenis_surat, ['data' => $surat])

@endif

@endsection