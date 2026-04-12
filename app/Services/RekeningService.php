<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RekeningService
{
    protected $map = [];

    public function __construct()
    {
        // Ambil data dari tabel koreks
        $koreks = DB::table('koreks')->pluck('id', 'kode')->toArray();

        foreach ($koreks as $kode => $id) {
            // Normalisasi kode dari database sebelum dimasukkan ke array map
            $normalizedKode = $this->normalizeKode($kode);
            $this->map[$normalizedKode] = $id;
        }
    }

    public function getIdByKode($kode)
    {
        // Normalisasi kode dari file JSON sebelum dicari
        $normalizedKode = $this->normalizeKode($kode);

        return $this->map[$normalizedKode] ?? null;
    }

    /**
     * Helper untuk menyamakan format dengan menghapus angka nol di depan setiap titik (segmen)
     */
    private function normalizeKode($kode)
    {
        if (empty($kode)) {
            return '';
        }

        // 1. Bersihkan spasi dan pecah string berdasarkan titik
        $segments = explode('.', trim((string) $kode));

        // 2. Looping setiap segmen dan ubah jadi tipe integer
        $normalizedSegments = array_map(function ($segment) {
            // Cast ke (int) otomatis membuang angka nol di depan: "002" jadi 2, "00005" jadi 5
            return (int) $segment;
        }, $segments);

        // 3. Gabungkan kembali dengan titik
        return implode('.', $normalizedSegments);
    }
}
