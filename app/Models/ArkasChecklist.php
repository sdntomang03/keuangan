<?php

namespace App\Models;

use App\Traits\FilterAnggaranAktif;
use Illuminate\Database\Eloquent\Model;

class ArkasChecklist extends Model
{
    use FilterAnggaranAktif;

    protected $guarded = ['id'];

    public function rkas()
    {
        return $this->belongsTo(Rkas::class);
    }
}
