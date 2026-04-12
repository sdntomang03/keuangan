<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class Korek extends Model
{
    use FilterAnggaranAktif;

    protected $fillable = ['ket', 'kode', 'uraian_singkat', 'singkat', 'jenis_belanja'];

    public function rkas()
    {
        return $this->hasMany(Rkas::class, 'kodeakun', 'kode');
    }
}
