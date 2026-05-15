<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekananSheet implements FromView, ShouldAutoSize, WithTitle
{
    protected $rekanan;

    public function __construct($rekanan)
    {
        $this->rekanan = $rekanan;
    }

    public function view(): View
    {
        return view('exports.excel_rekanan_semua_transaksi', [
            'rekanan' => $this->rekanan,
            'belanjas' => $this->rekanan->belanjas,
        ]);
    }

    public function title(): string
    {
        // Nama tab adalah nama rekanan (maksimal 31 karakter untuk Excel)
        return substr(preg_replace('/[^A-Za-z0-9 ]/', '', $this->rekanan->nama_rekanan), 0, 31);
    }
}
