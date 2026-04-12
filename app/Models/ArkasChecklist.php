<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArkasChecklist extends Model
{
    protected $guarded = ['id'];

    public function rkas()
    {
        return $this->belongsTo(Rkas::class);
    }
}
