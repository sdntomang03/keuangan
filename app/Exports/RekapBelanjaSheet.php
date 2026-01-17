<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahkan ini
use Maatwebsite\Excel\Concerns\WithTitle;    // Tambahkan ini
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBelanjaSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $belanja;

    public function __construct($belanja)
    {
        $this->belanja = $belanja;
    }

    public function collection()
    {
        return $this->belanja;
    }

    public function title(): string
    {
        return 'REKAP BELANJA';
    }

    public function headings(): array
    {
        return [
            ['REKAPITULASI SELURUH BELANJA'],
            [''],
            ['NO', 'TANGGAL', 'NO BUKTI', 'URAIAN', 'REKANAN', 'BRUTO', 'PPN', 'PPH', 'NETTO TRANSFER'],
        ];
    }

    public function map($b): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $b->tanggal,
            $b->no_bukti,
            $b->uraian,
            $b->rekanan->nama_rekanan ?? '-',
            ($b->subtotal + $b->ppn),
            $b->ppn,
            $b->pph,
            $b->transfer,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->belanja->count() + 3; // +3 karena header ada 3 baris
                $totalRow = $lastRow + 1;

                // 1. Tambahkan Label TOTAL
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');

                // 2. Gunakan Rumus SUM Excel (F sampai I)
                $sheet->setCellValue("F{$totalRow}", "=SUM(F4:F{$lastRow})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G4:G{$lastRow})");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H4:H{$lastRow})");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I4:I{$lastRow})");

                // 3. Styling Baris TOTAL
                $styleArray = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']], // Kuning
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];

                $sheet->getStyle("A{$totalRow}:I{$totalRow}")->applyFromArray($styleArray);
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Format Ribuan (Currency) untuk seluruh kolom uang
                $sheet->getStyle("F4:I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');

                // 5. Border untuk seluruh data tabel
                $sheet->getStyle("A3:I{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
            },
        ];
    }
}
