<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Bersihkan Cache Spatie Permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Master Hak Akses (Permissions)
        $permissions = [
            'kelola-user',
            'view-anggaran',
            'input-belanja',
            'generate-surat-spj',
            'cetak-dokumen',
            'upload-foto-dokumentasi',
            'kelola-dana-talangan',
            'buat-cover-lpj',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 3. Buat Role & Pasangkan Permission Sesuai Matriks Keuangan

        // - SUPER ADMIN (Akses Semua)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // - BENDAHARA
        $bendahara = Role::firstOrCreate(['name' => 'Bendahara']);
        $bendahara->syncPermissions([
            'view-anggaran',
            'input-belanja',
            'generate-surat-spj',
            'cetak-dokumen',
            'upload-foto-dokumentasi',
            'kelola-dana-talangan',
            'buat-cover-lpj',
        ]);

        // - KEPALA SEKOLAH
        $kepalaSekolah = Role::firstOrCreate(['name' => 'Kepala Sekolah']);
        $kepalaSekolah->syncPermissions([
            'view-anggaran',
            'generate-surat-spj',
            'cetak-dokumen',
            'buat-cover-lpj',
        ]);

        // - PENGURUS BARANG
        $pengurusBarang = Role::firstOrCreate(['name' => 'Pengurus Barang']);
        $pengurusBarang->syncPermissions([
            'view-anggaran',
            'cetak-dokumen',
            'upload-foto-dokumentasi',
        ]);
    }
}
