<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// PERHATIKAN: Saya menghapus "WithHeadings" karena kita akan membuat header secara manual
class NpdExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithStyles
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

    public function array(): array
    {
        $rows = [];

        // Baris 1: Judul Utama
        $rows[] = ['LAPORAN MONITORING PENARIKAN DANA (NPD)', '', '', '', '', '', '', ''];

        // Baris 2: Sub Judul
        $rows[] = [strtoupper($this->namaSekolah).' - TRIWULAN '.$this->triwulan, '', '', '', '', '', '', ''];

        // Baris 3: Pemisah Kosong
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Baris 4: Header Tabel
        $rows[] = [
            'Nomor NPD',
            'Tanggal',
            'Kegiatan',
            'Kode Rekening',
            'Pagu NPD (A)',
            'Realisasi Spj (B)',
            'Sisa Dana (A-B)',
            'Status',
        ];

        // Baris 5: Data Mulai dari Sini
        $currentRow = 5;

        foreach ($this->listNpd as $npd) {
            $pagu = (float) ($npd->nilai_npd ?? 0);
            $realisasi = (float) ($npd->realisasi_nota ?? 0);

            // Rumus sekarang dijamin tidak akan bergeser nilainya
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

        // Baris Total Keseluruhan (Jika datanya ada)
        if ($lastDataRow >= 5) {
            $rows[] = [
                '', '', '', 'TOTAL KESELURUHAN',
                "=SUM(E5:E{$lastDataRow})",
                "=SUM(F5:F{$lastDataRow})",
                "=SUM(G5:G{$lastDataRow})",
                '',
            ];
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        // Format Accounting
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah,
            'F' => $formatRupiah,
            'G' => $formatRupiah,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Total keseluruhan baris (Data + 4 baris awal + 1 baris total)
        $lastRow = count($this->listNpd) + 5;

        // 1. Merge sel untuk Judul (Baris 1 dan 2)
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

        // 2. Style Header Tabel (Baris 4)
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

        // 3. Style Border (Mulai dari baris 4 sampai bawah)
        $sheet->getStyle('A4:H'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF4B5563'],
                ],
            ],
        ]);

        // 4. Style Baris Total Keseluruhan (Paling Bawah)
        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);

        // Perataan Khusus (Tengah & Kanan)
        $sheet->getStyle('D'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if ($lastRow > 4) {
            $sheet->getStyle('B5:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H5:H'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
