<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpjEkskul extends Model
{
    protected $table = 'spj_ekskul';

    protected $guarded = [];

    // Relasi
    public function details()
    {
        return $this->hasMany(SpjEkskulDetail::class, 'spj_ekskul_id');
    }

    public function ekskul()
    {
        return $this->belongsTo(RefEkskul::class, 'ref_ekskul_id');
    }

    public function rekanan()
    {
        return $this->belongsTo(Rekanan::class, 'rekanan_id');
    }

    public function belanja()
    {
        return $this->belongsTo(Belanja::class, 'belanja_id');
    }
}
