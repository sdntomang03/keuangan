<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talangan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Pastikan tanggal_surat terbaca sebagai format Date/Carbon
    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class, 'anggaran_id');
    }

    public function korek()
    {
        // Jika di tabel koreks kolom kuncinya adalah 'id'
        return $this->belongsTo(Korek::class, 'kodeakun', 'id');
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }
}
