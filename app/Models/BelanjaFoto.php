<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BelanjaFoto extends Model
{
    protected $fillable = ['belanja_id', 'path', 'latitude', 'longitude'];

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
