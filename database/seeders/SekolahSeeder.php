<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user untuk dikaitkan ke sekolah
        // Jika belum ada user, buat user dummy atau ambil user pertama
        $user = User::first() ?? User::factory()->create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.id',
        ]);

        Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'SD Negeri Harapan Bangsa',
            'npsn' => '12345678',
            'nama_kepala_sekolah' => 'Drs. Budi Santoso, M.Pd',
            'nip_kepala_sekolah' => '197501012000031001',
            'nama_bendahara' => 'Siti Aminah, S.Pd',
            'nip_bendahara' => '198205122010012005',

            // Sementara set null jika tabel anggarans belum di-seed
            'anggaran_id_aktif' => null,
            'triwulan_aktif' => 1,

            'alamat' => 'Jl. Pendidikan No. 45, Kebon Jeruk',
            'kelurahan' => 'Sukabumi Selatan',
            'kecamatan' => 'Kebon Jeruk',
            'kota' => 'Jakarta Barat',
            'kodepos' => '11560',
            'telp' => '0215551234',
            'email' => 'sdn_harapanbangsa@edu.id',
            'logo' => null,
        ]);

        // Jika ingin membuat banyak sekolah secara otomatis (Mass Seeding)
        // Sekolah::factory()->count(5)->create();
    }
}
