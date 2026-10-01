<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanLampiran extends Model
{
    protected $fillable = ['catatan_id', 'file_path'];

    public function catatan(): BelongsTo
    {
        return $this->belongsTo(Catatan::class);
    }
}
