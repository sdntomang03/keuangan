<?php

namespace App\Imports;

use App\Models\Korek;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KorekUpdateImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Pastikan kolom 'kode' dan 'jenis_belanja' terbaca dari Excel
            if (! empty($row['kode'])) {
                // Cari data berdasarkan kode, lalu update jenis_belanjanya
                Korek::where('kode', $row['kode'])->update([
                    // Gunakan null coalescing agar jika di excel kosong, di DB jadi null
                    'jenis_belanja' => $row['jenis_belanja'] ?? null,
                ]);
            }
        }
    }
}
