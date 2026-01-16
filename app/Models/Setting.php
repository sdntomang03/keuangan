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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Jika satu sekolah bisa punya banyak user (misal: Bendahara & Operator)
    public function users()
    {
        return $this->hasMany(User::class, 'setting_id');
    }
}
