<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file CSV
        $file = database_path('seeders/csv/rekanan.csv');

        if (! file_exists($file)) {
            $this->command->error("File tidak ditemukan di: $file");

            return;
        }

        $fileHandle = fopen($file, 'r');

        // Lewati header (ket;kode;uraian_singkat;singkat)
        fgetcsv($fileHandle, 0, ';');

        DB::beginTransaction();
        try {
            $count = 0;
            while (($data = fgetcsv($fileHandle, 0, ';')) !== false) {
                // Skip baris kosong atau jika kolom kode ($data[1]) tidak ada
                if (empty($data) || ! isset($data[1]) || $data[1] == '') {
                    continue;
                }

                // Menggunakan updateOrInsert agar jika seeder dijalankan ulang tidak error karena duplicate 'kode'
                DB::table('rekanans')->insert([
                    // 1. ID Sekolah (Wajib Dinamis)
                    'sekolah_id' => 1,

                    // 2. Mapping Data Sesuai Urutan Kolom Excel
                    'nama_rekanan' => $data[0] ?? null,
                    'no_rekening' => $data[1] ?? null,
                    'nama_bank' => $data[2] ?? null,
                    'npwp' => $data[3] ?? null,
                    'pkp' => $data[4] ?? null,
                    'alamat' => $data[5] ?? null,
                    'alamat2' => $data[6] ?? null, // Excel: alamat_2 -> DB: alamat2
                    'kota' => $data[7] ?? null,
                    'provinsi' => $data[8] ?? null,
                    'pic' => $data[9] ?? null,
                    'jabatan' => $data[10] ?? null,
                    'no_telp' => $data[11] ?? null, // Posisi No Telp di sini
                    'nama_pimpinan' => $data[12] ?? null,
                    'ket' => $data[13] ?? null,

                    // 3. Timestamp
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }

            DB::commit();
            $this->command->info("Berhasil mengimpor $count data ke tabel rekanan.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal: '.$e->getMessage());
        }

        fclose($fileHandle);
    }
}
