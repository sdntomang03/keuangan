<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubProgram extends Model
{
    protected $guarded = [];

    /**
     * Kembali ke Program Utama
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relasi ke Daftar Uraian Kegiatan (KegiatanManual)
     */
    public function uraians(): HasMany
    {
        return $this->hasMany(KegiatanManual::class, 'sub_program_id');
    }
}
