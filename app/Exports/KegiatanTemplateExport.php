<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KegiatanTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * Mengembalikan data contoh (dummy) agar user mengerti format pengisian.
     */
    public function collection()
    {
        return collect([
            [
                'Standar Isi',           // snp
                'BOS',                   // sumber_dana
                '3.1',                   // kodedana
                'Pengembangan',          // namadana
                '1.1.1',                 // kodegiat
                'Penyusunan Kurikulum',  // namagiat
                'Kegiatan rapat penyusunan kurikulum sekolah tahun ajaran baru', // kegiatan
                '2026.BOS.001',          // idbl (WAJIB UNIK)
                'http://google.com',      // link
            ],
            [
                'Standar Proses',        // snp
                'BOP',                   // sumber_dana
                '4.2',                   // kodedana
                'Pemeliharaan',          // namadana
                '2.1.5',                 // kodegiat
                'Perbaikan Ringan',      // namagiat
                'Pengecatan ulang ruang kelas 1 dan 2', // kegiatan
                '2026.BOP.005',          // idbl (WAJIB UNIK)
                '-',                      // link
            ],
        ]);
    }

    /**
     * Judul Kolom (Header) Excel.
     * HARUS SAMA PERSIS dengan key yang dipakai di KegiatanImport.php
     */
    public function headings(): array
    {
        return [
            'snp',
            'sumber_dana',
            'kodedana',
            'namadana',
            'kodegiat',
            'namagiat',
            'kegiatan',
            'idbl',
            'link',
        ];
    }

    /**
     * Membuat baris header (baris 1) menjadi tebal (Bold).
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
