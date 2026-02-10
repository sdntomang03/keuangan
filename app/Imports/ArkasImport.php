<?php

namespace App\Imports;

use App\Models\Arkas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArkasImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    protected $jenisBelanjaSelected;

    public function __construct($jenisBelanjaSelected = null)
    {
        $this->jenisBelanjaSelected = $jenisBelanjaSelected;
    }

    /**
     * 1. ToCollection: Kita terima data per 'Chunk' (Potongan)
     * bukan satu per satu baris.
     */
    public function collection(Collection $rows)
    {
        $dataToInsert = [];
        $now = now()->toDateTimeString(); // Format waktu untuk SQL

        foreach ($rows as $row) {
            // Validasi sederhana: Skip jika ID Barang kosong
            if (empty($row['id_barang']) && empty($row['idbarang'])) {
                continue;
            }

            // Logic Jenis Belanja
            $jenisBelanjaFix = $this->jenisBelanjaSelected ?? $row['jenisbelanja'] ?? $row['jenis_belanja'] ?? null;

            // Masukkan ke array (Bukan simpan ke DB dulu)
            $dataToInsert[] = [
                'id_barang' => $row['id_barang'] ?? $row['idbarang'] ?? null,
                'kode_rekening' => $row['kode_rekening'] ?? $row['koderekening'] ?? null,
                'nama_rekening' => $row['nama_rekening'] ?? $row['namarekening'] ?? null,
                'nama_barang' => $row['nama_barang'] ?? $row['namabarang'] ?? null,
                'satuan' => $row['satuan'] ?? null,

                // Cleaning Number
                'harga_barang' => $this->cleanNumber($row['harga_barang'] ?? $row['hargabarang']),
                'harga_minimal' => $this->cleanNumber($row['harga_minimal'] ?? $row['hargaminimal']),
                'harga_maksimal' => $this->cleanNumber($row['harga_maksimal'] ?? $row['hargamaksimal']),

                'kode_belanja' => $row['kode_belanja'] ?? $row['kodebelanja'] ?? null,
                'jenis_belanja' => $jenisBelanjaFix,

                // PENTING: Insert Raw tidak otomatis ngisi timestamp
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 2. Eksekusi Query Sekaligus
        // Jika ChunkSize 1000, maka ini 1 Query memasukkan 1000 data.
        if (! empty($dataToInsert)) {
            // Gunakan insert() biasa atau insertOrIgnore() jika takut duplikat ID
            Arkas::insert($dataToInsert);
        }
    }

    /**
     * 3. WithChunkReading: Membaca file Excel sedikit demi sedikit.
     * Ini mencegah error "Allowed Memory Size Exhausted".
     * Excel dibaca per 1.000 baris, diproses, lalu memori dibersihkan.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    private function cleanNumber($value)
    {
        if (! $value) {
            return 0;
        }

        return preg_replace('/[^0-9]/', '', $value);
    }
}
