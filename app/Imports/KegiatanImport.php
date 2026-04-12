<?php

namespace App\Imports;

use App\Models\Kegiatan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KegiatanImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // 1. Ambil ID Sekolah langsung dari profil User yang sedang login
        $sekolahId = Auth::user()->sekolah_id;

        // PROTEKSI: Hentikan proses jika User belum terhubung dengan sekolah manapun
        if (! $sekolahId) {
            throw new \Exception('Akun Anda belum terhubung dengan data Instansi/Sekolah manapun. Silakan lengkapi profil terlebih dahulu.');
        }

        // 2. Looping setiap baris dari Excel
        foreach ($rows as $row) {

            // Proteksi: Lewati (skip) baris jika kolom idbl kosong / baris excel kosong
            if (! isset($row['idbl'])) {
                continue;
            }

            // 3. Eksekusi Update atau Create
            Kegiatan::updateOrCreate(
                [
                    'idbl' => $row['idbl'], // Kunci pencarian (Unique)
                ],
                [
                    'snp' => $row['snp'] ?? null,
                    'sumber_dana' => $row['sumber_dana'] ?? null,
                    'kodedana' => $row['kodedana'] ?? null,
                    'namadana' => $row['namadana'] ?? null,
                    'kodegiat' => $row['kodegiat'] ?? null,
                    'namagiat' => $row['namagiat'] ?? null,
                    'kegiatan' => $row['kegiatan'] ?? null,
                    'link' => $row['link'] ?? null,

                    // Gunakan ID Sekolah yang sudah pasti ada nilainya
                    'sekolah_id' => $sekolahId,
                ]
            );
        }
    }
}
