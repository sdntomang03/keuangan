<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekanan extends Model
{
    protected $fillable = [
        'user_id',
        'nama_rekanan',
        'no_rekening',
        'nama_bank',
        'npwp',
    ];

    public function belanjas()
    {
        return $this->hasMany(Belanja::class, 'rekanan_id');
    }
}
