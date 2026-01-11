<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    protected $fillable = ['belanja_id', 'dasar_pajak_id', 'nominal', 'is_terima', 'is_setor'];

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
