<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahan untuk format Text
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekananTemplateExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles
{
    /**
     * Mengembalikan contoh data (dummy)
     */
    public function collection()
    {
        return collect([
            [
                'CV. Maju Jaya',        // nama_rekanan
                '1234567890',           // no_rekening
                'Bank BCA',             // nama_bank
                '01.234.567.8-901.000', // npwp
                1,                      // pkp (1 = Ya, 0 = Tidak)
                'Jl. Sudirman No. 10',  // alamat
                'Blok A',               // alamat_2
                'Jakarta Selatan',      // kota
                'DKI Jakarta',          // provinsi
                'Budi Santoso',         // pic
                'Manager Marketing',    // jabatan
                '08123456789',          // no_telp
                'Bapak Pimpinan',       // nama_pimpinan
                'Suplier ATK',          // ket
            ],
            [
                'Toko Abadi',           // Contoh baris ke-2 (Non PKP)
                '0987654321',
                'Bank BRI',
                null,                   // NPWP Kosong
                0,                      // Non PKP
                'Jl. Merdeka',
                null,
                'Surabaya',
                'Jawa Timur',
                'Ani',
                'Admin',
                '081999888777',
                'Ibu Bos',
                'Jasa Service',
            ],
        ]);
    }

    /**
     * Judul Kolom (Header) - SESUAIKAN DENGAN IMPORT
     */
    public function headings(): array
    {
        return [
            'nama_rekanan',
            'no_rekening',
            'nama_bank',
            'npwp',
            'pkp',
            'alamat',
            'alamat_2',
            'kota',
            'provinsi',
            'pic',
            'jabatan',
            'no_telp',
            'nama_pimpinan',
            'ket',
        ];
    }

    /**
     * Format Kolom agar Angka dibaca sebagai Teks
     * (Supaya 0 di depan tidak hilang dan tidak jadi 1.2E+10)
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // Kolom No Rekening
            'D' => NumberFormat::FORMAT_TEXT, // Kolom NPWP
            'L' => NumberFormat::FORMAT_TEXT, // Kolom No Telp
        ];
    }

    /**
     * Styling Header
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 Bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
