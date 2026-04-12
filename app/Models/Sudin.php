<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sudin extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'sudins';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'nama',
        'singkatan',
    ];

    /**
     * Relasi: Satu Sudin memiliki banyak Sekolah.
     * (One To Many)
     */
    public function sekolahs()
    {
        return $this->hasMany(Sekolah::class);
    }
}
