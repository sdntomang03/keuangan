<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $guarded = [];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'idbl', 'idbl');
    }

    public function belanjas()
    {
        return $this->hasMany(Belanja::class, 'idbl', 'idbl');
    }
}
