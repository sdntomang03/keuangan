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

    protected $rowNumber = 0;

    public function __construct($belanja)
    {
        $this->belanja = $belanja;
        $this->rowNumber = 0;
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
        $this->rowNumber++;
        $currentContentRow = 7 + $this->rowNumber;

        return [
            $this->rowNumber,
            $rinci->namakomponen,
            $rinci->spek,
            $rinci->volume,
            $rinci->rkas->satuan ?? $rinci->satuan ?? '-', // Ambil dari RKAS
            $rinci->harga_satuan,
            "=D{$currentContentRow}*F{$currentContentRow}",
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. CONFIG BARIS ---
                $startDataRow = 8;
                $countData = $this->belanja->rincis->count();
                $lastDataRow = $startDataRow + $countData - 1;
                $currentRow = $lastDataRow + 1;

                // --- 2. STYLE JUDUL ---
                $sheet->mergeCells('A1:G1');
                $sheet->getRowDimension('1')->setRowHeight(35);
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // --- 3. FOOTER KALKULASI ---

                // SUBTOTAL
                $sheet->setCellValue("F{$currentRow}", 'Subtotal Belanja');
                $sheet->setCellValue("G{$currentRow}", "=SUM(G{$startDataRow}:G{$lastDataRow})");
                $subtotalRow = $currentRow;
                $currentRow++;

                // PPN
                $sheet->setCellValue("F{$currentRow}", 'PPN');
                $sheet->setCellValue("G{$currentRow}", $this->belanja->ppn ?? 0);
                $ppnRow = $currentRow;
                $currentRow++;

                // NILAI BRUTO (Sesuai Box Biru di Web)
                $brutoRow = $currentRow;
                $sheet->setCellValue("F{$brutoRow}", 'Nilai SPJ (Kwitansi)');
                $sheet->setCellValue("G{$brutoRow}", "=G{$subtotalRow}+G{$ppnRow}");
                $sheet->getStyle("F{$brutoRow}:G{$brutoRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0000FF'));
                $currentRow++;

                // --- LOOPING RINCIAN POTONGAN PAJAK (PPh) ---
                $firstPphRow = $currentRow;
                $listPajak = $this->belanja->pajaks; // Data dari relasi pajaks.masterPajak
                $pphcek = false;
                if ($listPajak && $listPajak->count() > 0) {
                    foreach ($listPajak as $pajak) {
                        // Mengambil nama dari relasi masterPajak sesuai tampilan web
                        $namaPajak = $pajak->masterPajak->nama_pajak ?? 'Potongan Pajak';
                        if (strtoupper($namaPajak) === 'PPN') {
                            continue;
                        }
                        $sheet->setCellValue("F{$currentRow}", $namaPajak);
                        $sheet->setCellValue("G{$currentRow}", $pajak->nominal);
                        $currentRow++;
                        $pphcek = true;
                    }
                } else {
                    // Jika tidak ada pajak, tampilkan satu baris PPh 0
                    $sheet->setCellValue("F{$currentRow}", 'Total PPh');
                    $sheet->setCellValue("G{$currentRow}", 0);
                    $pphcek = false;
                    $currentRow++;
                }
                $lastPphRow = $currentRow - 1;

                // NETTO / TRANSFER (Sesuai Emerald di Web)
                $transferRow = $currentRow;
                $sheet->setCellValue("F{$transferRow}", 'Transfer Rekanan');
                // Rumus: Bruto dikurangi jumlah seluruh baris pajak di atasnya
                if ($pphcek) {
                    $sheet->setCellValue("G{$transferRow}", "=G{$brutoRow}-SUM(G{$ppnRow}:G{$lastPphRow})");
                } else {
                    // Jika hanya ada PPN, langsung kurangi saja tanpa SUM
                    $sheet->setCellValue("G{$transferRow}", "=G{$brutoRow}-G{$ppnRow}");
                }

                // --- 4. FINAL STYLING ---

                // Area Hitam (A-E) mengikuti tinggi footer secara dinamis
                $sheet->mergeCells("A{$subtotalRow}:E{$transferRow}");
                $sheet->getStyle("A{$subtotalRow}:E{$transferRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);

                // Header Tabel
                $sheet->getStyle('A7:G7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Border seluruh tabel & Footer
                $sheet->getStyle("A7:G{$transferRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Format Rupiah & Alignment
                $sheet->getStyle("F7:G{$transferRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("F{$subtotalRow}:F{$transferRow}")->getFont()->setBold(true);
                $sheet->getStyle("G{$subtotalRow}:G{$transferRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Highlight baris TRANSFER
                $sheet->getStyle("F{$transferRow}:G{$transferRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9EAD3']],
                ]);
            },
        ];
    }
}
