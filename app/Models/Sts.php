<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sts extends Model
{
    protected $table = 'sts';

    protected $guarded = ['id'];

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class);
    }
}
