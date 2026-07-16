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

    protected static function booted()
    {
        // Event ini dipicu SETELAH sebuah record Talangan berhasil dihapus
        static::deleted(function ($talangan) {

            // Cek apakah masih ada sisa rincian talangan lain yang menggunakan surat_id yang sama
            $sisaTalangan = self::where('surat_id', $talangan->surat_id)->count();

            // Jika rincian talangan sudah habis (0), maka hapus juga Surat induknya
            if ($sisaTalangan === 0) {
                \App\Models\Surat::find($talangan->surat_id)?->delete();
            }
        });
    }
}
