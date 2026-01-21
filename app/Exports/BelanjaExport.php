<?php

namespace App\Exports;

use App\Models\Belanja;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BelanjaExport implements WithMultipleSheets
{
    protected $anggaran;

    public function __construct($anggaran)
    {
        // Simpan data anggaran yang dikirim dari controller
        $this->anggaran = $anggaran;
    }

    public function sheets(): array
    {
        $sheets = [];
        $user = auth()->user();

        // 1. Tambahkan relasi pajaks.masterPajak agar sama dengan method show
        $allBelanja = Belanja::with([
            'rincis.rkas',
            'rekanan',
            'korek',
            'pajaks.masterPajak', // Wajib ditambahkan untuk detail pajak
        ])
            ->where('anggaran_id', $this->anggaran->id)
            ->where('user_id', $user->id)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Sheet 1: Rekap Seluruh Belanja
        $sheets[] = new RekapBelanjaSheet($allBelanja, $this->anggaran);

        // Sheet Selanjutnya: Detail per Transaksi
        foreach ($allBelanja as $belanja) {
            // Karena relasi sudah di-load di atas, $belanja di sini sudah lengkap membawa data pajak
            $sheets[] = new SingleBelanjaSheet($belanja);
        }

        return $sheets;
    }
}
