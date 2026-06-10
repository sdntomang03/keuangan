<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanEkskulFoto extends Model
{
    protected $fillable = ['laporan_ekskul_id', 'path_foto'];

    public function laporanEkskul()
    {
        return $this->belongsTo(LaporanEkskul::class, 'laporan_ekskul_id');
    }
}
