<?php

namespace App\Http\Controllers;

use App\Exports\RekananExport;
use App\Models\Rekanan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RekananController extends Controller
{
    /**
     * Tampilkan daftar rekanan milik sekolah yang sedang login.
     */
    public function index()
    {
        // Ambil ID Sekolah dari user yang login
        // Pastikan tabel users Anda punya kolom sekolah_id
        $sekolahId = Auth::user()->sekolah_id;

        $rekanans = Rekanan::where('sekolah_id', $sekolahId)
            ->latest()
            ->paginate(10);

        return view('rekanan.index', compact('rekanans'));
    }

    /**
     * Form tambah data.
     */
    public function create()
    {
        return view('rekanan.create');
    }

    /**
     * Simpan data ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_rekanan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'alamat2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',

            // Personalia
            'nama_pimpinan' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:100',

            // Keuangan
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string|max:50',
            'ket' => 'nullable|string',
        ]);

        // 2. Tambahkan sekolah_id secara otomatis
        // Asumsi: User yang login terhubung ke sekolah
        $validated['sekolah_id'] = Auth::user()->sekolah_id;

        // Jika user belum punya sekolah_id, bisa hardcode dulu untuk testing:
        // $validated['sekolah_id'] = 1;

        // 3. Simpan
        Rekanan::create($validated);

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil ditambahkan.');
    }

    /**
     * Form edit data.
     */
    public function edit(Rekanan $rekanan)
    {
        // Keamanan: Pastikan user hanya bisa edit rekanan sekolahnya sendiri
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return view('rekanan.edit', compact('rekanan'));
    }

    /**
     * Update data di database.
     */
    public function update(Request $request, Rekanan $rekanan)
    {
        // Keamanan
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403);
        }

        // 1. Validasi
        $validated = $request->validate([
            'nama_rekanan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'alamat2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'nama_pimpinan' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string|max:50',
            'ket' => 'nullable|string',
        ]);

        // 2. Update (sekolah_id tidak perlu di-update agar tidak pindah sekolah)
        $rekanan->update($validated);

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil diperbarui.');
    }

    /**
     * Hapus data.
     */
    public function destroy(Rekanan $rekanan)
    {
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403);
        }

        $rekanan->delete();

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil dihapus.');
    }

    public function destroyAll()
    {
        $sekolahId = Auth::user()->sekolah_id;

        if (! $sekolahId) {
            return redirect()->route('setting.rekanan.index')
                ->with('error', 'Identitas Sekolah tidak ditemukan.');
        }

        // Mulai Transaksi
        DB::beginTransaction();

        try {
            // Coba lakukan penghapusan
            $deletedCount = Rekanan::where('sekolah_id', $sekolahId)->delete();

            // Jika berhasil tanpa error, Commit perubahan
            DB::commit();

            if ($deletedCount === 0) {
                return redirect()->route('setting.rekanan.index')
                    ->with('warning', 'Tidak ada data rekanan yang dihapus.');
            }

            return redirect()->route('setting.rekanan.index')
                ->with('success', "Berhasil menghapus $deletedCount data rekanan.");

        } catch (QueryException $e) {
            // Jika terjadi error database, Rollback (batalkan semua)
            DB::rollBack();

            // Cek Kode Error SQL 23000 (Integrity Constraint Violation)
            if ($e->getCode() == '23000') {
                return redirect()->route('setting.rekanan.index')
                    ->with('error', 'GAGAL MENGHAPUS! Data Rekanan masih digunakan dalam data Belanja/SPJ. Silakan hapus data Belanja terkait terlebih dahulu.');
            }

            // Error lain
            return redirect()->route('setting.rekanan.index')
                ->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        } catch (\Exception $e) {
            // Error umum PHP
            DB::rollBack();

            return redirect()->route('setting.rekanan.index')
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function export()
    {
        // 1. Buat nama file yang unik (opsional, agar ada timestamp)
        $timestamp = Carbon::now()->format('d-m-Y_H-i');
        $namaFile = "Data_Rekanan_{$timestamp}.xlsx";

        // 2. Panggil fungsi download
        // Parameter 1: Class Export yang kita buat tadi
        // Parameter 2: Nama file yang akan terdownload di browser user
        return Excel::download(new RekananExport, $namaFile);
    }
}
