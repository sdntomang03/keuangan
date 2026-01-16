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
}
