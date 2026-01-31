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
    // Tambahkan parameter optional $filterTw = null
    public function getBkuWithBalance($anggaranId, $filterTw = null)
    {
        // 1. Ambil SEMUA data dulu (Supaya perhitungan saldo berjalan benar dari awal tahun)
        $bkus = Bku::with(['belanja.kegiatan', 'belanja.rekanan', 'penerimaan'])
            ->where('anggaran_id', $anggaranId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_urut', 'asc')
            ->get();

        // 2. Hitung Saldo Berjalan (Fungsi ini menambahkan properti 'saldo_cair' ke setiap item)
        $bkusWithBalance = $this->calculateRunningBalance($bkus);

        // 3. Baru Filter datanya JIKA ada request filter TW
        if ($filterTw) {
            // Kita filter Collection-nya, bukan Query SQL-nya
            // values() digunakan untuk mereset index array agar rapi saat di-loop di View
            return $bkusWithBalance->where('tw', $filterTw)->values();
        }

        return $bkusWithBalance;
    }
}
