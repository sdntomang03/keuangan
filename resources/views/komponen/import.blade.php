@extends('layouts.app') {{-- Sesuaikan dengan nama layout master Anda --}}

@section('content')
<div class="container-fluid">
    {{-- Page Title --}}
    <div class="row bg-title">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <h3 class="page-title text-primary"><i class="fa fa-upload"></i>&nbsp; Import JSON Komponen</h3>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
            <a href="{{ route('komponenrkas.index') }}" class="btn btn-outline btn-info waves-effect waves-light m-t-5">
                <i class="ti-back-left m-r-5"></i> <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2 col-sm-12 col-xs-12">
            <div class="white-box border-top-lg border-top-blue">
                <div class="m-b-20 b-b p-b-10">
                    <h4 class="box-title">Formulir Import Data</h4>
                    <p class="text-muted">Unggah satu atau beberapa file JSON sekaligus. Sistem akan otomatis mendeteksi
                        kode rekening dari nama file.</p>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="m-b-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form class="form-horizontal form-material" action="{{ route('komponenrkas.storeImport') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group m-t-20">
                        <label class="col-xs-12 font-bold">Tahun Anggaran <span class="text-danger">*</span></label>
                        <div class="col-xs-12">
                            <input type="number" name="tahun" class="form-control" value="{{ date('Y') + 1 }}" required>
                        </div>
                    </div>

                    <div class="form-group m-t-20">
                        <label class="col-xs-12 font-bold">Kode Rekening (Default)</label>
                        <div class="col-xs-12">
                            <select name="kode_rekening" class="form-control select2">
                                <option value="">-- Otomatis ambil dari nama file --</option>
                                @foreach($koreks as $korek)
                                <option value="{{ $korek->kode }}">{{ $korek->kode }} - {{ $korek->uraian_singkat }}
                                </option>
                                @endforeach
                            </select>
                            <span class="help-block"><small>Hanya digunakan jika nama file gagal dibaca. Jika nama file
                                    sudah memiliki kode (contoh: 5.1.xx.json), kosongkan saja.</small></span>
                        </div>
                    </div>

                    <div class="form-group m-t-20">
                        <label class="col-xs-12 font-bold">File JSON Komponen <span class="text-danger">*</span></label>
                        <div class="col-xs-12">
                            <input type="file" name="json_files[]" class="form-control" accept=".json" multiple required
                                style="border: 1px solid #e4e7ea; padding: 5px;">
                            <span class="help-block"><small><i class="fa fa-info-circle text-info"></i> Anda bisa
                                    menyorot (blok) banyak file sekaligus saat memilih file.</small></span>
                        </div>
                    </div>

                    <div class="form-group text-right m-t-40 m-b-0">
                        <div class="col-xs-12">
                            <a href="{{ route('komponenrkas.index') }}"
                                class="btn btn-default waves-effect waves-light m-r-10">Batal</a>
                            <button class="btn btn-info text-uppercase waves-effect waves-light" type="submit">
                                <i class="fa fa-save"></i> Simpan Import
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection