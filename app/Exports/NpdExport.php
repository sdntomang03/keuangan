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

class NpdExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected $listNpd;

    protected $triwulan;

    protected $namaSekolah;

    protected $nomorNpd; // Tambahkan properti ini

    // Update Constructor
    public function __construct($listNpd, $triwulan, $namaSekolah, $nomorNpd = null)
    {
        $this->listNpd = $listNpd;
        $this->triwulan = $triwulan;
        $this->namaSekolah = $namaSekolah;
        $this->nomorNpd = $nomorNpd;
    }

    public function array(): array
    {
        $rows = [];

        // Baris 1: Judul Utama[cite: 5]
        $rows[] = ['LAPORAN MONITORING PENARIKAN DANA (NPD)', '', '', '', '', '', '', ''];

        // Baris 2: Sub Judul (Beri Keterangan Jika Difilter)
        $subJudul = strtoupper($this->namaSekolah).' - TRIWULAN '.$this->triwulan;
        if ($this->nomorNpd) {
            $subJudul .= ' (FILTER NOMOR: '.$this->nomorNpd.')';
        }
        $rows[] = [$subJudul, '', '', '', '', '', '', ''];

        // Baris 3: Pemisah Kosong[cite: 5]
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Baris 4: Header Tabel[cite: 5]
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

        // Baris 5: Data Mulai dari Sini[cite: 5]
        $currentRow = 5;

        foreach ($this->listNpd as $npd) {
            $pagu = (float) ($npd->nilai_npd ?? 0);
            $realisasi = (float) ($npd->realisasi_nota ?? 0);

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

        // Baris Total Keseluruhan (Jika datanya ada)[cite: 5]
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
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah,
            'F' => $formatRupiah,
            'G' => $formatRupiah,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->listNpd) + 5;

        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

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

        $sheet->getStyle('A4:H'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF4B5563'],
                ],
            ],
        ]);

        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);

        $sheet->getStyle('D'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if ($lastRow > 4) {
            $sheet->getStyle('B5:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H5:H'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
