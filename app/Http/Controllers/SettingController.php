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

    /**
     * Menampilkan halaman form import JSON
     */
    public function importKegiatanJson()
    {
        // Pengecekan akses opsional (jika dibutuhkan)
        // abort_if(auth()->user()->role != 'admin', 403, 'Hanya Admin yang bisa import.');

        return view('admin.kegiatan.import');
    }

    /**
     * Memproses file JSON dan memecahnya ke 3 tabel (Program, SubProgram, KegiatanManual)
     */
    public function storeImportKegiatanJson(\Illuminate\Http\Request $request)
    {
        // 1. Tambahkan validasi untuk sumber_dana_id yang dipilih dari form
        $request->validate([
            'file_json' => 'required|file|mimetypes:application/json,text/plain|max:10240',

        ], [
            'file_json.required' => 'File JSON wajib diunggah.',

        ]);

        $file = $request->file('file_json');
        $data = json_decode(file_get_contents($file->getPathname()), true);

        if (! is_array($data)) {
            return back()->withErrors(['error' => 'Format isi JSON tidak valid.']);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $count = 0;
            foreach ($data as $item) {
                // Fleksibilitas Key: Mendukung huruf besar atau huruf kecil
                $namaProgram = $item['Program'] ?? $item['program'] ?? null;
                $namaSubProgram = $item['Sub Program'] ?? $item['sub_program'] ?? null;
                $namaUraian = $item['Uraian'] ?? $item['uraian'] ?? null;

                // Jika 3 data utama kosong, lewati baris ini
                if (empty($namaProgram) || empty($namaSubProgram) || empty($namaUraian)) {
                    continue;
                }

                // Generate ID Otomatis jika id_kegiatan tidak ada di file JSON
                if (empty($idKegiatan)) {
                    $idKegiatan = 'KGT-'.strtoupper(substr(md5($namaUraian), 0, 8));
                }

                // A. Cari atau Buat Program
                $program = \App\Models\Program::firstOrCreate([
                    'nama_program' => trim($namaProgram),
                ]);

                // B. Cari atau Buat Sub Program
                $subProgram = \App\Models\SubProgram::firstOrCreate([
                    'program_id' => $program->id,
                    'nama_sub_program' => trim($namaSubProgram),
                ]);

                // C. Cari atau Buat Uraian Kegiatan (Disimpan ke tabel uraian_kegiatans)
                $uraian = \App\Models\UraianKegiatan::firstOrCreate([
                    'sub_program_id' => $subProgram->id,
                    'nama_uraian' => trim($namaUraian),
                ]);

                $count++;
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('setting.kegiatan.index')
                ->with('success', "Luar biasa! Berhasil memecah dan mengimpor $count rincian kegiatan ke dalam database.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return back()->withErrors(['error' => 'Sistem gagal memproses data. Pesan Error: '.$e->getMessage()]);
        }
    }
}
