<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catatan extends Model
{
    protected $fillable = ['anggaran_id', 'tw', 'catatan', 'file_path', 'is_tl', 'user_id'];

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class);
    }

    public function lampirans(): HasMany
    {
        return $this->hasMany(CatatanLampiran::class);
    }
}
