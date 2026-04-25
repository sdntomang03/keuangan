<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Korek extends Model
{
    protected $fillable = ['ket', 'kode', 'uraian_singkat', 'singkat', 'jenis_belanja'];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kodeakun', 'kode');
    }

    public function komponenManuals(): HasMany
    {
        // Pastikan 'korek_id' adalah nama kolom foreign key di tabel komponen_manuals
        return $this->hasMany(KomponenManual::class, 'korek_id');
    }
}
