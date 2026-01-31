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
        'rincian',
        'uraian',
        'subtotal',
        'ppn',
        'pph',
        'transfer',
        'idbl',
        'kodeakun',
        'status',
        'anggaran_id',
        'tanggal_bast',
        'no_bast',
        'tw',

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

    public function surats()
    {
        return $this->hasMany(Surat::class);
    }

    public function fotos()
    {
        // Pastikan nama modelnya benar
        return $this->hasMany(BelanjaFoto::class, 'belanja_id');
    }

    public function spjEkskul()
    {
        return $this->hasOne(SpjEkskul::class, 'belanja_id');
    }

    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data; // Middleware

        // Ambil Belanja yang memiliki relasi ke spj_ekskul
        // Ini memfilter agar yang tampil HANYA belanja Ekskul, bukan ATK/Lainnya
        $belanjas = Belanja::with(['rekanan', 'spjEkskul'])
            ->where('anggaran_id', $anggaran->id)
            ->whereHas('spjEkskul') // Hanya ambil yang punya data SPJ Ekskul
            ->orderBy('tanggal', 'desc')
            ->orderBy('no_bukti', 'desc')
            ->paginate(10);

        return view('ekskul.index', compact('belanjas', 'anggaran'));
    }
}
