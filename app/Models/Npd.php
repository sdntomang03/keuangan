<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Npd extends Model
{
    use HasFactory;

    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = ['id'];

    // Casting tipe data agar mudah diolah
    protected $casts = [
        'tanggal' => 'date',
        'nilai_npd' => 'double',
        'pagu_anggaran' => 'double',
        'total_realisasi' => 'double',
        'sisa_anggaran' => 'double',
    ];

    /**
     * Relasi ke Sekolah
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Relasi ke Kegiatan (Menggunakan IDBL)
     * Parameter 2: Foreign Key di tabel NPD (idbl)
     * Parameter 3: Primary Key di tabel Kegiatan (id)
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'idbl', 'idbl');
    }

    /**
     * Relasi ke Kode Rekening/Korek (Menggunakan KODEAKUN)
     * Parameter 2: Foreign Key di tabel NPD (kodeakun)
     * Parameter 3: Key di tabel Korek yang isinya kode string (biasanya 'kode')
     */
    public function korek(): BelongsTo
    {
        // Asumsi: Di tabel 'koreks', kolom yang menyimpan "5.1.02.xx" bernama 'kode'
        return $this->belongsTo(Korek::class, 'kodeakun');
    }

    public function belanjas()
    {
        return $this->hasMany(Belanja::class, 'idbl', 'idbl')
            ->whereColumn('kodeakun', 'npds.kodeakun');
    }
}
