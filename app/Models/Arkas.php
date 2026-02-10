<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arkas extends Model
{
    use HasFactory;

    // Nama tabel spesifik
    protected $table = 'arkas';

    // Kolom yang boleh diisi
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
        'jenis_belanja',
    ];

    // Konversi tipe data otomatis
    protected $casts = [
        'harga_barang' => 'double',
        'harga_minimal' => 'double',
        'harga_maksimal' => 'double',
    ];
}
