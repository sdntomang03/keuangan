<?php

namespace App\Services;

use App\Models\Bku;
use Illuminate\Support\Collection;

class BkuService
{
    /**
     * Menghitung saldo berjalan untuk koleksi BKU
     */
    public function calculateRunningBalance(Collection $bkus, $initialBalance = 0): Collection
    {
        $currentBalance = $initialBalance;

        return $bkus->transform(function ($item) use (&$currentBalance) {
            $currentBalance = $currentBalance + $item->debit - $item->kredit;
            $item->saldo_akhir = $currentBalance;

            return $item;
        });
    }

    /**
     * Mengambil data BKU sekaligus menghitung saldonya
     */
    public function getBkuWithBalance($anggaranId)
    {
        $bkus = Bku::with(['belanja.kegiatan', 'belanja.rekanan', 'penerimaan'])
            ->where('anggaran_id', $anggaranId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_urut', 'asc')
            ->get();

        return $this->calculateRunningBalance($bkus);
    }
}
