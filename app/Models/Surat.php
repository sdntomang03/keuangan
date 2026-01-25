<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surat extends Model
{
    use HasFactory;

    // Guarded ['id'] artinya semua kolom selain ID (termasuk sekolah_id, triwulan)
    // otomatis bisa diisi (Mass Assignment). Jadi aman.
    protected $guarded = ['id'];

    // Cast tanggal agar otomatis jadi Object Carbon (bisa diformat tgl)
    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    /**
     * Relasi ke Belanja (Induk)
     */
    public function belanja(): BelongsTo
    {
        return $this->belongsTo(Belanja::class);
    }

    /**
     * Relasi ke Sekolah (BARU)
     * Diperlukan untuk mengambil data sekolah (NPSN, Kop Surat) saat cetak surat
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Helper (Accessor) untuk Nama Lengkap Surat
     * Cara panggil di blade: {{ $surat->nama_dokumen }}
     */
    public function getNamaDokumenAttribute()
    {
        return match ($this->jenis_surat) {
            'PH' => 'Surat Permintaan Harga',
            'NH' => 'Surat Negosiasi Harga',
            'SP' => 'Surat Pesanan',
            'BAPB' => 'Berita Acara Penerimaan Barang',
            default => $this->jenis_surat,
        };
    }

    public function rincis()
    {
        return $this->belongsToMany(BelanjaRinci::class, 'surat_rinci', 'surat_id', 'belanja_rinci_id')
            ->withPivot('volume') // <--- WAJIB: Agar kolom volume bisa diakses
            ->withTimestamps();
    }
}
