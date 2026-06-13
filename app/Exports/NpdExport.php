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

    protected $triwulan;

    protected $namaSekolah;

    public function __construct($listNpd, $triwulan, $namaSekolah)
    {
        $this->listNpd = $listNpd;
        $this->triwulan = $triwulan;
        $this->namaSekolah = $namaSekolah;
    }

    /**
     * Judul Kolom Tabel (Sekarang akan berada di Baris 4)
     */
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
        // Data dimulai dari baris 5 karena:
        // Baris 1: Judul Utama
        // Baris 2: Sub-Judul (Triwulan & Sekolah)
        // Baris 3: Kosong (Pemisah)
        // Baris 4: Header Tabel
        $currentRow = 5;

        foreach ($this->listNpd as $npd) {
            $pagu = (float) ($npd->nilai_npd ?? 0);
            $realisasi = (float) ($npd->realisasi_nota ?? 0);

            // Formula Excel dinamis mengikuti baris
            $formulaSisa = "=E{$currentRow}-F{$currentRow}";
            $formulaStatus = "=IF(G{$currentRow}>0,\"STS\",\"Sesuai\")";

            $rows[] = [
                $npd->nomor_npd,
                $npd->tanggal ? $npd->tanggal->format('d/m/Y') : '-',
                $npd->kegiatan->namagiat ?? '-',
                $npd->korek->ket ?? '',
                $pagu,
                $realisasi,
                $formulaSisa,
                $formulaStatus,
            ];

            $currentRow++;
        }

        $lastDataRow = $currentRow - 1;
        $totalRow = $currentRow;

        // Baris Total Keseluruhan
        $rows[] = [
            '', '', '', 'TOTAL KESELURUHAN',
            "=SUM(E5:E{$lastDataRow})",
            "=SUM(F5:F{$lastDataRow})",
            "=SUM(G5:G{$lastDataRow})",
            '',
        ];

        return $rows;
    }

    public function columnFormats(): array
    {
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah,
            'F' => $formatRupiah,
            'G' => $formatRupiah,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. TAMBAHKAN JUDUL DI ATAS TABEL
        $sheet->insertNewRowBefore(1, 3); // Sisipkan 3 baris di awal

        $sheet->setCellValue('A1', 'LAPORAN MONITORING PENARIKAN DANA (NPD)');
        $sheet->setCellValue('A2', strtoupper($this->namaSekolah).' - TRIWULAN '.$this->triwulan);

        // Merge sel untuk judul agar ke tengah
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Style Judul Utama
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style Sub-Judul
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // 2. STYLE UNTUK HEADER TABEL (Baris 4)
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. STYLE UNTUK SELURUH TABEL (Border)
        $lastRow = count($this->listNpd) + 5;
        $sheet->getStyle('A4:H'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF4B5563'],
                ],
            ],
        ]);

        // 4. STYLE BARIS TOTAL
        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);

        // Perataan teks
        $sheet->getStyle('D'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B5:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H5:H'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
