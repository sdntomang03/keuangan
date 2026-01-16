<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rkas extends Model
{
    protected $fillable = [
        'idblrinci', 'idbl', 'idsubtitle', 'namasub', 'keterangan',
        'kodeakun', 'namaakun', 'idkomponen', 'namakomponen', 'spek',
        'satuan', 'koefisien', 'hargasatuan', 'hargabaru', 'totalharga',
        'totalpajak', 'createduser', 'createddate', 'createdtime',
        'updateduser', 'updateddate', 'updatedtime', 'giatsubteks',
        'action', 'user1', 'user2', 'jenis_anggaran', 'tahun', 'user_id', 'setting_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('setting', function ($builder) {
            if (auth()->check() && auth()->user()->setting_id) {
                $builder->where('setting_id', auth()->user()->setting_id);
            }
        });
    }

    public function kegiatan()
    {
        // RKAS memiliki satu master kegiatan berdasarkan idbl
        return $this->belongsTo(Kegiatan::class, 'idbl', 'idbl');
    }

    public function akb()
    {
        // RKAS memiliki satu AKB dihubungkan melalui idblrinci
        return $this->hasOne(Akb::class, 'idblrinci', 'idblrinci');
    }

    public function korek()
    {
        // Menghubungkan kodeakun (RKAS) ke kolom kode (Koreks)
        return $this->belongsTo(Korek::class, 'kodeakun', 'id');
    }

    public function akbRincis()
    {
        return $this->hasMany(AkbRinci::class, 'idblrinci', 'idblrinci');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
