<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Belanja extends Model
{
    const STATUS_DRAFT = 'draft';

    const STATUS_POSTED = 'posted';

    const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'user_id',
        'rekanan_id',
        'tanggal',
        'no_bukti',
        'uraian',
        'subtotal',
        'ppn',
        'pph',
        'transfer',
        'idbl',
        'kodeakun',
        'status',
        'anggaran_id',

    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function rekanan()
    {
        return $this->belongsTo(Rekanan::class);
    }

    public function rincis()
    {
        return $this->hasMany(BelanjaRinci::class);
    }

    public function pajaks()
    {
        return $this->hasMany(Pajak::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'idbl', 'idbl');
    }

    public function korek()
    {
        // Jika di tabel koreks kolom kuncinya adalah 'id'
        return $this->belongsTo(Korek::class, 'kodeakun', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class);
    }
}
