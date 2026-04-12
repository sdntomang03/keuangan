<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anggaran extends Model
{
    protected $fillable = [
        'sekolah_id', // Penting!
        'nama_anggaran', // misal: BOS Reguler
        'tahun',
        'singkatan',
        'is_aktif', // aktif/nonaktif
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
