<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RincianKegiatan extends Model
{
    protected $guarded = [];

    // Relasi balik ke Uraian
    public function uraian()
    {
        return $this->belongsTo(UraianKegiatan::class, 'uraian_id');
    }
}
