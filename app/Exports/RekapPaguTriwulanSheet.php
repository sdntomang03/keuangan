<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPaguTriwulanSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $rekeningRekap,
        private readonly int $triwulan
    ) {}

    public function array(): array
    {
        $rows = [];
        foreach ($this->rekeningRekap as $index => $rekening) {
            $row = $index + 2;
            $rows[] = [
                $index + 1,
                $rekening['kode'],
                $rekening['akun'],
                "=SUMIF('Rincian Komponen'!\$D:\$D,B{$row},'Rincian Komponen'!\$O:\$O)",
                "=SUMIF('Rincian Komponen'!\$D:\$D,B{$row},'Rincian Komponen'!\$P:\$P)",
                "=D{$row}+E{$row}",
            ];
        }

        $totalRow = count($rows) + 2;
        $sum = fn (string $column) => $rows === []
            ? '=0'
            : "=SUM({$column}2:{$column}".($totalRow - 1).')';
        $rows[] = [
            '',
            '',
            'TOTAL',
            $sum('D'),
            $sum('E'),
            $sum('F'),
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Rekening',
            'Nama Akun',
            'Pagu Awal',
            'Penyesuaian Bersih',
            'Pagu TW Setelah Penyesuaian',
        ];
    }

    public function title(): string
    {
        return 'Rekap Pagu per Kode Rekening';
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0;[Red]-#,##0',
            'E' => '#,##0;[Red]-#,##0',
            'F' => '#,##0;[Red]-#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rekeningRekap) + 2;
        $sheet->freezePane('A2');
        $sheet->getAutoFilter()->setRange('A1:F'.($lastRow - 1));
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
        ]);
        $sheet->getStyle("A{$lastRow}:F{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF111827']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        return [];
    }
}
