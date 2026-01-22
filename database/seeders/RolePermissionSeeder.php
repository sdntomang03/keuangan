<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cache permission Spatie (Penting agar perubahan langsung terbaca)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definisikan Permission khusus aplikasi keuangan
        $permissions = [
            'kelola-sekolah',
            'input-transaksi',
            'verifikasi-laporan',
            'akses-admin-pusat',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 3. Buat Roles dan Hubungkan dengan Permission
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all()); // Admin dapat semua akses

        $roleBendahara = Role::firstOrCreate(['name' => 'bendahara']);
        $roleBendahara->givePermissionTo(['input-transaksi', 'kelola-sekolah']);

        $roleKepalaSekolah = Role::firstOrCreate(['name' => 'kepala sekolah']);
        $roleKepalaSekolah->givePermissionTo(['verifikasi-laporan']);

        Role::firstOrCreate(['name' => 'operator']);

        // 4. Sinkronisasi User Utama (ID 1)
        $user = User::find(1);

        if ($user) {
            // Menggunakan syncRoles agar user bersih dari role lama
            $user->syncRoles(['admin']);
            $this->command->info("User {$user->name} berhasil diatur sebagai Admin.");
        } else {
            $this->command->error('User dengan ID 1 tidak ditemukan.');
        }
    }
}
