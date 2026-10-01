<?php

namespace App\Services;

use App\Models\Anggaran;
use App\Models\Catatan;
use App\Models\CatatanLampiran;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatatanService
{
    public function simpanCatatan(array $data, Anggaran $anggaran, int $twAktif): Catatan
    {
        $paths = $this->simpanBerkas($data);

        try {
            return DB::transaction(function () use ($data, $anggaran, $twAktif, $paths) {
                $catatan = Catatan::create([
                    'anggaran_id' => $anggaran->id,
                    'tw' => $twAktif,
                    'catatan' => $data['catatan'],
                    'is_tl' => $data['is_tl'] ?? false,
                    'user_id' => auth()->id(),
                ]);

                foreach ($paths as $path) {
                    $catatan->lampirans()->create(['file_path' => $path]);
                }

                return $catatan;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }
    }

    public function perbaruiCatatan(Catatan $catatan, array $data, Anggaran $anggaran): void
    {
        $paths = $this->simpanBerkas($data);

        try {
            DB::transaction(function () use ($catatan, $data, $anggaran, $paths) {
                $catatan->update([
                    'anggaran_id' => $anggaran->id,
                    'catatan' => $data['catatan'],
                    'is_tl' => $data['is_tl'] ?? false,
                ]);

                foreach ($paths as $path) {
                    $catatan->lampirans()->create(['file_path' => $path]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }
    }

    public function hapusCatatan(Catatan $catatan): void
    {
        $paths = $catatan->lampirans->pluck('file_path')->all();
        if ($catatan->file_path) {
            $paths[] = $catatan->file_path;
        }

        DB::transaction(fn () => $catatan->delete());
        Storage::disk('public')->delete($paths);
    }

    public function hapusLampiran(CatatanLampiran $lampiran): void
    {
        $path = $lampiran->file_path;
        $lampiran->delete();
        Storage::disk('public')->delete($path);
    }

    public function hapusLampiranLama(Catatan $catatan): void
    {
        if (! $catatan->file_path) {
            return;
        }

        $path = $catatan->file_path;
        $catatan->update(['file_path' => null]);
        Storage::disk('public')->delete($path);
    }

    public function toggleTindakLanjut(Catatan $catatan): void
    {
        $catatan->update([
            'is_tl' => ! $catatan->is_tl,
        ]);
    }

    /**
     * @return list<string>
     */
    private function simpanBerkas(array $data): array
    {
        $files = $data['files'] ?? [];
        if (isset($data['file'])) {
            $files[] = $data['file'];
        }

        $paths = [];
        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $paths[] = $file->store('catatan', 'public');
            }
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }

        return $paths;
    }
}
