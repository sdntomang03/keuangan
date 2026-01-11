<?php

namespace App\Exports;

use App\Models\Rkas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RincianAkbExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        // Logika query SAMA dengan yang ada di Controller agar data sinkron
        return Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis'])
            ->when($this->request->tahun, function ($q) {
                return $q->where('tahun', $this->request->tahun);
            })
            ->when($this->request->jenis_anggaran, function ($q) {
                return $q->where('jenis_anggaran', $this->request->jenis_anggaran);
            })
            ->get();
    }

    public function headings(): array
    {
        // Header sesuai dengan tabel di View Anda
        $headers = ['Program', 'Kegiatan', 'Sub Kegiatan', 'Keterangan', 'Kode Rekening', 'Id Komp', 'Nama Komponen', 'Spesifikasi', 'Satuan', 'Harga Satuan',  'Pajak'];

        for ($i = 1; $i <= 12; $i++) {
            $headers[] = 'Bln '.$i;
        }

        $headers[] = 'Total Vol';
        $headers[] = 'Total';
        $headers[] = 'Jumlah Pajak';
        $headers[] = 'Status';

        return $headers;
    }

    public function map($item): array
    {
        $volAkb = $item->akb->volume ?? 0;
        $totalVolRinci = $item->akbRincis->sum('volume');
        $selisih = $volAkb - $totalVolRinci;

        // Menentukan teks status
        if ($selisih == 0 && $volAkb > 0) {
            $status = 'MATCH '.$volAkb;
        } elseif ($selisih > 0) {
            $status = 'SELISIH ('.number_format($selisih, 2).')';
        } elseif ($volAkb == 0 && $totalVolRinci == 0) {
            $status = 'EMPTY';
        } else {
            $status = 'OVER';
        }
        $statusPajak = ($item->totalpajak > 0) ? 'PPN' : '';
        // Data Dasar
        $data = [
            $item->kegiatan->snp ?? '-',
            $item->kegiatan->namagiat,
            $item->giatsubteks ?? '-',
            $item->keterangan ?? '-',
            $item->korek->singkat ?? '-',
            $item->idkomponen ?? '-',
            $item->namakomponen ?? '-',
            $item->spek ?? '-',
            $item->satuan ?? '-',
            $item->hargasatuan ?? '-',
            $statusPajak,
        ];

        // Loop Volume Bulanan
        for ($m = 1; $m <= 12; $m++) {
            $rowBulan = $item->akbRincis->firstWhere('bulan', $m);
            $data[] = $rowBulan ? $rowBulan->volume : 0;
        }

        // Data Akhir
        $data[] = $totalVolRinci;
        $data[] = $item->totalharga;
        $data[] = $item->totalpajak;
        $data[] = $status;

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold header baris pertama
        ];
    }
}
