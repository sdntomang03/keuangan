<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komponen extends Model
{
    protected $fillable = [
        'kode_rekening', 'idkomponen', 'namakomponen',
        'spek', 'satuan', 'harga', 'tahun',
    ];

    public function korek()
    {
        return $this->belongsTo(Korek::class, 'kode_rekening', 'kode');
    }
}
