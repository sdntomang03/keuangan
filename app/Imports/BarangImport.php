<?php

namespace App\Imports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class BarangImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts
{
    /**
     * Memetakan baris Excel ke Model Barang
     */
    public function model(array $row)
    {
        // Lewati baris jika ID kosong
        if (empty($row['id_barang'])) {
            return null;
        }

        return new Barang([
            'id_barang' => (string) $row['id_barang'],
            'kode_rekening' => (string) $row['kode_rekening'],
            'nama_rekening' => (string) $row['nama_rekening'],
            'nama_barang' => (string) $row['nama_barang'],
            'satuan' => (string) $row['satuan'],
            'harga_barang' => (int) ($row['harga_barang'] ?? 0),
            'harga_minimal' => (int) ($row['harga_minimal'] ?? 0),
            'harga_maksimal' => (int) ($row['harga_maksimal'] ?? 0),
            'kode_belanja' => (string) $row['kode_belanja'],
            'kategori' => (string) $row['kategori'],
            'digunakan_rkas' => filter_var($row['digunakan_rkas'], FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Tentukan kolom yang menjadi Primary Key untuk fungsi Upsert (Update jika ada, Insert jika baru)
     */
    public function uniqueBy()
    {
        return 'id_barang';
    }

    /**
     * Ukuran batch untuk disimpan ke database per query (Mencegah Limit Placeholder MySQL)
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * Ukuran baris Excel yang dibaca ke memory (Mencegah Out of Memory RAM)
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
