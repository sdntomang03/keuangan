<?php

namespace Database\Seeders;

use App\Models\Anggaran;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua sekolah yang sudah ada
        $allSekolah = Sekolah::all();

        foreach ($allSekolah as $sekolah) {
            // 2. Buat Data Anggaran BOS untuk sekolah tersebut
            $bos = Anggaran::create([
                'sekolah_id' => $sekolah->id,
                'tahun' => '2026',
                'singkatan' => 'bos',
                'nama_anggaran' => 'BOS Reguler Tahun 2026',
            ]);

            // 3. Buat Data Anggaran BOP untuk sekolah tersebut
            $bop = Anggaran::create([
                'sekolah_id' => $sekolah->id,
                'tahun' => '2026',
                'singkatan' => 'bop',
                'nama_anggaran' => 'BOP Daerah Tahun 2026',
            ]);

            // 4. Update tabel sekolah untuk menentukan anggaran mana yang aktif
            // Secara default kita buat BOS sebagai yang aktif pertama kali
            $sekolah->update([
                'anggaran_id_aktif' => $bos->id,
            ]);
            User::where('id', 1)->update([
                'sekolah_id' => 1,
            ]);
        }
    }
}
