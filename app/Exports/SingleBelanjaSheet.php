<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SingleBelanjaSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithTitle
{
    protected $belanja;

    public function __construct($belanja)
    {
        $this->belanja = $belanja;
    }

    public function collection()
    {
        return $this->belanja->rincis;
    }

    public function title(): string
    {
        // 1. Gabungkan kode singkat dan nomor bukti
        $name = $this->belanja->korek->singkat.'-'.$this->belanja->no_bukti;

        // 2. Bersihkan karakter yang dilarang oleh Excel
        $cleanName = str_replace(['/', '\\', '*', ':', '?', '[', ']'], '-', $name);

        // 3. Potong maksimal 31 karakter agar tidak error
        return substr($cleanName, 0, 31);
    }

    public function headings(): array
    {
        return [
            ['RINCIAN BELANJA BKU'],
            ['Nomor Bukti:', $this->belanja->no_bukti],
            ['Tanggal:', $this->belanja->tanggal],
            ['Rekanan:', $this->belanja->rekanan->nama_rekanan ?? '-'],
            ['Korek:', $this->belanja->korek->ket ?? '-'],
            [''],
            ['NO', 'KOMPONEN', 'SPESIFIKASI', 'QTY', 'SATUAN', 'HARGA SATUAN', 'TOTAL HARGA'],
        ];
    }

    public function map($rinci): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $rinci->namakomponen,
            $rinci->spek,
            $rinci->volume,
            $rinci->rkas->satuan ?? $rinci->satuan ?? '-', // Ambil dari RKAS
            $rinci->harga_satuan,
            $rinci->volume * $rinci->harga_satuan,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 7 + $this->belanja->rincis->count();
                $footerRow = $lastRow + 1;

                $sheet->mergeCells('A1:G1');

                // 2. STYLE JUDUL (Font Besar & Center)
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16, // Ukuran font lebih besar
                        'color' => ['rgb' => '1F4E78'], // Biru Tua Profesional
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                // 1. STYLING HEADER TABEL (Baris ke-6)
                $sheet->getStyle('A7:G7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // 2. BORDER PADA TABEL
                $sheet->getStyle('A7:G'.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // 3. FORMAT RUPIAH PADA KOLOM HARGA
                $sheet->getStyle('F7:G'.($footerRow + 5))->getNumberFormat()->setFormatCode('#,##0');

                // 4. FOOTER (Ringkasan Pajak & Transfer)
                $footerData = [
                    'SUBTOTAL' => $this->belanja->subtotal,
                    'PPN (11%)' => $this->belanja->ppn,
                    'PPh' => $this->belanja->pph,
                    'BRUTO (Kwitansi)' => ($this->belanja->subtotal + $this->belanja->ppn),
                    'NETTO TRANSFER' => $this->belanja->transfer,
                ];
                $currentRow = $footerRow;
                $firstFooterRow = $footerRow; // Simpan baris awal footer untuk styling blok

                foreach ($footerData as $label => $value) {
                    $sheet->setCellValue("F{$currentRow}", $label);
                    $sheet->setCellValue("G{$currentRow}", $value);

                    // Style standar untuk setiap baris footer (Border dan Bold Label)
                    $sheet->getStyle("F{$currentRow}:G{$currentRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Format khusus untuk baris NETTO TRANSFER (Warna Hijau & Bold)
                    if ($label == 'NETTO TRANSFER') {
                        $sheet->getStyle("F{$currentRow}:G{$currentRow}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D9EAD3'],
                            ],
                        ]);
                    } else {
                        // Font tebal hanya untuk label di kolom F (selain Netto)
                        $sheet->getStyle("F{$currentRow}")->getFont()->setBold(true);
                    }

                    $currentRow++;
                }

                // Tambahan: Pastikan kolom angka di footer rata kanan dan berformat ribuan
                $lastFooterRow = $currentRow - 1;
                $sheet->getStyle("G{$firstFooterRow}:G{$lastFooterRow}")->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->getStyle("G{$firstFooterRow}:G{$lastFooterRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }
}
