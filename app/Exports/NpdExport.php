<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NpdExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles
{
    protected $listNpd;

    public function __construct($listNpd)
    {
        $this->listNpd = $listNpd;
    }

    public function headings(): array
    {
        return [
            'Nomor NPD',
            'Tanggal',
            'Kegiatan',
            'Kode Rekening',
            'Pagu NPD (A)',
            'Realisasi Spj (B)',
            'Sisa Dana (A-B)',
            'Status',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $currentRow = 2; // Baris data pertama di Excel (karena baris 1 digunakan untuk Header)

        foreach ($this->listNpd as $npd) {
            $pagu = (float) ($npd->nilai_npd ?? 0);
            $realisasi = (float) ($npd->realisasi_nota ?? 0);

            // 1. FORMULA PENGURANGAN EXCEL UNTUK SISA DANA (Kolom G = Kolom E - Kolom F)
            $formulaSisa = "=E{$currentRow}-F{$currentRow}";

            // 2. FORMULA IF EXCEL UNTUK STATUS (Jika Kolom G > 0 maka "STS", jika tidak maka "Sesuai")
            // Kita buat dinamis juga agar status ikut berubah jika angka di Excel diedit manual
            $formulaStatus = "=IF(G{$currentRow}>0,\"STS\",\"Sesuai\")";

            $rows[] = [
                $npd->nomor_npd,
                $npd->tanggal ? $npd->tanggal->format('d/m/Y') : '-',
                $npd->kegiatan->namagiat ?? '-',
                $npd->korek->ket ?? '',
                $pagu,
                $realisasi,
                $formulaSisa,   // Menggunakan rumus Excel
                $formulaStatus, // Menggunakan rumus Excel
            ];

            $currentRow++;
        }

        // Tentukan batas baris data terakhir untuk referensi rumus SUM
        $lastDataRow = $currentRow - 1;

        // 3. FORMULA SUM EXCEL UNTUK TOTAL KESELURUHAN (Baris paling bawah)
        $rows[] = [
            '', '', '', 'TOTAL KESELURUHAN',
            "=SUM(E2:E{$lastDataRow})", // SUM Pagu NPD
            "=SUM(F2:F{$lastDataRow})", // SUM Realisasi Spj
            "=SUM(G2:G{$lastDataRow})", // SUM Sisa Dana
            '',
        ];

        return $rows;
    }

    public function columnFormats(): array
    {
        // Format Accounting agar angka 0 tetap muncul sebagai "Rp 0"
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah,
            'F' => $formatRupiah,
            'G' => $formatRupiah,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->listNpd) + 2;

        // Style untuk Header (Baris 1)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk Border seluruh tabel
        $sheet->getStyle('A1:H'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF4B5563'],
                ],
            ],
        ]);

        // Style untuk Baris Total (Baris Terakhir)
        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Perataan Letak Teks
        $sheet->getStyle('D'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B2:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:H'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
