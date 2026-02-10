<?php

namespace Tests\Feature;

use App\Models\Anggaran;
use App\Models\Rekanan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BelanjaTest extends TestCase
{
    // Trait ini WAJIB: Reset database setiap kali test selesai biar bersih
    use RefreshDatabase;

    public function test_user_bisa_menyimpan_belanja()
    {
        // 1. SETUP DATA DUMMY (Karena DB kosong melompong)
        // Kita butuh Sekolah, User, Anggaran, Rekanan
        $sekolah = Sekolah::factory()->create();
        $user = User::factory()->create(['sekolah_id' => $sekolah->id]);
        $anggaran = Anggaran::factory()->create(); // Asumsi ada factory
        $rekanan = Rekanan::factory()->create(['sekolah_id' => $sekolah->id]);

        // 2. SIMULASI LOGIN
        $this->actingAs($user);

        // 3. SIAPKAN DATA INPUT (Sesuai form Anda)
        $inputData = [
            'tanggal' => '2024-01-01',
            'no_bukti' => 'BOS-001-TEST',
            'rekanan_id' => $rekanan->id,
            'uraian' => 'Beli Laptop Testing',
            'items' => [
                [
                    'namakomponen' => 'Laptop Asus',
                    'volume' => 1,
                    'harga_satuan' => 5000000,
                    'satuan' => 'Unit',
                    // field lain yg required di controller...
                ],
            ],
            // Kirim data tambahan yg dibutuhkan middleware/controller
            'anggaran_data' => $anggaran,
        ];

        // 4. ACTION: Tembak ke URL store
        // Pastikan route namenya benar 'belanja.store'
        $response = $this->post(route('belanja.store'), $inputData);

        // 5. ASSERTION (Pengecekan Hasil)

        // Cek 1: Apakah redirect sukses (tidak error 500)?
        $response->assertRedirect(route('belanja.index'));

        // Cek 2: Apakah ada session success?
        $response->assertSessionHas('success');

        // Cek 3: Apakah data masuk ke tabel 'belanjas'?
        $this->assertDatabaseHas('belanjas', [
            'no_bukti' => 'BOS-001-TEST',
            'uraian' => 'Beli Laptop Testing',
            'user_id' => $user->id,
        ]);
    }
}
