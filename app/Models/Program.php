<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke Sub Program (One-to-Many)
     */
    public function subPrograms(): HasMany
    {
        return $this->hasMany(SubProgram::class);
    }
}
