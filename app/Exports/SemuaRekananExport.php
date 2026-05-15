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
                // 1. Tambahkan Sheet Rekap untuk Rekanan ini
                $sheets[] = new RekapRekananSheet($dataBelanja, $rekanan);

                // 2. Tambahkan Sheet Rincian (URK) per transaksi milik Rekanan ini
                foreach ($dataBelanja as $belanja) {
                    $sheets[] = new SingleBelanjaSheet($belanja);
                }
            }
        }

        return $sheets;
    }
}
