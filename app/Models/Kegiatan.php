<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $guarded = [];

    // Jika idbl bukan angka yang auto-increment (misal: kode manual), tambahkan:
    // public $incrementing = false;
    protected $primaryKey = 'idbl';

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'idbl', 'idbl');
    }

    public function belanja()
    {
        return $this->hasMany(Belanja::class, 'idbl', 'idbl');
    }
}
