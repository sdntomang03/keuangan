<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PaguTriwulanExport implements WithMultipleSheets
{
    public function __construct(
        private readonly array $rekeningRekap,
        private readonly array $komponen,
        private readonly int $triwulan
    ) {}

    public function sheets(): array
    {
        return [
            new RekapPaguTriwulanSheet($this->rekeningRekap, $this->triwulan),
            new RincianPaguTriwulanSheet($this->komponen, $this->triwulan),
        ];
    }
}
