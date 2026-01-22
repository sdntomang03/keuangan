<?php

namespace App\Imports;

use App\Models\Rekanan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Agar membaca baris pertama sebagai Header
use Maatwebsite\Excel\Concerns\WithValidation; // Untuk validasi data excel

class RekananImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Rekanan([
            // Mengambil ID user yang sedang login
            'user_id' => Auth::id(),

            // Mapping kolom Excel ke Database
            // Pastikan header di Excel menggunakan huruf kecil & underscore
            'nama_rekanan' => $row['nama_rekanan'],
            'no_rekening' => $row['no_rekening'] ?? null,
            'nama_bank' => $row['nama_bank'] ?? null,
            'npwp' => $row['npwp'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_rekanan' => 'required|string|max:255',

            // Hapus 'integer' dan 'max'. Biarkan nullable saja.
            'no_rekening' => 'nullable',
            'nama_bank' => 'nullable|string|max:50',
            'npwp' => 'nullable',
        ];
    }
}
