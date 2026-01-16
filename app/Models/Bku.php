<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bku extends Model
{
    protected $guarded = [];

    public static function catat($tanggal, $no_bukti, $uraian, $debit, $kredit, $belanjaId = null, $pajakId = null)
    {
        // 1. Ambil data terakhir untuk saldo dan nomor urut
        $terakhir = self::orderBy('id', 'desc')->first();
        $saldoTerakhir = $terakhir ? $terakhir->saldo : 0;
        $noUrutTerakhir = $terakhir ? $terakhir->no_urut : 0;

        // 2. Hitung saldo baru
        $saldoBaru = $saldoTerakhir + ($debit - $kredit);

        // 3. Simpan data
        return self::create([
            'no_urut' => $noUrutTerakhir + 1,
            'tanggal' => $tanggal,
            'no_bukti' => $no_bukti,
            'uraian' => $uraian,
            'debit' => $debit,
            'kredit' => $kredit,
            'saldo' => $saldoBaru,
            'belanja_id' => $belanjaId,
            'pajak_id' => $pajakId,
        ]);
    }

    public function belanja()
    {
        return $this->belongsTo(Belanja::class, 'belanja_id');
    }

    // Relasi ke tabel Pajak (opsional, jika ingin menampilkan detail pajak juga)
    public function pajak()
    {
        return $this->belongsTo(Pajak::class, 'pajak_id');
    }
}
