<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenomoranSurat extends Model
{
    protected $fillable = ['sekolah_id', 'tahun', 'triwulan', 'nomor_awal'];
}
