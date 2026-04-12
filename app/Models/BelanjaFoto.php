<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class BelanjaFoto extends Model
{
    use FilterAnggaranAktif;

    protected $fillable = ['belanja_id', 'path', 'latitude', 'longitude'];

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
