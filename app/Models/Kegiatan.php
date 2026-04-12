<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use FilterAnggaranAktif;
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = [];

    // Jika idbl bukan angka yang auto-increment (misal: kode manual), tambahkan:
    // public $incrementing = false;

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'idbl', 'idbl');
    }

    public function belanja()
    {
        return $this->hasMany(Belanja::class, 'idbl', 'idbl');
    }
}
