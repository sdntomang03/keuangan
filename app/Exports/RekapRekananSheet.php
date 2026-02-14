<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle; // Tambahkan ini
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapRekananSheet implements FromCollection, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithTitle
{
    protected $dataBelanja;

    protected $rekanan;

    private $rowNumber = 0;

    public function __construct($dataBelanja, $rekanan)
    {
        $this->dataBelanja = $dataBelanja;
        $this->rekanan = $rekanan;
    }

    public function collection()
    {
        return $this->dataBelanja;
    }

    /**
     * Tentukan cell di mana DATA (bukan heading) dimulai
     */
    public function startCell(): string
    {
        return 'A7';
    }

    public function title(): string
    {
        return 'REKAP';
    }

    public function headings(): array
    {
        // Heading diletakkan di baris ke-6 secara manual via registerEvents
        // atau biarkan headings() mengembalikan baris ke-6 saja.
        return [
            ['NO', 'TANGGAL', 'NO. BUKTI', 'KEGIATAN', 'KODE REKENING', 'NILAI SPJ (Rp)', 'PPN (Rp)', 'PPH (Rp)', 'NETTO (Rp)'],
        ];
    }

    public function map($belanja): array
    {
        $this->rowNumber++;

        $kegiatan = $belanja->rincis->map(function ($item) {
            return $item->rkas->kegiatan->namagiat ?? '-';
        })->unique()->implode("\n");

        $kodeRekening = $belanja->rincis->map(function ($item) {
            return $item->rkas->korek->ket ?? '-';
        })->unique()->implode("\n");

        $totalPph = $belanja->pajaks->where('masterPajak.nama_pajak', '!=', 'PPN')->sum('nominal');
        $bruto = $belanja->subtotal + $belanja->ppn;
        $netto = $bruto - $totalPph;

        return [
            $this->rowNumber,
            Carbon::parse($belanja->tanggal)->translatedFormat('d F Y'),
            $belanja->no_bukti,
            $kegiatan,
            $kodeRekening,
            $bruto,
            $belanja->ppn,
            $totalPph,
            $netto,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,  'B' => 18, 'C' => 20, 'D' => 40,
            'E' => 30, 'F' => 18, 'G' => 15, 'H' => 15, 'I' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Karena startCell di A7, maka Header ada di Baris 6
                $headerRow = 7;
                $startDataRow = 7;
                $lastDataRow = $sheet->getHighestRow();
                $footerRow = $lastDataRow + 1;

                // Memindahkan Heading ke baris 6 jika Laravel Excel menaruhnya di A1 secara default
                // Tapi dengan startCell A7, Heading otomatis ditaruh di baris 6 oleh library.

                // --- 1. STYLE JUDUL UTAMA (BIRU) ---
                $sheet->mergeCells('A1:I1');
                $sheet->getRowDimension('1')->setRowHeight(35);
                $sheet->setCellValue('A1', 'REKAP BELANJA - '.strtoupper($this->rekanan->nama_rekanan));
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // --- 2. INFO BANK (KUNING) ---
                $sheet->mergeCells('G2:H2');
                $sheet->setCellValue('G2', 'Nama Bank :');
                $sheet->mergeCells('G3:H3');
                $sheet->setCellValue('G3', 'No. Rekening :');
                $sheet->mergeCells('G4:H4');
                $sheet->setCellValue('G4', 'NPWP :');

                $sheet->setCellValueExplicit('I2', $this->rekanan->nama_bank ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('I3', $this->rekanan->no_rekening ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('I4', $this->rekanan->npwp ?? '-', DataType::TYPE_STRING);

                $sheet->getStyle('G2:I4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                // --- 3. STYLE HEADER TABEL (Baris 6) ---
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // --- 4. FORMATTING DATA ---
                $sheet->getStyle("A{$startDataRow}:I{$lastDataRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                $sheet->getStyle("F{$startDataRow}:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A{$startDataRow}:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- 5. FOOTER TOTAL ---
                $sheet->mergeCells("A{$footerRow}:E{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'TOTAL KESELURUHAN');

                foreach (['F', 'G', 'H', 'I'] as $col) {
                    $sheet->setCellValue("{$col}{$footerRow}", "=SUM({$col}{$startDataRow}:{$col}{$lastDataRow})");
                }

                $sheet->getStyle("A{$footerRow}:E{$footerRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                $sheet->getStyle("F{$footerRow}:I{$footerRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9EAD3']],
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->getStyle("F{$footerRow}:I{$footerRow}")->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
