<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NpdExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    protected $listNpd;

    // Menerima data dari Controller
    public function __construct($listNpd)
    {
        $this->listNpd = $listNpd;
    }

    // Mengatur Judul Kolom (Header)
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

    // Mengatur isi data baris demi baris
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
                $npd->nilai_npd,
                $realisasi,
                $sisa,
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

    // Opsional: Membuat tulisan Header dan Baris Total menjadi Bold
    public function styles(Worksheet $sheet)
    {
        // Hitung baris terakhir (Jumlah data + 1 baris header + 1 baris total)
        $lastRow = count($this->listNpd) + 2;

        return [
            1 => ['font' => ['bold' => true]],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}
