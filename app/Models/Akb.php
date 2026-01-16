<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akb extends Model
{
    protected $fillable = [
        'idakun', 'idblrinci', 'volume', 'pajak', 'totalrincian',
        'bulan1', 'bulan2', 'bulan3', 'bulan4', 'bulan5', 'bulan6',
        'bulan7', 'bulan8', 'bulan9', 'bulan10', 'bulan11', 'bulan12',
        'totalakb', 'selisih', 'tahun', 'realtw1', 'realtw2', 'realtw3', 'realtw4',
        'jenis_anggaran', 'user_id', 'setting_id',
    ];

    public function rkas()
    {
        // AKB ini dimiliki oleh satu RKAS
        return $this->belongsTo(Rkas::class, 'idblrinci', 'idblrinci');
    }
}
