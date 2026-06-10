<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    use HasFactory;

    protected $fillable = ['sekolah_id', 'user_id', 'nama_ekskul', 'periode', 'keterangan'];

    // Relasi ke tabel detail laporan pertemuan
    public function laporans()
    {
        return $this->hasMany(LaporanEkskul::class, 'ekskul_id');
    }

    // Relasi ke User Pelatih
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
}
