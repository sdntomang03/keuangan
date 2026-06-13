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

        // Buat variabel penampung untuk menghitung total secara manual
        $totalPagu = 0;
        $totalRealisasi = 0;
        $totalSisa = 0;

        foreach ($this->listNpd as $npd) {
            // Paksa konversi ke float agar tidak ada nilai null yang merusak perhitungan
            $pagu = (float) ($npd->nilai_npd ?? 0);
            $realisasi = (float) ($npd->realisasi_nota ?? 0);
            $sisa = $pagu - $realisasi;
            $status = $sisa > 0 ? 'STS' : 'Sesuai';

            // Tambahkan ke grand total
            $totalPagu += $pagu;
            $totalRealisasi += $realisasi;
            $totalSisa += $sisa;

            $rows[] = [
                $npd->nomor_npd,
                $npd->tanggal ? $npd->tanggal->format('d/m/Y') : '-',
                $npd->kegiatan->namagiat ?? '-',
                $npd->korek->ket ?? '',
                $pagu,
                $realisasi,
                $sisa,
                $status,
            ];
        }

        // Baris Total di paling bawah menggunakan hasil hitungan manual
        $rows[] = [
            '', '', '', 'TOTAL KESELURUHAN',
            $totalPagu,
            $totalRealisasi,
            $totalSisa,
            '',
        ];

        return $rows;
    }

    public function columnFormats(): array
    {
        // Memaksa angka 0 tetap muncul sebagai "Rp 0" dan bukan strip (-)
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah,
            'F' => $formatRupiah,
            'G' => $formatRupiah,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Hitung total baris (Data + 1 baris Header + 1 baris Total)
        $lastRow = count($this->listNpd) + 2;

        // 1. Style untuk Header (Baris 1)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Teks Putih
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'], // Background Abu-abu gelap (Gray-800)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Style untuk seluruh Tabel (Memberikan Border)
        $sheet->getStyle('A1:H'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF4B5563'], // Warna border abu-abu
                ],
            ],
        ]);

        // 3. Style untuk Baris Total (Baris Paling Bawah)
        $sheet->getStyle('A'.$lastRow.':H'.$lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'], // Background abu-abu terang
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Rata Kanan khusus untuk teks "TOTAL KESELURUHAN"
        $sheet->getStyle('D'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 4. Rata Tengah (Center) untuk kolom Tanggal dan Status
        $sheet->getStyle('B2:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:H'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
