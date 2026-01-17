<?php

namespace App\Exports;

use App\Models\Belanja;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BelanjaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $user = auth()->user();
        $settingId = $user->setting_id;
        $anggaranId = session('anggaran_aktif_id'); // Atau dari $user->setting->anggaran_id_aktif

        $allBelanja = Belanja::with(['rincis.rkas', 'rekanan', 'korek'])
            ->where('setting_id', $settingId) // Kunci berdasarkan Sekolah
            ->where('anggaran_id', $anggaranId) // Kunci berdasarkan Tahun & Jenis (BOS/BOP)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Sheet 1: Rekap Seluruh Belanja (Diletakkan paling depan)
        $sheets[] = new RekapBelanjaSheet($allBelanja);

        // Sheet Selanjutnya: Detail per Transaksi
        foreach ($allBelanja as $belanja) {
            $sheets[] = new SingleBelanjaSheet($belanja);
        }

        return $sheets;
    }
}
