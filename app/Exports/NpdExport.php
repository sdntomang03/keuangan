<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; // 1. Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// 2. Tambahkan implement "WithColumnFormatting" di bawah ini
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

        foreach ($this->listNpd as $npd) {
            $realisasi = $npd->realisasi_nota ?? 0;
            $sisa = $npd->nilai_npd - $realisasi;
            $status = $sisa > 0 ? 'STS' : 'Sesuai';

            $rows[] = [
                $npd->nomor_npd,
                $npd->tanggal->format('d/m/Y'),
                $npd->kegiatan->namagiat ?? '-',
                $npd->korek->ket ?? '',
                $npd->nilai_npd, // Pastikan ini murni angka (jangan pakai number_format di sini)
                $realisasi,      // Murni angka
                $sisa,           // Murni angka
                $status,
            ];
        }

        // Baris Total di paling bawah
        $totalPagu = $this->listNpd->sum('nilai_npd');
        $totalRealisasi = $this->listNpd->sum('realisasi_nota');
        $totalSisa = $totalPagu - $totalRealisasi;

        $rows[] = [
            '', '', '', 'TOTAL',
            $totalPagu,
            $totalRealisasi,
            $totalSisa,
            '',
        ];

        return $rows;
    }

    // 3. Tambahkan fungsi ini untuk memformat kolom E, F, dan G
    public function columnFormats(): array
    {
        // Penjelasan Format Excel: [Positif] ; [Negatif] ; [Nol] ; [Teks]
        // Pada bagian [Nol] (urutan ketiga), kita paksa agar memunculkan angka 0
        $formatRupiah = '_("Rp"* #,##0_);_("Rp"* -#,##0_);_("Rp"* 0_);_(@_)';

        return [
            'E' => $formatRupiah, // Kolom Pagu NPD (A)
            'F' => $formatRupiah, // Kolom Realisasi Spj (B)
            'G' => $formatRupiah, // Kolom Sisa Dana (A-B)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->listNpd) + 2;

        return [
            1 => ['font' => ['bold' => true]],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}
