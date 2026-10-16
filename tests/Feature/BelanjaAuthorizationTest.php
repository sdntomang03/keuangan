<?php

namespace Tests\Feature;

use App\Models\Belanja;
use App\Models\Rekanan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BelanjaAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_or_update_another_schools_transaction(): void
    {
        $sekolahPemilik = $this->createSekolah('Sekolah Pemilik');
        $pemilik = User::factory()->create(['sekolah_id' => $sekolahPemilik->id]);

        $sekolahLain = $this->createSekolah('Sekolah Lain');
        $pengguna = User::factory()->create(['sekolah_id' => $sekolahLain->id]);
        $pengguna->givePermissionTo(Permission::create([
            'name' => 'input-belanja',
            'guard_name' => 'web',
        ]));

        $rekanan = Rekanan::create([
            'sekolah_id' => $sekolahPemilik->id,
            'nama_rekanan' => 'Penyedia Pemilik',
        ]);

        $belanja = Belanja::create([
            'user_id' => $pemilik->id,
            'rekanan_id' => $rekanan->id,
            'tanggal' => '2026-01-15',
            'no_bukti' => 'BUKTI-PEMILIK',
            'uraian' => 'Transaksi milik sekolah lain',
            'subtotal' => 100000,
            'ppn' => 0,
            'pph' => 0,
            'transfer' => 100000,
        ]);

        $this->actingAs($pengguna)
            ->get(route('belanja.show', $belanja))
            ->assertNotFound();

        $this->get(route('belanja.edit', $belanja))
            ->assertNotFound();

        $this->put(route('belanja.update', $belanja), [
            'tanggal' => '2026-01-16',
            'no_bukti' => 'BUKTI-DIUBAH',
            'rekanan_id' => $rekanan->id,
            'uraian' => 'Transaksi diubah',
            'idbl' => 'KEGIATAN-1',
            'kodeakun' => 'AKUN-1',
            'items' => [[
                'idblrinci' => 'RINCIAN-1',
                'namakomponen' => 'Barang',
                'volume' => 1,
                'harga_satuan' => 100000,
            ]],
        ])->assertNotFound();

        $rekananPengguna = Rekanan::create([
            'sekolah_id' => $sekolahLain->id,
            'nama_rekanan' => 'Penyedia Pengguna',
        ]);
        $belanjaSendiri = Belanja::create([
            'user_id' => $pengguna->id,
            'rekanan_id' => $rekananPengguna->id,
            'tanggal' => '2026-01-15',
            'no_bukti' => 'BUKTI-PENGGUNA',
            'uraian' => 'Transaksi milik sekolah pengguna',
            'subtotal' => 100000,
            'ppn' => 0,
            'pph' => 0,
            'transfer' => 100000,
        ]);

        $this->get(route('belanja.json', $belanjaSendiri))
            ->assertOk();

        $this->assertDatabaseHas('belanjas', [
            'id' => $belanja->id,
            'no_bukti' => 'BUKTI-PEMILIK',
            'uraian' => 'Transaksi milik sekolah lain',
        ]);
    }

    private function createSekolah(string $nama): Sekolah
    {
        $user = User::factory()->create();

        return Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => $nama,
            'nama_kepala_sekolah' => 'Kepala Sekolah',
            'nip_kepala_sekolah' => '123',
            'nama_bendahara' => 'Bendahara',
            'nip_bendahara' => '456',
            'nama_pengurus_barang' => 'Pengurus Barang',
            'nip_pengurus_barang' => '789',
        ]);
    }
}
