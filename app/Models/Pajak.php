<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    use FilterAnggaranAktif;

    protected $fillable = ['belanja_id', 'dasar_pajak_id', 'nominal', 'is_terima', 'is_setor'];

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }

    public function masterPajak()
    {

        return $this->belongsTo(DasarPajak::class, 'dasar_pajak_id');
    }
}
