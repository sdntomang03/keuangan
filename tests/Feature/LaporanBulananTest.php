<?php

namespace Tests\Feature;

use App\Models\Anggaran;
use App\Models\Belanja;
use App\Models\Korek;
use App\Models\LaporanBulanan;
use App\Models\Penerimaan;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use App\Models\Sts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LaporanBulananTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_and_print_monthly_responsibility_report(): void
    {
        $user = User::factory()->create();
        $sekolah = Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'SD Negeri Uji',
            'npsn' => '20101172',
            'nama_kepala_sekolah' => 'Kepala Sekolah Uji',
            'nip_kepala_sekolah' => '196805051993032010',
            'nama_bendahara' => 'Bendahara Uji',
            'nip_bendahara' => '123',
            'nama_pengurus_barang' => 'Pengurus Uji',
            'nip_pengurus_barang' => '456',
        ]);
        $user->update(['sekolah_id' => $sekolah->id]);
        $anggaran = Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => '2026',
            'singkatan' => 'BOSP',
            'nama_anggaran' => 'DANA BOSP',
            'is_aktif' => true,
        ]);
        $user->givePermissionTo(Permission::create([
            'name' => 'kelola-anggaran',
            'guard_name' => 'web',
        ]));
        Penerimaan::create([
            'anggaran_id' => $anggaran->id,
            'tanggal' => '2026-09-10',
            'no_bukti' => 'TR-09',
            'uraian' => 'Penerimaan Dana Tahap II',
            'nominal' => 188927425,
        ]);
        Penerimaan::create([
            'anggaran_id' => $anggaran->id,
            'tanggal' => '2026-08-10',
            'no_bukti' => 'TR-08',
            'uraian' => 'Penerimaan Tahap II',
            'nominal' => 999999,
        ]);
        Sts::create([
            'anggaran_id' => $anggaran->id,
            'tanggal' => '2026-09-20',
            'no_bukti' => 'STS-09',
            'uraian' => 'Setoran Pajak September',
            'nominal' => 50,
            'tw' => 3,
        ]);
        $rekanan = Rekanan::create([
            'sekolah_id' => $sekolah->id,
            'nama_rekanan' => 'Rekanan Uji',
        ]);
        foreach ([
            ['operasional', 63677520],
            ['mesin', 73876050],
            ['aset lainnya', 25559400],
        ] as $index => [$jenisBelanja, $subtotal]) {
            $korek = Korek::create([
                'kode' => '5.'.$index,
                'uraian_singkat' => 'Belanja Uji',
                'jenis_belanja' => $jenisBelanja,
            ]);
            Belanja::create([
                'user_id' => $user->id,
                'rekanan_id' => $rekanan->id,
                'tanggal' => '2026-09-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'no_bukti' => 'BL-09-'.$index,
                'uraian' => 'Belanja '.$jenisBelanja,
                'subtotal' => $subtotal,
                'ppn' => 0,
                'pph' => 0,
                'transfer' => $subtotal,
                'kodeakun' => $korek->id,
                'status' => Belanja::STATUS_POSTED,
                'anggaran_id' => $anggaran->id,
            ]);
        }
        $operasionalKorek = Korek::where('jenis_belanja', 'operasional')->firstOrFail();
        Rkas::create([
            'idblrinci' => 'RKAS-OPERASIONAL-UJI',
            'kodeakun' => $operasionalKorek->id,
            'namakomponen' => 'Komponen operasional',
            'totalharga' => 100000000,
            'anggaran_id' => $anggaran->id,
        ]);
        Belanja::create([
            'user_id' => $user->id,
            'rekanan_id' => $rekanan->id,
            'tanggal' => '2026-09-30',
            'no_bukti' => 'BL-09-DRAFT',
            'uraian' => 'Belanja draft tidak dihitung',
            'subtotal' => 999999,
            'ppn' => 0,
            'pph' => 0,
            'transfer' => 999999,
            'kodeakun' => $operasionalKorek->id,
            'status' => Belanja::STATUS_DRAFT,
            'anggaran_id' => $anggaran->id,
        ]);
        Belanja::create([
            'user_id' => $user->id,
            'rekanan_id' => $rekanan->id,
            'tanggal' => '2026-08-30',
            'no_bukti' => 'BL-08',
            'uraian' => 'Belanja Bulan Lalu',
            'subtotal' => 999999,
            'ppn' => 0,
            'pph' => 0,
            'transfer' => 999999,
            'kodeakun' => 1,
            'status' => Belanja::STATUS_POSTED,
            'anggaran_id' => $anggaran->id,
        ]);

        $reportData = [
            'bulan' => 9,
            'nomor_surat' => '123/2026',
            'sumber_dana' => 'DANA BOSP',
            'saldo_awal' => 25670319,
            'penerimaan_tahap_i' => 0,
            'penerimaan_tahap_ii' => 1,
            'penerimaan_kinerja' => 0,
            'penerimaan_lain' => 0,
            'pengeluaran_barang_jasa' => 1,
            'pengeluaran_modal_peralatan' => 1,
            'pengeluaran_modal_aset_lainnya' => 1,
            'pengeluaran_lain' => 0,
            'kas_tunai' => 0,
            'rekening_bank' => 51484774,
            'penjelasan_selisih' => '',
            'kota_surat' => 'Jakarta',
            'tanggal_surat' => '2026-10-16',
        ];
        $response = $this->actingAs($user)->post(route('laporan-bulanan.store'), $reportData);

        $laporan = LaporanBulanan::firstOrFail();
        $response->assertRedirect(route('laporan-bulanan.index'));
        $this->assertSame(188927425.0, $laporan->jumlah_penerimaan);
        $this->assertSame(163112970.0, $laporan->jumlah_pengeluaran);
        $this->assertSame(51484774.0, $laporan->sisa_dana);

        $this->get(route('laporan-bulanan.print', $laporan->id))
            ->assertOk()
            ->assertSee('SURAT PERNYATAAN TANGGUNGJAWAB MUTLAK')
            ->assertSee('September')
            ->assertSee('SD Negeri Uji')
            ->assertSee('Kepala Sekolah Uji')
            ->assertSee('51.484.774')
            ->assertSee('PENGATURAN CETAK')
            ->assertSee('Ukuran Font')
            ->assertSee('Margin Kertas')
            ->assertSee('Bukti-bukti atas belanja tersebut pada huruf B di atas')
            ->assertSee('Penjelasan Selisih');

        $this->get(route('laporan-bulanan.realisasi', $laporan->id))
            ->assertOk()
            ->assertSee('LAPORAN REALISASI PENERIMAAN DAN PENGELUARAN DANA BOSP')
            ->assertSee('PENGATURAN CETAK')
            ->assertSee('Kerapatan Baris')
            ->assertSee('Margin Kertas')
            ->assertSee('size: A4 portrait')
            ->assertSee('Realisasi s.d. Agustus')
            ->assertSee('Realisasi Bulan September')
            ->assertSee('Jumlah Realisasi s.d. September')
            ->assertSee('Belanja Modal Peralatan dan Mesin')
            ->assertSee('64.677.519')
            ->assertSee('100.000.000')
            ->assertSee('188.927.425')
            ->assertSee('Setoran Pajak')
            ->assertSee('50');

        $this->assertSame($anggaran->id, $laporan->anggaran_id);

        $unclassifiedKorek = Korek::create([
            'kode' => '5.99',
            'uraian_singkat' => 'Belanja Tanpa Jenis',
        ]);
        Belanja::create([
            'user_id' => $user->id,
            'rekanan_id' => $rekanan->id,
            'tanggal' => '2026-10-01',
            'no_bukti' => 'BL-10-UNCLASSIFIED',
            'uraian' => 'Transaksi belum diklasifikasikan',
            'subtotal' => 10000,
            'ppn' => 0,
            'pph' => 0,
            'transfer' => 10000,
            'kodeakun' => $unclassifiedKorek->id,
            'status' => Belanja::STATUS_POSTED,
            'anggaran_id' => $anggaran->id,
        ]);

        $this->post(route('laporan-bulanan.store'), [...$reportData, 'bulan' => 10])
            ->assertSessionHasErrors('bulan')
            ->assertSessionHasErrors([
                'bulan' => 'Jenis belanja Korek belum diatur untuk transaksi: BL-10-UNCLASSIFIED (rekening 5.99). Atur jenis belanja pada data Korek sebelum menyimpan laporan.',
            ]);
    }
}
