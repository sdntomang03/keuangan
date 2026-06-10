<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanEkskul extends Model
{
    use HasFactory;

    protected $fillable = ['ekskul_id', 'tanggal_kegiatan', 'materi', 'catatan'];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    // Relasi ke banyak foto di satu pertemuan
    public function fotos()
    {
        return $this->hasMany(LaporanEkskulFoto::class, 'laporan_ekskul_id');
    }
}
