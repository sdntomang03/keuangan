<?php

namespace App\Http\Controllers;

use App\Exports\KegiatanTemplateExport;
use App\Exports\RekananTemplateExport;
use App\Imports\KegiatanImport;
use App\Imports\RekananImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SettingController extends Controller
{
    // 1. Tampilkan Halaman Import
    public function importRekananView()
    {
        return view('settings.import_rekanan');
    }

    public function downloadTemplateRekanan()
    {
        return Excel::download(new RekananTemplateExport, 'template_rekanan.xlsx');
    }

    // 2. Proses Upload Excel
    public function importRekananStore(Request $request)
    {
        // 1. Validasi File Upload
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048', // Maksimal 2MB
        ]);

        try {
            // 2. Eksekusi Import
            // Pastikan file excel memiliki header yang sesuai dengan RekananImport.php
            Excel::import(new RekananImport, $request->file('file_excel'));

            // 3. Redirect Sukses
            return redirect()->back()->with('success', 'Data Rekanan berhasil diimport!');

        } catch (ValidationException $e) {
            // 4. Menangkap Error Validasi Data (Contoh: Nama kosong, format salah)
            $failures = $e->failures();

            // Mengumpulkan pesan error (ambil maksimal 5 error pertama agar notifikasi tidak kepanjangan)
            $errorMessages = [];
            foreach ($failures as $index => $failure) {
                if ($index >= 5) {
                    break;
                } // Batasi tampilan error

                $row = $failure->row();
                $attribute = $failure->attribute(); // Nama kolom yang error
                $errors = implode(', ', $failure->errors());

                $errorMessages[] = "Baris {$row} ({$attribute}): {$errors}";
            }

            // Tambahkan info jika error lebih dari 5
            if (count($failures) > 5) {
                $errorMessages[] = '...dan '.(count($failures) - 5).' error lainnya.';
            }

            return redirect()->back()->with('error', 'Gagal Import Validasi: '.implode(' | ', $errorMessages));

        } catch (\Exception $e) {
            // 5. Menangkap Error Sistem Lainnya (Misal: Struktur DB beda, Header Excel salah)
            Log::error('Import Rekanan Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    public function importKegiatanView()
    {
        // Tidak perlu kirim data anggaran
        return view('settings.import_kegiatan');
    }

    public function downloadTemplateKegiatan()
    {
        return Excel::download(new KegiatanTemplateExport, 'template_kegiatan_master.xlsx');
    }

    public function importKegiatanStore(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            // Langsung panggil Import Class
            Excel::import(new KegiatanImport, $request->file('file_excel'));

            return redirect()->back()->with('success', 'Data Master Kegiatan berhasil diimport/diupdate!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            return redirect()->back()->with('error', 'Error baris ke-'.$failures[0]->row().': '.implode(', ', $failures[0]->errors()));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: '.$e->getMessage());
        }
    }
}
