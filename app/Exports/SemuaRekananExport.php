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

        // Setiap 1 Rekanan akan dibuatkan 1 Sheet
        foreach ($this->daftarRekanan as $rekanan) {
            $sheets[] = new RekananSheet($rekanan);
        }

        return $sheets;
    }
}
