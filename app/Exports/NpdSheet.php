<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NpdSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $dataBelanja;

    public function __construct($dataBelanja)
    {
        $this->dataBelanja = $dataBelanja;
    }

    public function title(): string
    {
        return 'REKAP NPD';
    }

    public function headings(): array
    {
        return [
            ['REKAPITULASI PER KEGIATAN & KODE REKENING'],
            [''],
            ['NO', 'URAIAN KEGIATAN / KODE REKENING', 'JUMLAH (Rp)'],
        ];
    }

    public function collection()
    {
        // 1. Kumpulkan semua item rincian
        $allItems = $this->dataBelanja->flatMap(function ($belanja) {
            return $belanja->rincis;
        });

        $output = collect([]);
        $no = 1;
        $grandTotal = 0;

        // ==========================================
        // BAGIAN 1: REKAP PER KEGIATAN
        // ==========================================

        // Grouping berdasarkan Nama Kegiatan
        $groupedData = $allItems->groupBy(function ($item) {
            return $item->rkas->kegiatan->namagiat ?? 'Tanpa Kegiatan';
        });

        foreach ($groupedData as $namaKegiatan => $itemsPerKegiatan) {

            // A. Header Kegiatan
            $output->push([
                'no' => $no++,
                'uraian' => $namaKegiatan,
                'nominal' => '',
                'type' => 'header_kegiatan',
            ]);

            // Grouping Kode Rekening di dalam Kegiatan
            $rekeningGroup = $itemsPerKegiatan->groupBy(function ($item) {
                return $item->rkas->korek->ket ?? '-';
            });

            $subTotalKegiatan = 0;

            // B. Rincian Rekening (Indented)
            foreach ($rekeningGroup as $namaRekening => $itemsPerRekening) {
                $totalRekening = $itemsPerRekening->sum('total_bruto');
                $subTotalKegiatan += $totalRekening;

                $output->push([
                    'no' => '',
                    'uraian' => '   '.$namaRekening, // Indentasi Spasi
                    'nominal' => $totalRekening,
                    'type' => 'item_rekening',
                ]);
            }

            // C. Subtotal per Kegiatan
            $output->push([
                'no' => '',
                'uraian' => "Subtotal $namaKegiatan",
                'nominal' => $subTotalKegiatan,
                'type' => 'subtotal',
            ]);

            // Baris Kosong Pemisah
            $output->push(['', '', '']);

            $grandTotal += $subTotalKegiatan;
        }

        // D. Grand Total Bagian 1
        $output->push([
            'no' => '',
            'uraian' => 'TOTAL KESELURUHAN (PER KEGIATAN)',
            'nominal' => $grandTotal,
            'type' => 'grand_total',
        ]);

        // ==========================================
        // BAGIAN 2: REKAP GLOBAL KODE REKENING (BARU)
        // ==========================================

        // Tambah Jarak Baris
        $output->push(['', '', '']);
        $output->push(['', '', '']);

        // Judul Bagian 2
        $output->push([
            'no' => '',
            'uraian' => 'REKAPITULASI PER KODE REKENING (GLOBAL)',
            'nominal' => '',
            'type' => 'header_global_rekening', // Penanda styling baru
        ]);

        // Grouping Data Global berdasarkan Nama Rekening (Abaikan Kegiatan)
        $globalRekening = $allItems->groupBy(function ($item) {

            $ket = $item->rkas->korek->ket ?? '-';

            // Gabung kode & ket biar unik dan urut
            return "$ket";
        })->sortKeys();

        $noRek = 1;
        $grandTotalRekening = 0;

        foreach ($globalRekening as $namaRekening => $items) {
            $total = $items->sum('total_bruto');
            $grandTotalRekening += $total;

            $output->push([
                'no' => $noRek++,
                'uraian' => $namaRekening,
                'nominal' => $total,
                'type' => 'item_rekening_global',
            ]);
        }

        // Total Akhir Bagian 2
        $output->push([
            'no' => '',
            'uraian' => 'TOTAL KESELURUHAN (PER REKENING)',
            'nominal' => $grandTotalRekening,
            'type' => 'grand_total', // Pakai style yang sama
        ]);

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C1');

        // Style Dasar Judul & Header
        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        $rows = $this->collection();
        $currentRow = 4;

        foreach ($rows as $row) {
            if (isset($row['type'])) {

                // Style Header Kegiatan
                if ($row['type'] === 'header_kegiatan') {
                    $styles[$currentRow] = [
                        'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
                    ];

                    // Style Header Global Rekening (Bagian 2)
                } elseif ($row['type'] === 'header_global_rekening') {
                    // Merge Cell Judul Bagian 2
                    $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                    $styles[$currentRow] = [
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']], // Biru Tua
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ];

                    // Style Subtotal
                } elseif ($row['type'] === 'subtotal') {
                    $styles[$currentRow] = [
                        'font' => ['bold' => true, 'italic' => true],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ];
                    $sheet->getStyle("C$currentRow")->getNumberFormat()->setFormatCode('#,##0');

                    // Style Grand Total
                } elseif ($row['type'] === 'grand_total') {
                    $styles[$currentRow] = [
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]],
                    ];
                    $sheet->getStyle("C$currentRow")->getNumberFormat()->setFormatCode('#,##0');

                    // Style Item Biasa & Global
                } elseif ($row['type'] === 'item_rekening' || $row['type'] === 'item_rekening_global') {
                    $sheet->getStyle("C$currentRow")->getNumberFormat()->setFormatCode('#,##0');
                }
            }
            $currentRow++;
        }

        // Sembunyikan Kolom D (Jika memang perlu)
        $sheet->getColumnDimension('D')->setVisible(false);

        // Border All Data (Hitung baris terakhir)
        $lastRow = $currentRow - 1;
        $sheet->getStyle("A3:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        return $styles;
    }
}
