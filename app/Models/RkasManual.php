<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkasManual extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke Master Sumber Dana
     */
    public function sumberDana()
    {
        return $this->belongsTo(SumberDanaManual::class, 'sumber_dana_id');
    }

    /**
     * Relasi ke Master Kegiatan
     */
    public function kegiatanManual()
    {
        // Ganti 'kegiatan_id' menjadi 'kegiatan_manual_id'
        return $this->belongsTo(KegiatanManual::class, 'kegiatan_manual_id');
    }

    /**
     * Relasi ke Master Kode Rekening
     */
    public function korek()
    {
        return $this->belongsTo(Korek::class, 'korek_id');
    }

    /**
     * Relasi ke Master Komponen (Bisa Null)
     */
    public function komponenManual()
    {
        return $this->belongsTo(KomponenManual::class, 'komponen_manual_id');
    }

    /**
     * Relasi ke Anggaran Kas Bulanan (AKB)
     */
    public function akbManuals()
    {
        return $this->hasMany(AkbManual::class, 'rkas_manual_id');
    }

    public function uraian()
    {
        return $this->belongsTo(UraianKegiatan::class, 'uraian_id');
    }

    // 2. Relasi ke Rincian Kegiatan
    public function rincianKegiatan()
    {
        return $this->belongsTo(RincianKegiatan::class, 'rincian_kegiatan_id');
    }
}
