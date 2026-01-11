<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkbRinci extends Model
{
    protected $guarded = [];

    public function rkas()
    {
        // Menghubungkan akb_rincis ke rkas berdasarkan idblrinci
        return $this->belongsTo(Rkas::class, 'idblrinci', 'idblrinci');
    }
}
