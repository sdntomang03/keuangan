<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings; // Agar lebar kolom otomatis
use Maatwebsite\Excel\Concerns\WithStyles;     // Untuk styling header (opsional)
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekananTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * Mengembalikan contoh data (dummy) agar user paham formatnya
     */
    public function collection()
    {
        return collect([
            [
                'CV. Maju Jaya',    // nama_rekanan
                '1234567890',       // no_rekening
                'Bank BCA',         // nama_bank
                '123456789', // npwp
            ],
        ]);
    }

    /**
     * Judul Kolom (Header) - WAJIB SAMA dengan Import
     */
    public function headings(): array
    {
        return [
            'nama_rekanan',
            'no_rekening',
            'nama_bank',
            'npwp',
        ];
    }

    /**
     * Styling Header agar tebal (Bold)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris ke-1 di-bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
