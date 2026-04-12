<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Korek extends Model
{
    protected $fillable = ['ket', 'kode', 'uraian_singkat', 'singkat', 'jenis_belanja'];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kodeakun', 'kode');
    }
}
