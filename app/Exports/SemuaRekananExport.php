<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SemuaRekananExport implements WithMultipleSheets
{
    protected $daftarRekanan;

    public function __construct($daftarRekanan)
    {
        $this->daftarRekanan = $daftarRekanan;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->daftarRekanan as $rekanan) {
            $dataBelanja = $rekanan->belanjas;

            if ($dataBelanja->isNotEmpty()) {
                // Kita gunakan ulang class RekapRekananSheet yang sudah Anda miliki!
                // 1 Tab (Sheet) di Excel = 1 Rekanan
                $sheets[] = new RekapRekananSheet($dataBelanja, $rekanan);
            }
        }

        return $sheets;
    }
}
