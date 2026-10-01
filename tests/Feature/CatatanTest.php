<?php

namespace Tests\Feature;

use App\Models\Anggaran;
use App\Models\Catatan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_notes_can_be_created_with_multiple_pdf_and_image_attachments_and_listed_across_years(): void
    {
        Storage::fake('public');
        [$user, $school, $activeBudget, $previousBudget] = $this->createSchoolWithBudgets();
        $this->actingAs($user);

        $this->post(route('catatan.store'), [
            'anggaran_id' => $activeBudget->id,
            'catatan' => 'Catatan tahun aktif',
            'files' => [
                UploadedFile::fake()->create('bukti.pdf', 30, 'application/pdf'),
                UploadedFile::fake()->create('foto.png', 30, 'image/png'),
            ],
        ])->assertRedirect();

        $this->post(route('catatan.store'), [
            'anggaran_id' => $previousBudget->id,
            'catatan' => 'Catatan tahun sebelumnya',
        ])->assertRedirect();

        $this->assertDatabaseCount('catatan_lampirans', 2);
        $this->get(route('catatan.index', ['tw' => 'semua']))
            ->assertOk()
            ->assertSee('Catatan tahun aktif')
            ->assertSee('Catatan tahun sebelumnya')
            ->assertSee('2025')
            ->assertSee('2026');

        $previousYearNote = Catatan::query()->where('catatan', 'Catatan tahun sebelumnya')->firstOrFail();
        $this->get(route('catatan.index', ['anggaran' => $previousBudget->id, 'tw' => 'semua']))
            ->assertOk()
            ->assertSee('Catatan tahun sebelumnya')
            ->assertDontSee('Catatan tahun aktif');

        $this->assertSame($school->id, $previousBudget->sekolah_id);
    }

    public function test_note_can_be_edited_across_years_and_attachments_can_be_added_or_removed(): void
    {
        Storage::fake('public');
        [$user, , $activeBudget, $previousBudget] = $this->createSchoolWithBudgets();
        $this->actingAs($user);

        $catatan = Catatan::create([
            'anggaran_id' => $activeBudget->id,
            'user_id' => $user->id,
            'tw' => 2,
            'catatan' => 'Isi lama',
            'is_tl' => false,
        ]);
        $oldPath = UploadedFile::fake()->create('lama.pdf', 10, 'application/pdf')->store('catatan', 'public');
        $oldAttachment = $catatan->lampirans()->create(['file_path' => $oldPath]);

        $this->patch(route('catatan.update', $catatan), [
            'anggaran_id' => $previousBudget->id,
            'catatan' => 'Isi diperbarui',
            'is_tl' => 1,
            'files' => [UploadedFile::fake()->create('tambahan.pdf', 10, 'application/pdf')],
        ])->assertRedirect();

        $catatan->refresh();
        $this->assertSame($previousBudget->id, $catatan->anggaran_id);
        $this->assertSame('Isi diperbarui', $catatan->catatan);
        $this->assertTrue((bool) $catatan->is_tl);
        $this->assertDatabaseCount('catatan_lampirans', 2);
        Storage::disk('public')->assertExists($catatan->lampirans()->latest()->first()->file_path);

        $this->get(route('catatan.index', ['tw' => 'semua']))
            ->assertOk()
            ->assertSee(route('catatan.lampiran.destroy', [$catatan, $oldAttachment]), false)
            ->assertSee('Hapus lampiran ini? Berkas akan dihapus permanen.');

        $otherNote = Catatan::create([
            'anggaran_id' => $previousBudget->id,
            'user_id' => $user->id,
            'tw' => 2,
            'catatan' => 'Catatan lain',
        ]);
        $otherAttachment = $otherNote->lampirans()->create(['file_path' => 'catatan/lain.pdf']);
        $this->delete(route('catatan.lampiran.destroy', [$catatan, $otherAttachment]))
            ->assertNotFound();

        $this->delete(route('catatan.lampiran.destroy', [$catatan, $oldAttachment]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Lampiran berhasil dihapus.');
        $this->assertDatabaseHas('catatan_lampirans', ['id' => $otherAttachment->id]);
        $this->assertSame(1, $catatan->lampirans()->count());
        Storage::disk('public')->assertMissing($oldPath);
    }

    private function createSchoolWithBudgets(): array
    {
        $user = User::factory()->create();
        $school = Sekolah::create([
            'user_id' => $user->id,
            'nama_sekolah' => 'Sekolah Catatan',
            'nama_kepala_sekolah' => 'Kepala',
            'nip_kepala_sekolah' => '1',
            'nama_bendahara' => 'Bendahara',
            'nip_bendahara' => '2',
            'nama_pengurus_barang' => 'Pengurus',
            'nip_pengurus_barang' => '3',
            'triwulan_aktif' => 2,
        ]);
        $user->update(['sekolah_id' => $school->id]);

        $activeBudget = Anggaran::create([
            'sekolah_id' => $school->id,
            'tahun' => '2026',
            'singkatan' => 'BOS',
            'nama_anggaran' => 'BOS',
            'is_aktif' => true,
        ]);
        $previousBudget = Anggaran::create([
            'sekolah_id' => $school->id,
            'tahun' => '2025',
            'singkatan' => 'BOS',
            'nama_anggaran' => 'BOS',
            'is_aktif' => false,
        ]);
        $user->refresh();

        return [$user, $school, $activeBudget, $previousBudget];
    }
}
