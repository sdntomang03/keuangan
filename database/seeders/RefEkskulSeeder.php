<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefEkskulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ID Sekolah Dummy (Misal sekolah dengan ID 1)
        // Jika Anda multi-sekolah, sesuaikan logika ini
        $sekolahId = 1;

        $ekskuls = [
            ['nama' => 'Pramuka'],
            ['nama' => 'Paskibra'],
            ['nama' => 'Futsal'],
            ['nama' => 'Pencak Silat'],
            ['nama' => 'Karate'],
            ['nama' => 'English CLub'],
            ['nama' => 'Seni Tari'],
            ['nama' => 'Marawis'],
            ['nama' => 'Komputer / IT'],
        ];

        $data = [];
        $now = Carbon::now();

        foreach ($ekskuls as $ekskul) {
            $data[] = [
                'nama' => $ekskul['nama'],
                'rekanan_id' => null, // Biarkan null dulu, nanti diisi saat edit data pelatih
                'sekolah_id' => $sekolahId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('ref_ekskul')->insert($data);
    }
}
