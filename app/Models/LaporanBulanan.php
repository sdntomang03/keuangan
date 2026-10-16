<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanBulanan extends Model
{
    protected $fillable = [
        'sekolah_id',
        'anggaran_id',
        'user_id',
        'bulan',
        'tahun',
        'nomor_surat',
        'sumber_dana',
        'saldo_awal',
        'penerimaan_tahap_i',
        'penerimaan_tahap_ii',
        'penerimaan_kinerja',
        'penerimaan_lain',
        'pengeluaran_barang_jasa',
        'pengeluaran_modal_peralatan',
        'pengeluaran_modal_aset_lainnya',
        'pengeluaran_lain',
        'kas_tunai',
        'rekening_bank',
        'penjelasan_selisih',
        'kota_surat',
        'tanggal_surat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'bulan' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function anggaran(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class);
    }

    public function getJumlahPenerimaanAttribute(): float
    {
        return (float) $this->penerimaan_tahap_i
            + (float) $this->penerimaan_tahap_ii
            + (float) $this->penerimaan_kinerja
            + (float) $this->penerimaan_lain;
    }

    public function getJumlahPengeluaranAttribute(): float
    {
        return (float) $this->pengeluaran_barang_jasa
            + (float) $this->pengeluaran_modal_peralatan
            + (float) $this->pengeluaran_modal_aset_lainnya
            + (float) $this->pengeluaran_lain;
    }

    public function getSisaDanaAttribute(): float
    {
        return (float) $this->saldo_awal + $this->jumlah_penerimaan - $this->jumlah_pengeluaran;
    }
}
