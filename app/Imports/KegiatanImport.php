<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KegiatanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari Sekolah milik User yang Login
        $sekolah = Sekolah::where('user_id', Auth::id())->first();

        // Jika sekolah tidak ditemukan (misal user belum input profil sekolah), skip atau error.
        // Disini kita assumsikan user sudah punya sekolah.
        $sekolahId = $sekolah ? $sekolah->id : null;

        // 2. Gunakan updateOrCreate berdasarkan 'idbl'
        return Kegiatan::updateOrCreate(
            [
                'idbl' => $row['idbl'], // Kunci pencarian (Unique)
            ],
            [
                'snp' => $row['snp'],
                'sumber_dana' => $row['sumber_dana'],
                'kodedana' => $row['kodedana'],
                'namadana' => $row['namadana'],
                'kodegiat' => $row['kodegiat'],
                'namagiat' => $row['namagiat'],
                'kegiatan' => $row['kegiatan'], // Deskripsi panjang
                'link' => $row['link'] ?? null,
                'sekolah_id' => $sekolahId, // Otomatis dari sistem
            ]
        );
    }
}
