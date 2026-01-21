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
    // Ubah dari $request menjadi $anggaran
    protected $anggaran;

    public function __construct($anggaran)
    {
        $this->anggaran = $anggaran;
    }

    public function collection()
    {
        // Filter langsung menggunakan anggaran_id yang aktif
        return Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis'])
            ->where('anggaran_id', $this->anggaran->id)
            ->get();
    }

    public function headings(): array
    {
        // Header tetap sama
        $headers = ['Program', 'Kegiatan', 'Sub Kegiatan', 'Keterangan', 'Kode Rekening', 'Id Komp', 'Nama Komponen', 'Spesifikasi', 'Satuan', 'Harga Satuan', 'Pajak'];

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

        // Logika status tetap sama
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

        for ($m = 1; $m <= 12; $m++) {
            $rowBulan = $item->akbRincis->firstWhere('bulan', $m);
            $data[] = $rowBulan ? $rowBulan->volume : 0;
        }

        $data[] = $totalVolRinci;
        $data[] = $item->totalharga;
        $data[] = $item->totalpajak;
        $data[] = $status;

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
