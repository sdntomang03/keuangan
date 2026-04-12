<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RealisasiKomponenExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $dataRkas;

    protected $anggaran;

    protected $sekolah;

    protected $periodeText;

    public function __construct($dataRkas, $anggaran, $sekolah, $periodeText)
    {
        $this->dataRkas = $dataRkas;
        $this->anggaran = $anggaran;
        $this->sekolah = $sekolah;
        $this->periodeText = $periodeText;
    }

    public function view(): View
    {
        return view('exports.realisasi_komponen', [
            'dataRkas' => $this->dataRkas,
            'anggaran' => $this->anggaran,
            'sekolah' => $this->sekolah,
            'periodeText' => $this->periodeText,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true]],
            'A:B' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                ],
            ],
            'A:L' => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                ],
            ],
        ];
    }
}
