<?php

namespace Tests\Feature;

use App\Models\Akb;
use App\Models\AkbRinci;
use App\Models\Anggaran;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AkbRincianTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_components_can_be_shown_as_volume_or_nominal(): void
    {
        $user = User::factory()->create();
        $sekolah = Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah Uji',
            'nama_kepala_sekolah' => 'Kepala Sekolah',
            'nip_kepala_sekolah' => '123',
            'nama_bendahara' => 'Bendahara',
            'nip_bendahara' => '456',
            'nama_pengurus_barang' => 'Pengurus Barang',
            'nip_pengurus_barang' => '789',
        ]);
        $user->update(['sekolah_id' => $sekolah->id]);

        $anggaran = Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => '2026',
            'singkatan' => 'BOS',
            'nama_anggaran' => 'BOS Reguler',
            'is_aktif' => true,
        ]);

        $user->givePermissionTo(Permission::create([
            'name' => 'view-anggaran',
            'guard_name' => 'web',
        ]));

        $rkasId = 'COMPONENT-TEST-1';
        $akb = Akb::create([
            'idblrinci' => $rkasId,
            'anggaran_id' => $anggaran->id,
        ]);

        \App\Models\Rkas::create([
            'idblrinci' => $rkasId,
            'namakomponen' => 'Komponen Uji',
            'satuan' => 'Unit',
            'hargasatuan' => 1000,
            'totalharga' => 3000,
            'anggaran_id' => $anggaran->id,
        ]);

        AkbRinci::create([
            'akb_id' => $akb->id,
            'idblrinci' => $rkasId,
            'bulan' => 1,
            'nominal' => 1000,
            'volume' => 2.5,
            'anggaran_id' => $anggaran->id,
        ]);
        AkbRinci::create([
            'akb_id' => $akb->id,
            'idblrinci' => $rkasId,
            'bulan' => 2,
            'nominal' => 2000,
            'volume' => 1.25,
            'anggaran_id' => $anggaran->id,
        ]);

        $this->actingAs($user)
            ->get(route('akb.rincian', ['jenis_nilai' => 'volume']))
            ->assertOk()
            ->assertSee('Volume AKB')
            ->assertDontSee('Sub Total Kegiatan')
            ->assertDontSee('Grand Total Keseluruhan')
            ->assertSee('2,50')
            ->assertSee('1,25')
            ->assertSee('3,75');

        $this->get(route('akb.rincian', ['jenis_nilai' => 'nominal']))
            ->assertOk()
            ->assertSee('Nominal AKB')
            ->assertSee('Sub Total Kegiatan')
            ->assertSee('Grand Total Keseluruhan')
            ->assertSee('1.000')
            ->assertSee('2.000')
            ->assertSee('3.000');

        AkbRinci::where('idblrinci', $rkasId)->update(['volume' => 0]);

        $this->get(route('akb.rincian', ['jenis_nilai' => 'volume']))
            ->assertOk()
            ->assertSee('Komponen Uji')
            ->assertDontSee('Sub Total Kegiatan')
            ->assertDontSee('Grand Total Keseluruhan');
    }
}
