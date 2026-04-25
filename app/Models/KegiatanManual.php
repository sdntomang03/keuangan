<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanManual extends Model
{
    protected $guarded = [];

    // Scope untuk mempermudah filter data per sekolah
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    // Relasi ke Rincian Belanja (RKAS)
    public function rkasManuals()
    {
        // Ganti 'kegiatan_id' menjadi 'kegiatan_manual_id'
        return $this->hasMany(RkasManual::class, 'kegiatan_manual_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function subProgram(): BelongsTo
    {
        return $this->belongsTo(SubProgram::class, 'sub_program_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDanaManual::class, 'sumber_dana_id');
    }
}
