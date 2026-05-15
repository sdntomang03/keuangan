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
        // Mengirim data 1 rekanan dan kumpulan belanjanya ke View
        return view('exports.excel_rekanan_semua_transaksi', [
            'rekanan' => $this->rekanan,
            'belanjas' => $this->rekanan->belanjas,
        ]);
    }

    public function title(): string
    {
        // Syarat mutlak Excel: Nama tab max 31 karakter dan tanpa simbol ilegal
        $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', $this->rekanan->nama_rekanan);

        return substr($cleanName, 0, 31);
    }
}
