<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BelanjaExport implements WithMultipleSheets
{
    protected $dataBelanja;

    protected $periodeText; // Tambahan properti untuk teks periode

    // Tambahkan parameter kedua dengan nilai default
    public function __construct($dataBelanja, $periodeText = 'Satu Tahun Anggaran')
    {
        $this->dataBelanja = $dataBelanja;
        $this->periodeText = $periodeText;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Rekap Seluruh Belanja (Per Bukti)
        // Kita lempar $periodeText agar bisa dijadikan Judul/Kop di dalam Excel
        $sheets[] = new RekapBelanjaSheet($this->dataBelanja, $this->periodeText);

        // Sheet 2: REKAP NPD (Per Kegiatan & Rekening)
        $sheets[] = new NpdSheet($this->dataBelanja, $this->periodeText);

        // Sheet 3 dst: Detail per Transaksi
        foreach ($this->dataBelanja as $belanja) {
            $sheets[] = new SingleBelanjaSheet($belanja);
        }

        return $sheets;
    }
}
