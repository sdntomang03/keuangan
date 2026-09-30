<?php

namespace Tests\Feature;

use App\Models\Akb;
use App\Models\AkbRinci;
use App\Models\Anggaran;
use App\Models\Belanja;
use App\Models\BelanjaRinci;
use App\Models\Kegiatan;
use App\Models\Korek;
use App\Models\PenyesuaianPagu;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PenyesuaianPaguTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_components_show_available_monthly_volume_and_12_percent_tax_basis(): void
    {
        $user = User::factory()->create();
        $sekolah = Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah Uji',
            'nama_kepala_sekolah' => 'Kepala',
            'nip_kepala_sekolah' => '1',
            'nama_bendahara' => 'Bendahara',
            'nip_bendahara' => '2',
            'nama_pengurus_barang' => 'Pengurus',
            'nip_pengurus_barang' => '3',
            'triwulan_aktif' => 3,
        ]);
        $user->update(['sekolah_id' => $sekolah->id]);

        $anggaran = Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => '2026',
            'singkatan' => 'BOS',
            'nama_anggaran' => 'BOS 2026',
            'is_aktif' => true,
        ]);
        $kegiatan = Kegiatan::create([
            'idbl' => 'KEG-1',
            'kodegiat' => '01',
            'namagiat' => 'Kegiatan Uji',
        ]);
        $korek = Korek::create([
            'kode' => '5.1.1',
            'ket' => 'Belanja Barang Panjang',
            'singkat' => 'BARANG',
        ]);
        $rkas = Rkas::create([
            'idblrinci' => 'KOMP-1',
            'idbl' => $kegiatan->idbl,
            'kodeakun' => $korek->id,
            'namakomponen' => 'Komponen Uji',
            'satuan' => 'Unit',
            'hargasatuan' => 100,
            'totalharga' => 800,
            'totalpajak' => 5,
            'anggaran_id' => $anggaran->id,
        ]);
        $akb = Akb::create([
            'idblrinci' => $rkas->idblrinci,
            'anggaran_id' => $anggaran->id,
        ]);
        foreach ([[1, 5, 500], [2, 3, 300], [7, 4, 400]] as [$bulan, $volume, $nominal]) {
            AkbRinci::create([
                'akb_id' => $akb->id,
                'idblrinci' => $rkas->idblrinci,
                'anggaran_id' => $anggaran->id,
                'bulan' => $bulan,
                'volume' => $volume,
                'nominal' => $nominal,
            ]);
        }

        $rkasTanpaPpn = Rkas::create([
            'idblrinci' => 'KOMP-2',
            'idbl' => $kegiatan->idbl,
            'namakomponen' => 'Komponen Tanpa PPN',
            'satuan' => 'Unit',
            'hargasatuan' => 100,
            'totalharga' => 400,
            'totalpajak' => 0,
            'anggaran_id' => $anggaran->id,
        ]);
        $akbTanpaPpn = Akb::create([
            'idblrinci' => $rkasTanpaPpn->idblrinci,
            'anggaran_id' => $anggaran->id,
        ]);
        AkbRinci::create([
            'akb_id' => $akbTanpaPpn->id,
            'idblrinci' => $rkasTanpaPpn->idblrinci,
            'anggaran_id' => $anggaran->id,
            'bulan' => 1,
            'volume' => 4,
            'nominal' => 400,
        ]);

        $kegiatanLain = Kegiatan::create([
            'idbl' => 'KEG-2',
            'kodegiat' => '02',
            'namagiat' => 'Kegiatan Lain',
        ]);
        $rkasKegiatanLain = Rkas::create([
            'idblrinci' => 'KOMP-3',
            'idbl' => $kegiatanLain->idbl,
            'namakomponen' => 'Komponen Kegiatan Lain',
            'satuan' => 'Paket',
            'hargasatuan' => 250,
            'totalharga' => 250,
            'anggaran_id' => $anggaran->id,
        ]);
        $akbKegiatanLain = Akb::create([
            'idblrinci' => $rkasKegiatanLain->idblrinci,
            'anggaran_id' => $anggaran->id,
        ]);
        AkbRinci::create([
            'akb_id' => $akbKegiatanLain->id,
            'idblrinci' => $rkasKegiatanLain->idblrinci,
            'anggaran_id' => $anggaran->id,
            'bulan' => 1,
            'volume' => 1,
            'nominal' => 250,
        ]);

        $rekanan = Rekanan::create([
            'sekolah_id' => $sekolah->id,
            'nama_rekanan' => 'Toko Uji',
        ]);
        $belanja = Belanja::create([
            'user_id' => $user->id,
            'rekanan_id' => $rekanan->id,
            'tanggal' => '2026-01-10',
            'no_bukti' => 'TEST-1',
            'uraian' => 'Belanja uji',
            'subtotal' => 200,
            'ppn' => 1,
            'pph' => 0,
            'transfer' => 200,
            'status' => Belanja::STATUS_POSTED,
            'anggaran_id' => $anggaran->id,
        ]);
        BelanjaRinci::create([
            'belanja_id' => $belanja->id,
            'idblrinci' => $rkas->idblrinci,
            'namakomponen' => $rkas->namakomponen,
            'harga_satuan' => 100,
            'volume' => 2,
            'bulan' => 1,
            'total_bruto' => 200,
        ]);

        Permission::findOrCreate('view-anggaran', 'web');
        $user->givePermissionTo('view-anggaran');
        Permission::findOrCreate('kelola-anggaran', 'web');
        $user->givePermissionTo('kelola-anggaran');
        $this->actingAs($user);

        $this->get(route('rkas.penyesuaian-pagu'))
            ->assertOk()
            ->assertSee('Komponen Uji')
            ->assertSee('Rp1.200')
            ->assertSee('Rp400')
            ->assertSee('Rp224')
            ->assertSee('Rp976')
            ->assertSee('BARANG')
            ->assertDontSee('Belanja Barang Panjang');

        $this->get(route('rkas.penyesuaian-pagu', ['kegiatan' => $kegiatan->idbl]))
            ->assertOk()
            ->assertSee('Komponen Uji')
            ->assertDontSee('Komponen Kegiatan Lain');

        $this->post(route('rkas.penyesuaian-pagu.proses'), [
            'jenis' => 'pergeseran',
            'kegiatan' => $kegiatan->idbl,
            'komponen' => [$rkas->id, $rkasTanpaPpn->id],
        ])
            ->assertOk()
            ->assertSee('Bulan 1')
            ->assertSee('name="kegiatan" value="'.$kegiatan->idbl.'"', false)
            ->assertDontSee('Komponen Kegiatan Lain')
            ->assertSee('PPN 12%')
            ->assertSee('value="3"', false)
            ->assertSee('name="volume['.$rkas->id.'][11]"', false)
            ->assertSee('data-tax="0"', false)
            ->assertViewHas('komponen', fn ($items) => $items->firstWhere('id', $rkas->id)->volume_tersisa->get(1) === 3.0
                && $items->firstWhere('id', $rkas->id)->volume_tersisa->get(2) === 3.0);

        $volume = [
            $rkas->id => array_replace(array_fill(1, 12, 0), [1 => 3, 2 => 3, 7 => 4]),
            $rkasTanpaPpn->id => array_replace(array_fill(1, 12, 0), [1 => 4]),
        ];
        $volume[$rkas->id][1] = 2;
        $volume[$rkas->id][7] = 0;
        $volume[$rkas->id][11] = 4;
        $volume[$rkasTanpaPpn->id][1] = 1;

        $volumeTidakValid = $volume;
        $volumeTidakValid[$rkas->id][11] = 6;
        $this->post(route('rkas.penyesuaian-pagu.simpan'), [
            'jenis' => 'pergeseran',
            'komponen' => [$rkas->id, $rkasTanpaPpn->id],
            'volume' => $volumeTidakValid,
        ])->assertSessionHasErrors();
        $this->assertDatabaseCount('penyesuaian_pagus', 0);

        $volumeTidakBerubah = [
            $rkas->id => array_replace(array_fill(1, 12, 0), [1 => 3, 2 => 3, 7 => 4]),
            $rkasTanpaPpn->id => array_replace(array_fill(1, 12, 0), [1 => 4]),
        ];
        $this->post(route('rkas.penyesuaian-pagu.simpan'), [
            'jenis' => 'pergeseran',
            'komponen' => [$rkas->id, $rkasTanpaPpn->id],
            'volume' => $volumeTidakBerubah,
        ])->assertRedirect(route('rkas.penyesuaian-pagu'))
            ->assertSessionHas('info');
        $this->assertDatabaseCount('penyesuaian_pagus', 0);
        $this->assertDatabaseCount('penyesuaian_pagu_rincis', 0);

        $headerPenyesuaian = PenyesuaianPagu::create([
            'anggaran_id' => $anggaran->id,
            'user_id' => $user->id,
            'jenis' => 'pergeseran',
            'tw' => 3,
        ]);
        $this->post(route('rkas.penyesuaian-pagu.simpan'), [
            'jenis' => 'pergeseran',
            'komponen' => [$rkas->id, $rkasTanpaPpn->id],
            'volume' => $volume,
        ])
            ->assertRedirect(route('rkas.penyesuaian-pagu'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('penyesuaian_pagus', 1);
        $this->assertDatabaseHas('penyesuaian_pagus', [
            'anggaran_id' => $anggaran->id,
            'user_id' => $user->id,
            'jenis' => 'pergeseran',
            'tw' => 3,
        ]);
        $this->assertDatabaseHas('penyesuaian_pagu_rincis', [
            'penyesuaian_pagu_id' => $headerPenyesuaian->id,
            'idblrinci' => $rkas->idblrinci,
            'bulan' => 1,
            'volume_awal' => 3,
            'volume_setelah' => 2,
            'volume_selisih' => 1,
            'ppn_persen' => 12,
            'nominal_selisih' => 100,
            'nominal_ppn' => 12,
            'pagu_dapat_digeser' => 112,
        ]);
        $this->assertDatabaseHas('penyesuaian_pagu_rincis', [
            'idblrinci' => $rkas->idblrinci,
            'bulan' => 7,
            'volume_awal' => 4,
            'volume_setelah' => 0,
            'volume_selisih' => 4,
            'pagu_dapat_digeser' => 448,
        ]);
        $this->assertDatabaseHas('penyesuaian_pagu_rincis', [
            'idblrinci' => $rkas->idblrinci,
            'bulan' => 11,
            'volume_awal' => 0,
            'volume_setelah' => 4,
            'volume_selisih' => -4,
            'nominal_selisih' => -400,
            'nominal_ppn' => -48,
            'pagu_dapat_digeser' => 0,
        ]);
        $this->assertDatabaseHas('penyesuaian_pagu_rincis', [
            'idblrinci' => $rkasTanpaPpn->idblrinci,
            'bulan' => 1,
            'volume_awal' => 4,
            'volume_setelah' => 1,
            'volume_selisih' => 3,
            'ppn_persen' => 0,
            'nominal_selisih' => 300,
            'nominal_ppn' => 0,
            'pagu_dapat_digeser' => 300,
        ]);
        $this->assertDatabaseCount('penyesuaian_pagu_rincis', 4);

        $this->post(route('rkas.penyesuaian-pagu.simpan'), [
            'jenis' => 'pergeseran',
            'komponen' => [$rkas->id, $rkasTanpaPpn->id],
            'volume' => $volume,
        ])->assertRedirect(route('rkas.penyesuaian-pagu'))
            ->assertSessionHas('success');
        $this->assertDatabaseCount('penyesuaian_pagus', 1);
        $this->assertDatabaseCount('penyesuaian_pagu_rincis', 4);

        $this->assertTrue(Schema::hasColumn('penyesuaian_pagu_rincis', 'idblrinci'));
        $this->assertFalse(Schema::hasColumn('penyesuaian_pagu_rincis', 'rkas_id'));
        $this->assertSame(800.0, (float) $rkas->fresh()->totalharga);
        $this->assertSame(5.0, (float) $rkas->fresh()->totalpajak);

        $this->get(route('rkas.penyesuaian-pagu.daftar', ['tw' => 3]))
            ->assertOk()
            ->assertSee('Pergeseran · TW 3')
            ->assertSee('Komponen Uji')
            ->assertSee('Komponen Tanpa PPN')
            ->assertSee('Rekap Kode Rekening')
            ->assertSee('5.1.1')
            ->assertSee('Rp112')
            ->assertDontSee('Rp560');

        $this->get(route('rkas.penyesuaian-pagu.daftar', ['tw' => 2]))
            ->assertOk()
            ->assertSee('Belum ada catatan pergeseran/perubahan');

        $this->get(route('rkas.penyesuaian-pagu.tw', ['tw' => 3]))
            ->assertOk()
            ->assertSee('Rekap Pagu per Kode Rekening · TW 3')
            ->assertSee('Rp448')
            ->assertSee('Rp0');

        $this->get(route('rkas.penyesuaian-pagu.tw', ['tw' => 4]))
            ->assertOk()
            ->assertSee('Rekap Pagu per Kode Rekening · TW 4')
            ->assertSee('+Rp448')
            ->assertSee('Rp448');

        $this->assertSame(400.0, (float) AkbRinci::query()
            ->where('akb_id', $akb->id)
            ->where('bulan', 7)
            ->value('nominal'));
    }
}
