<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SudinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // JAKARTA PUSAT
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah I Kota Administrasi Jakarta Pusat',
                'singkatan' => 'JP1',
            ],
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah II Kota Administrasi Jakarta Pusat',
                'singkatan' => 'JP2',
            ],

            // JAKARTA UTARA
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah I Kota Administrasi Jakarta Utara',
                'singkatan' => 'JU1',
            ],
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah II Kota Administrasi Jakarta Utara',
                'singkatan' => 'JU2',
            ],

            // JAKARTA BARAT
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah I Kota Administrasi Jakarta Barat',
                'singkatan' => 'JB1',
            ],
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah II Kota Administrasi Jakarta Barat',
                'singkatan' => 'JB2',
            ],

            // JAKARTA SELATAN
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah I Kota Administrasi Jakarta Selatan',
                'singkatan' => 'JS1',
            ],
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah II Kota Administrasi Jakarta Selatan',
                'singkatan' => 'JS2',
            ],

            // JAKARTA TIMUR
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah I Kota Administrasi Jakarta Timur',
                'singkatan' => 'JT1',
            ],
            [
                'nama' => 'Suku Dinas Pendidikan Wilayah II Kota Administrasi Jakarta Timur',
                'singkatan' => 'JT2',
            ],

            // KEPULAUAN SERIBU
            [
                'nama' => 'Suku Dinas Pendidikan Kabupaten Administrasi Kepulauan Seribu',
                'singkatan' => 'KS',
            ],
        ];

        foreach ($data as $item) {
            DB::table('sudins')->insert([
                'nama' => $item['nama'],
                'singkatan' => $item['singkatan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
