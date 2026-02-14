<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekananMultipleSheetExport implements WithMultipleSheets
{
    protected $dataBelanja;

    protected $rekanan; // Tambahkan properti rekanan

    public function __construct($dataBelanja, $rekanan) // Terima data rekanan
    {
        $this->dataBelanja = $dataBelanja;
        $this->rekanan = $rekanan;
    }

    public function sheets(): array
    {
        $sheets = [];

        // 1. TAMBAHKAN SHEET REKAP (HALAMAN 1)
        // Ini akan menjadi Tab paling kiri di Excel
        $sheets[] = new RekapRekananSheet($this->dataBelanja, $this->rekanan);

        // 2. TAMBAHKAN SHEET RINCIAN PER TRANSAKSI
        // Ini akan menjadi Tab-tab berikutnya
        foreach ($this->dataBelanja as $belanja) {
            $sheets[] = new SingleBelanjaSheet($belanja);
        }

        return $sheets;
    }
}
