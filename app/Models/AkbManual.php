<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkbManual extends Model
{
    protected $guarded = [];

    /**
     * Relasi kembali ke parent RKAS Manual
     */
    public function rkasManual()
    {
        return $this->belongsTo(RkasManual::class, 'rkas_manual_id');
    }
}
