<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BelanjaExport implements WithMultipleSheets
{
    protected $dataBelanja;

    public function __construct($dataBelanja)
    {
        $this->dataBelanja = $dataBelanja;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Rekap Seluruh Belanja (Per Bukti)
        $sheets[] = new RekapBelanjaSheet($this->dataBelanja);

        // Sheet 2: REKAP NPD (Per Kegiatan & Rekening) -- BARU
        $sheets[] = new NpdSheet($this->dataBelanja);

        // Sheet 3 dst: Detail per Transaksi
        foreach ($this->dataBelanja as $belanja) {
            $sheets[] = new SingleBelanjaSheet($belanja);
        }

        return $sheets;
    }
}
