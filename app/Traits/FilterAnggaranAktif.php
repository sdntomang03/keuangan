<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterAnggaranAktif
{
    protected static function bootFilterAnggaranAktif()
    {
        static::addGlobalScope('anggaran_aktif', function (Builder $builder) {
            // Cek apakah ada request anggaran_data (hasil injeksi dari Middleware Anda)
            if (request()->has('anggaran_data') && request()->anggaran_data) {

                // Ambil nama tabel secara dinamis (misal: rkas, akb_rincis)
                $tableName = $builder->getModel()->getTable();

                // Otomatis tambahkan WHERE anggaran_id = ID_AKTIF
                $builder->where($tableName.'.anggaran_id', request()->anggaran_data->id);
            }
        });
    }
}
