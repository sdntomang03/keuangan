<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KorekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file CSV
        $file = database_path('seeders/csv/korek.csv');

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
                DB::table('koreks')->updateOrInsert(
                    ['kode' => $data[1]], // Cek berdasarkan kode unik
                    [
                        'ket' => $data[0] ?? null,
                        'uraian_singkat' => $data[2] ?? null,
                        'singkat' => $data[3] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }

            DB::commit();
            $this->command->info("Berhasil mengimpor $count data ke tabel koreks.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal: '.$e->getMessage());
        }

        fclose($fileHandle);
    }
}
