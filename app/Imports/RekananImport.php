<?php

namespace App\Imports;

use App\Models\Rekanan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RekananImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * LANGKAH KUNCI: Ubah data Angka menjadi String SEBELUM Validasi
     * Ini mengatasi error: "The no_rekening must be a string."
     */
    public function prepareForValidation($data, $index)
    {
        // Daftar kolom yang di Excel sering terdeteksi sebagai angka (General/Number)
        // tapi di Database kita butuh sebagai Teks/String.
        $fieldsToString = ['no_rekening', 'npwp', 'pkp', 'no_telp', 'ket'];

        foreach ($fieldsToString as $field) {
            if (isset($data[$field])) {
                // Paksa ubah jadi string. Contoh: 123456 -> "123456"
                $data[$field] = (string) $data[$field];
            }
        }

        return $data;
    }

    /**
     * Rules Validasi
     */
    public function rules(): array
    {
        return [
            // Wajib Diisi
            'nama_rekanan' => 'required|string|max:255',

            // Opsional (Boleh Kosong)
            'no_rekening' => 'nullable|string|max:50',
            'nama_bank' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string', // Aman karena sudah dicast ke string di atas
            'alamat' => 'nullable|string',
            'alamat_2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'pic' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'nama_pimpinan' => 'nullable|string|max:100',
            'ket' => 'nullable|string',
        ];
    }

    /**
     * Mapping Data ke Database
     */
    public function model(array $row)
    {
        // Logika PKP: Pastikan tersimpan sebagai string/angka yang benar
        $pkpStatus = isset($row['pkp']) ? (string) $row['pkp'] : '0';

        return new Rekanan([
            // --- Data Sistem ---
            'user_id' => Auth::id(),
            'sekolah_id' => Auth::user()->sekolah_id,

            // --- Data dari Excel ---
            'nama_rekanan' => $row['nama_rekanan'],

            // Gunakan casting (string) lagi disini untuk keamanan ganda saat save ke DB
            'no_rekening' => isset($row['no_rekening']) ? (string) $row['no_rekening'] : null,
            'nama_bank' => $row['nama_bank'] ?? null,
            'npwp' => isset($row['npwp']) ? (string) $row['npwp'] : null,
            'pkp' => $pkpStatus,

            'alamat' => $row['alamat'] ?? null,
            'alamat_2' => $row['alamat_2'] ?? null, // Pastikan header di Excel bernama 'alamat_2' bukan 'alamat2'
            'kota' => $row['kota'] ?? null,
            'provinsi' => $row['provinsi'] ?? null,
            'pic' => $row['pic'] ?? null,
            'jabatan' => $row['jabatan'] ?? null,
            'no_telp' => isset($row['no_telp']) ? (string) $row['no_telp'] : null,
            'nama_pimpinan' => $row['nama_pimpinan'] ?? null,
            'ket' => $row['ket'] ?? null,
        ]);
    }
}
