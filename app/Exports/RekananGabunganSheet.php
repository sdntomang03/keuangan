<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekananGabunganSheet implements FromView, ShouldAutoSize, WithTitle
{
    protected $rekanan;

    protected $belanjas;

    public function __construct($rekanan, $belanjas)
    {
        $this->rekanan = $rekanan;
        $this->belanjas = $belanjas;
    }

    public function view(): View
    {
        return view('exports.excel_rekanan_gabungan', [
            'rekanan' => $this->rekanan,
            'belanjas' => $this->belanjas,
        ]);
    }

    public function title(): string
    {
        // Nama tab diambil dari nama rekanan
        return substr(preg_replace('/[^A-Za-z0-9 ]/', '', $this->rekanan->nama_rekanan), 0, 31);
    }
}
