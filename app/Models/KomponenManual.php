<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenManual extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke Master Kode Rekening (Korek)
     */
    public function korek()
    {
        return $this->belongsTo(Korek::class, 'korek_id');
    }

    /**
     * Relasi ke transaksi RKAS yang menggunakan komponen ini
     */
    public function rkasManuals()
    {
        return $this->hasMany(RkasManual::class, 'komponen_manual_id');
    }
}
