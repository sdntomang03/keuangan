<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bku extends Model
{
    protected $guarded = [];

    // Tambahkan $anggaranId sebagai parameter ke-8
    public static function catat($tanggal, $no_bukti, $uraian, $debit, $kredit, $belanjaId = null, $pajakId = null, $anggaranId = null, $penerimaanId = null, $twAktif = null)
    {
        // 1. Ambil data terakhir KHUSUS untuk anggaran yang dipilih
        // Ini penting agar saldo BOS dan BOP tidak bercampur
        $terakhir = self::where('anggaran_id', $anggaranId)
            ->orderBy('id', 'desc')
            ->first();

        // No urut juga sebaiknya per anggaran atau per tahun
        $noUrutTerakhir = $terakhir ? $terakhir->no_urut : 0;

        // 3. Simpan data
        return self::create([
            'no_urut' => $noUrutTerakhir + 1,
            'tanggal' => $tanggal,
            'no_bukti' => $no_bukti,
            'uraian' => $uraian,
            'debit' => $debit,
            'kredit' => $kredit,
            'belanja_id' => $belanjaId,
            'pajak_id' => $pajakId,
            'anggaran_id' => $anggaranId, // Sekarang variabel ini sudah ada dari parameter
            'user_id' => auth()->id(), // Tambahkan juga user_id jika kolomnya ada
            'penerimaan_id' => $penerimaanId,
            'tw' => $twAktif,
        ]);
    }

    protected static function booted()
    {
        static::deleted(function ($bku) {
            // Jika baris BKU yang dihapus memiliki relasi penerimaan
            if ($bku->penerimaan_id) {
                $bku->penerimaan()->delete();
            }
        });
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

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class);
    }
}
