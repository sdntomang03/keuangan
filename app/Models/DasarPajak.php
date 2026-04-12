<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DasarPajak extends Model
{
use FilterAnggaranAktif;
    use HasFactory;

    protected $fillable = [
        'nama_pajak',
        'persen',
    ];
}
