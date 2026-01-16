<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Belanja extends Model
{
    protected $fillable = [
        'user_id',
        'rekanan_id',
        'tanggal',
        'no_bukti',
        'uraian',
        'subtotal',
        'ppn',
        'pph',
        'transfer',
        'idbl',
        'kodeakun',
    ];

    public function rekanan()
    {
        return $this->belongsTo(Rekanan::class);
    }

    public function rincis()
    {
        return $this->hasMany(BelanjaRinci::class);
    }

    public function pajaks()
    {
        return $this->hasMany(Pajak::class);
    }

    public function kegiatans()
    {
        return $this->belongsTo(Kegiatan::class);
    }
}
