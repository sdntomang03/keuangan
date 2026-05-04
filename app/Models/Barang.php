<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    // Menentukan nama tabel (opsional jika mengikuti standar Laravel)
    protected $table = 'barangs';

    // Mengizinkan field ini untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'id_barang',
        'kode_rekening',
        'nama_rekening',
        'nama_barang',
        'satuan',
        'harga_barang',
        'harga_minimal',
        'harga_maksimal',
        'kode_belanja',
        'kategori',
        'digunakan_rkas',
    ];

    // Otomatis mengkonversi (casting) tipe data saat diambil dari database
    protected $casts = [
        'digunakan_rkas' => 'boolean',
        'harga_barang' => 'integer',
        'harga_minimal' => 'integer',
        'harga_maksimal' => 'integer',
    ];
}
