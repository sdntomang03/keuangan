<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BelanjaRinci extends Model
{
    protected $fillable = [
        'spek',
        'idblrinci',
        'belanja_id',
        'namakomponen',
        'harga_satuan',
        'volume',
        'total_bruto', // harga_satuan * volume
    ];

    public function rkas()
    {
        // Menghubungkan akb_rincis ke rkas berdasarkan idblrinci
        return $this->belongsTo(Rkas::class, 'idblrinci', 'idblrinci');
    }
}
