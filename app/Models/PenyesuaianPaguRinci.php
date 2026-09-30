<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyesuaianPaguRinci extends Model
{
    protected $fillable = [
        'penyesuaian_pagu_id',
        'idblrinci',
        'namakomponen',
        'satuan',
        'bulan',
        'harga_satuan',
        'volume_awal',
        'volume_setelah',
        'volume_selisih',
        'ppn_persen',
        'nominal_selisih',
        'nominal_ppn',
        'pagu_dapat_digeser',
    ];

    public function penyesuaianPagu(): BelongsTo
    {
        return $this->belongsTo(PenyesuaianPagu::class);
    }

    public function rkas(): BelongsTo
    {
        return $this->belongsTo(Rkas::class, 'idblrinci', 'idblrinci');
    }
}
