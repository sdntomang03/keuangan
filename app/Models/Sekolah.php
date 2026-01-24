<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit (opsional tapi disarankan)
    protected $table = 'sekolahs';

    protected $fillable = [
        'user_id',
        'nama_sekolah',
        'npsn',
        'nama_kepala_sekolah',
        'nip_kepala_sekolah',
        'nama_bendahara',
        'nip_bendahara',
        'nama_pengurus_barang',
        'nip_pengurus_barang',
        'anggaran_id_aktif',
        'triwulan_aktif',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'kodepos',
        'telp',
        'email',
        'logo',
    ];

    // app/Models/Sekolah.php
    public function users(): HasMany
    {
        // Nama method dibuat jamak (users) karena jumlahnya banyak
        return $this->hasMany(User::class, 'sekolah_id');
    }

    /**
     * Relasi ke semua daftar anggaran (BOS 2024, BOS 2025, BOP 2025, dll)
     */
    public function anggarans(): HasMany
    {
        return $this->hasMany(Anggaran::class);
    }

    /**
     * Relasi ke Anggaran yang sedang aktif dipilih
     */
    public function anggaranAktif(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class, 'anggaran_id_aktif');
    }

    public function rekanans()
    {
        return $this->hasMany(Rekanan::class, 'sekolah_id');
    }
}
