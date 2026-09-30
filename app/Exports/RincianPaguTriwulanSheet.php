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

class RincianPaguTriwulanSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $komponen,
        private readonly int $triwulan
    ) {}

    public function array(): array
    {
        $rows = array_map(
            fn (array $item, int $index) => [
                $index + 1,
                $item['kodegiat'],
                $item['namagiat'],
                $item['kode_rekening'],
                $item['akun'],
                $item['idblrinci'],
                $item['komponen'],
                $item['spek'],
                $item['keterangan'],
                $item['satuan'],
                $item['harga_satuan'],
                $item['ppn_persen'],
                $item['volume_awal'],
                $item['volume_setelah'],
                '=ROUND(M'.($index + 2).'*K'.($index + 2).'*(1+L'.($index + 2).'/100),2)',
                '=ROUND((N'.($index + 2).'-M'.($index + 2).')*K'.($index + 2).'*(1+L'.($index + 2).'/100),2)',
                '=O'.($index + 2).'+P'.($index + 2),
            ],
            $this->komponen,
            array_keys($this->komponen)
        );

        $firstDataRow = 2;
        $lastDataRow = count($rows) + 1;
        $totalRow = $lastDataRow + 1;
        $sum = fn (string $column) => $rows === []
            ? '=0'
            : "=SUM({$column}{$firstDataRow}:{$column}{$lastDataRow})";
        $rows[] = [
            '', '', '', '', '', '', 'TOTAL', '', '', '',
            '',
            '',
            $sum('M'),
            $sum('N'),
            $sum('O'),
            $sum('P'),
            "=O{$totalRow}+P{$totalRow}",
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Kegiatan',
            'Nama Kegiatan',
            'Kode Rekening',
            'Nama Akun',
            'ID Komponen',
            'Nama Komponen',
            'Spesifikasi',
            'Keterangan',
            'Satuan',
            'Harga Satuan',
            'PPN (%)',
            'Volume Sebelum',
            'Volume Sesudah',
            'Pagu Awal TW',
            'Penyesuaian Bersih',
            'Pagu TW Hasil Penyesuaian',
        ];
    }

    public function title(): string
    {
        return 'Rincian Komponen';
    }

    public function columnFormats(): array
    {
        return [
            'K' => '#,##0.00',
            'L' => '0.00',
            'M' => '0.00',
            'N' => '0.00',
            'O' => '#,##0;[Red]-#,##0',
            'P' => '#,##0;[Red]-#,##0',
            'Q' => '#,##0;[Red]-#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->komponen) + 2;
        $sheet->freezePane('A2');
        $sheet->getAutoFilter()->setRange("A1:Q{$lastRow}");
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getStyle("A1:Q{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$lastRow}:Q{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF111827']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
        ]);
        $sheet->getStyle("O2:Q{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(34);
        for ($row = 2; $row < $lastRow; $row += 2) {
            $sheet->getStyle("A{$row}:Q{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF8FAFC');
        }

        return [];
    }
}
