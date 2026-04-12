<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    use FilterAnggaranAktif;

    protected $fillable = ['tanggal', 'no_bukti', 'uraian', 'nominal', 'anggaran_id', 'user_id', 'tw'];
}
