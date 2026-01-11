<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'user_id', 'nama_sekolah', 'nama_kepala_sekolah', 'nip_kepala_sekolah',
        'nama_bendahara', 'nip_bendahara', 'tahun_aktif', 'anggaran_aktif', 'triwulan_aktif',
    ];

    // Relasi balik ke User
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
