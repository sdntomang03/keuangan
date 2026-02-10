<?php

namespace App\Exports;

use App\Models\Rekanan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekananExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    /**
     * 1. Query Data
     * Mengambil data Rekanan hanya milik Sekolah yang sedang login.
     */
    public function query()
    {
        return Rekanan::query()
            ->where('sekolah_id', Auth::user()->sekolah_id)
            ->orderBy('nama_rekanan', 'asc');
    }

    /**
     * 2. Header Judul Kolom (Baris 1)
     */
    public function headings(): array
    {
        return [
            'Nama Rekanan',
            'No. Rekening',
            'Nama Bank',
            'NPWP',
            'PKP',
            'Alamat',
            'Alamat 2',
            'Kota',
            'Provinsi',
            'PIC',
            'Jabatan',
            'No. Telepon',
            'Nama Pimpinan',
            'Keterangan',
        ];
    }

    /**
     * 3. Mapping Data (Isi Baris)
     * Menentukan data apa yang masuk ke kolom mana.
     */
    public function map($rekanan): array
    {
        return [
            $rekanan->nama_rekanan,
            // Paksa string agar Excel tidak mengubah format (misal: 0812.. jadi 812..)
            "'".$rekanan->no_rekening,
            $rekanan->nama_bank,
            "'".$rekanan->npwp,
            (string) $rekanan->pkp,
            $rekanan->alamat,
            $rekanan->alamat_2,
            $rekanan->kota,
            $rekanan->provinsi,
            $rekanan->pic,
            $rekanan->jabatan,
            (string) $rekanan->no_telp,
            $rekanan->nama_pimpinan,
            $rekanan->ket,
        ];
    }

    /**
     * 4. Format Kolom Excel
     * PENTING: Mengatur kolom angka sensitif menjadi TEXT agar 0 di depan tidak hilang.
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER, // Kolom B: No. Rekening
            'D' => NumberFormat::FORMAT_NUMBER, // Kolom D: NPWP
            'E' => NumberFormat::FORMAT_TEXT, // Kolom E: PKP
            'L' => NumberFormat::FORMAT_TEXT, // Kolom L: No. Telp
        ];
    }

    /**
     * 5. Styling (Opsional)
     * Membuat Header (Baris 1) menjadi Tebal (Bold).
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
