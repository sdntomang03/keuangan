<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpjEkskulDetail extends Model
{
    protected $table = 'spj_ekskul_detail';

    protected $guarded = [];

    public function spjEkskul()
    {
        // Relasi balik ke tabel induk (spj_ekskuls)
        // Parameter ke-2: foreign key di tabel detail
        // Parameter ke-3: primary key di tabel induk
        return $this->belongsTo(SpjEkskul::class, 'spj_ekskul_id', 'id');
    }
}
