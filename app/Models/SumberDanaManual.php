<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumberDanaManual extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke transaksi RKAS
     */
    public function rkasManuals()
    {
        return $this->hasMany(RkasManual::class, 'sumber_dana_id');
    }
}
