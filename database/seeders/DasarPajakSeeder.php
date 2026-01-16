<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DasarPajakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_pajak' => 'PPN',
                'persen' => 11,
                'jenis' => 'penambah',
            ],
            [
                'nama_pajak' => 'PPh 21',
                'persen' => 2.5,
                'jenis' => 'pengurang',
            ],
            [
                'nama_pajak' => 'PPh 22',
                'persen' => 1.5,
                'jenis' => 'pengurang',
            ],
            [
                'nama_pajak' => 'PPh 23',
                'persen' => 2,
                'jenis' => 'pengurang',
            ],

        ];

        foreach ($data as $item) {
            DB::table('dasar_pajaks')->insert([
                'nama_pajak' => $item['nama_pajak'],
                'persen' => $item['persen'],
                'jenis' => $item['jenis'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
