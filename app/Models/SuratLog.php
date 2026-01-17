<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratLog extends Model
{
    protected $fillable = ['belanja_id', 'jenis_surat', 'tanggal', 'nomor_surat'];

    public function belanja()
    {
        return $this->belongsTo(Belanja::class);
    }
}
