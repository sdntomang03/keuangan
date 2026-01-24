<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rekanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'sekolah_id',
        'nama_rekanan',
        'alamat',
        'alamat2',
        'kota',
        'provinsi',
        'no_telp',
        'nama_pimpinan',
        'pic',
        'jabatan',
        'nama_bank',
        'no_rekening',
        'npwp',
        'pkp',
        'ket',
    ];

    public function belanjas()
    {
        return $this->hasMany(Belanja::class, 'rekanan_id');
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
}
