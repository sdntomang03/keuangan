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
            // Setiap rekanan dibuatkan satu sheet yang berisi semua transaksinya
            $sheets[] = new RekananSheet($rekanan);
        }

        return $sheets;
    }
}
