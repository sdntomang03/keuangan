<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $fillable = ['tanggal', 'no_bukti', 'uraian', 'nominal', 'anggaran_id', 'user_id', 'tw'];
}
