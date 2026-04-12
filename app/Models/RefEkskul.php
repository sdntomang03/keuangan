<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class RefEkskul extends Model
{
    use FilterAnggaranAktif;

    protected $table = 'ref_ekskul';

    protected $guarded = [];

    public function rekanan()
    {
        // Parameter 2 ('rekanan_id') adalah nama kolom foreign key di tabel ref_ekskul
        return $this->belongsTo(Rekanan::class, 'rekanan_id');
    }
}
