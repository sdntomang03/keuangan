<?php

namespace App\Http\Controllers;

use App\Exports\KegiatanTemplateExport;
use App\Exports\RekananTemplateExport;
use App\Imports\KegiatanImport;
use App\Imports\RekananImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
        // Validasi File
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048', // Max 2MB
        ]);

        try {
            // Proses Import
            Excel::import(new RekananImport, $request->file('file_excel'));

            // Redirect Sukses
            return redirect()->back()->with('success', 'Data Rekanan berhasil diimport!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Error Validasi Data Excel (Misal nama kosong)
            $failures = $e->failures();
            $message = 'Error pada baris ke-'.$failures[0]->row().': '.implode(', ', $failures[0]->errors());

            return redirect()->back()->with('error', $message);

        } catch (\Exception $e) {
            // Error Umum
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal import: '.$e->getMessage());
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
