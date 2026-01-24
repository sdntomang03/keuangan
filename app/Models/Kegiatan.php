<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'snp',
        'sumber_dana',
        'kodedana',
        'namadana',
        'kodegiat',
        'namagiat',
        'kegiatan',
        'idbl',
        'link',
    ];

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
