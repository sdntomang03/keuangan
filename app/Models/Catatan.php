<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catatan extends Model
{
    protected $fillable = ['anggaran_id', 'tw', 'catatan', 'file_path', 'is_tl', 'user_id'];

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class);
    }
}
