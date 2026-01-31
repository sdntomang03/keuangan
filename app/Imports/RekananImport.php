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
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pastikan kolom 'pkp' diisi 1 (Ya) atau 0 (Tidak) di Excel.
        // Jika kosong dianggap 0.
        $pkpStatus = isset($row['pkp']) ? $row['pkp'] : 0;

        return new Rekanan([
            // --- Data Sistem ---
            'user_id' => Auth::id(),
            // Asumsi tabel rekanan butuh sekolah_id (berdasarkan konteks aplikasi Anda sebelumnya)
            'sekolah_id' => Auth::user()->sekolah_id,

            // --- Data dari Excel ---
            'nama_rekanan' => $row['nama_rekanan'],
            'no_rekening' => $row['no_rekening'] ?? null,
            'nama_bank' => $row['nama_bank'] ?? null,
            'npwp' => $row['npwp'] ?? null,
            'pkp' => $pkpStatus,
            'alamat' => $row['alamat'] ?? null,
            'alamat_2' => $row['alamat_2'] ?? null,
            'kota' => $row['kota'] ?? null,
            'provinsi' => $row['provinsi'] ?? null,
            'pic' => $row['pic'] ?? null,           // Person In Charge
            'jabatan' => $row['jabatan'] ?? null,       // Jabatan PIC
            'no_telp' => $row['no_telp'] ?? null,
            'nama_pimpinan' => $row['nama_pimpinan'] ?? null, // Nama Pimpinan Perusahaan
            'ket' => $row['ket'] ?? null,           // Keterangan / Jenis (Penyedia/Tukang/dll)
        ]);
    }

    public function rules(): array
    {
        return [
            // Wajib Diisi
            'nama_rekanan' => 'required|string|max:255',

            // Opsional (Boleh Kosong)
            'no_rekening' => 'nullable|string|max:50', // String agar angka 0 di depan tidak hilang
            'nama_bank' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string', // Di Excel isi 1 atau 0
            'alamat' => 'nullable|string',
            'alamat_2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'pic' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'nama_pimpinan' => 'nullable|string|max:100',
            'ket' => 'nullable|string', // atau integer jika ini relasi (misal jenis_rekanan_id)
        ];
    }
}
