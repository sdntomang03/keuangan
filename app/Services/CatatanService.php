<?php

namespace App\Services;

use App\Models\Catatan;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class CatatanService
{
    public function simpanCatatan(array $data, $anggaran, $twAktif): Catatan
    {
        $filePath = null;

        if (isset($data['file'])) {
            $file = $data['file'];
            $extension = strtolower($file->getClientOriginalExtension());

            // Daftar ekstensi yang dianggap gambar untuk dikonversi ke WebP
            $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($extension, $imageExtensions)) {
                // Skenario 1: Konversi Gambar ke WebP
                $filename = 'catatan_'.time().'_'.uniqid().'.webp';

                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                $encoded = $image->toWebp(80);

                Storage::disk('public')->put('catatan/'.$filename, $encoded);
                $filePath = 'catatan/'.$filename;
            } else {
                // Skenario 2: Simpan Dokumen (PDF/Word/Excel) Sesuai Aslinya
                $filename = 'dokumen_'.time().'_'.uniqid().'.'.$extension;

                // Simpan file asli menggunakan storeAs ke disk public folder 'catatan'
                $filePath = $file->storeAs('catatan', $filename, 'public');
            }
        }

        return Catatan::create([
            'anggaran_id' => $anggaran->id,
            'tw' => $twAktif,
            'catatan' => $data['catatan'],
            'is_tl' => $data['is_tl'] ?? false,
            'file_path' => $filePath,
            'user_id' => auth()->id(),
        ]);
    }

    public function hapusCatatan(Catatan $catatan): void
    {
        if ($catatan->file_path && Storage::disk('public')->exists($catatan->file_path)) {
            Storage::disk('public')->delete($catatan->file_path);
        }

        $catatan->delete();
    }

    public function toggleTindakLanjut(Catatan $catatan): void
    {
        $catatan->update([
            'is_tl' => ! $catatan->is_tl,
        ]);
    }
}
