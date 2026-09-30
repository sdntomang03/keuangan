<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyesuaianPagu extends Model
{
    protected $fillable = [
        'anggaran_id',
        'user_id',
        'jenis',
        'tw',
    ];

    public function rincis(): HasMany
    {
        return $this->hasMany(PenyesuaianPaguRinci::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
