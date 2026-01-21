<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bku extends Model
{
    protected $guarded = [];

    // Tambahkan $anggaranId sebagai parameter ke-8
    public static function catat($tanggal, $no_bukti, $uraian, $debit, $kredit, $belanjaId = null, $pajakId = null, $anggaranId = null)
    {
        // 1. Ambil data terakhir KHUSUS untuk anggaran yang dipilih
        // Ini penting agar saldo BOS dan BOP tidak bercampur
        $terakhir = self::where('anggaran_id', $anggaranId)
            ->orderBy('id', 'desc')
            ->first();

        $saldoTerakhir = $terakhir ? $terakhir->saldo : 0;

        // No urut juga sebaiknya per anggaran atau per tahun
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
            'anggaran_id' => $anggaranId, // Sekarang variabel ini sudah ada dari parameter
            'user_id' => auth()->id(), // Tambahkan juga user_id jika kolomnya ada
        ]);
    }

    public function belanja()
    {
        return $this->belongsTo(Belanja::class, 'belanja_id');
    }

    public function pajak()
    {
        return $this->belongsTo(Pajak::class, 'pajak_id');
    }

    public function anggaran()
    {
        return $this->belongsTo(Anggaran::class, 'anggaran_id');
    }
}
