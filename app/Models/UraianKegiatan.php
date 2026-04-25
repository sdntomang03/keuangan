<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UraianKegiatan extends Model
{
    protected $guarded = [];

    // Relasi ke atas: Milik siapa Uraian ini? (Sub Program)
    public function subProgram()
    {
        return $this->belongsTo(SubProgram::class, 'sub_program_id');
    }

    // Relasi ke bawah: Uraian ini dipakai di Kegiatan Manual mana saja?
    public function kegiatanManuals()
    {
        return $this->hasMany(KegiatanManual::class, 'uraian_kegiatan_id');
    }
}
