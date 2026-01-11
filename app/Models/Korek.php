<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Korek extends Model
{
    protected $fillable = ['ket', 'kode', 'uraian_singkat', 'singkat'];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kodeakun', 'kode');
    }
}
