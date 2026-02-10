<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
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
            // PERUBAHAN 1: Menambahkan Kolom KEGIATAN dan KODE REKENING
            ['NO', 'TANGGAL', 'NO BUKTI', 'KEGIATAN', 'KODE REKENING', 'URAIAN', 'REKANAN', 'BRUTO', 'PPN', 'PPH', 'NETTO TRANSFER'],
        ];
    }

    public function map($b): array
    {
        static $no = 0;
        $no++;

        // PERUBAHAN 2: Logika mengambil Kegiatan & Rekening
        // Menggunakan map & unique untuk mengantisipasi jika 1 bukti bayar terdiri dari beberapa item kegiatan berbeda
        // Data digabung dengan new line (\n)

        $kegiatan = $b->rincis->map(function ($item) {
            return $item->rkas->kegiatan->namagiat ?? '-';
        })->unique()->implode("\n");

        $kodeRekening = $b->rincis->map(function ($item) {
            return $item->rkas->korek->ket ?? '-';
        })->unique()->implode("\n");

        return [
            $no,
            $b->tanggal,
            $b->no_bukti,
            $kegiatan,      // Kolom D
            $kodeRekening,  // Kolom E
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
        // PERUBAHAN 3: Merge Header diperlebar sampai K (11 Kolom)
        $sheet->mergeCells('A1:K1');

        // Agar text panjang (seperti nama kegiatan) turun ke bawah otomatis
        $sheet->getStyle('A:K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:K')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

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
                $lastRow = $this->belanja->count() + 3;
                $totalRow = $lastRow + 1;

                // --- TAMBAHAN: SEMBUNYIKAN KOLOM D DAN E ---
                $sheet->getColumnDimension('D')->setVisible(false);
                $sheet->getColumnDimension('E')->setVisible(false);
                // -------------------------------------------

                // 1. Merge Label TOTAL (A sampai G)
                // Meskipun D dan E disembunyikan, merge tetap aman
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');

                // 2. Rumus SUM
                $sheet->setCellValue("H{$totalRow}", "=SUM(H4:H{$lastRow})"); // Bruto
                $sheet->setCellValue("I{$totalRow}", "=SUM(I4:I{$lastRow})"); // PPN
                $sheet->setCellValue("J{$totalRow}", "=SUM(J4:J{$lastRow})"); // PPH
                $sheet->setCellValue("K{$totalRow}", "=SUM(K4:K{$lastRow})"); // Netto

                // 3. Styling Baris TOTAL
                $styleArray = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ];

                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray($styleArray);
                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Format Ribuan
                $sheet->getStyle("H4:K{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');

                // 5. Border Data
                $sheet->getStyle("A3:K{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
            },
        ];
    }
}
