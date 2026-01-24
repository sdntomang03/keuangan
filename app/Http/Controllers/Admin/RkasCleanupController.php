<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akb;
use App\Models\Anggaran;
use App\Models\Rkas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RkasCleanupController extends Controller
{
    /**
     * Tampilkan halaman form pemilihan
     */
    public function index()
    {
        // Ambil semua sekolah untuk dropdown pertama
        $sekolahs = Sekolah::orderBy('nama_sekolah', 'asc')->get();

        return view('admin.rkas.cleanup', compact('sekolahs'));
    }

    /**
     * API JSON untuk mengisi dropdown kedua
     */
    public function getAnggaranBySekolah($sekolahId)
    {
        $anggarans = Anggaran::where('sekolah_id', $sekolahId)
            ->orderBy('tahun', 'desc')
            ->get(['id', 'nama_anggaran', 'tahun', 'singkatan']); // Ambil kolom yg perlu aja

        return response()->json($anggarans);
    }

    /**
     * Proses Hapus Data RKAS
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'sekolah_id' => 'required|exists:sekolahs,id',
            'anggaran_id' => 'required|exists:anggarans,id',
        ]);

        $anggaran = Anggaran::find($request->anggaran_id);

        // Validasi keamanan: Pastikan anggaran benar-benar milik sekolah yang dipilih
        if ($anggaran->sekolah_id != $request->sekolah_id) {
            return back()->with('error', 'Data anomali: Anggaran tidak cocok dengan sekolah.');
        }

        try {
            // Gunakan Transaction agar jika salah satu gagal, semuanya batal (Data aman)
            DB::transaction(function () use ($request) {
                // 1. Hapus Data AKB (Rincian Bulanan) terlebih dahulu
                $deletedAkb = Akb::where('anggaran_id', $request->anggaran_id)->delete();

                // 2. Hapus Data RKAS (Rincian Belanja)
                $deletedRkas = Rkas::where('anggaran_id', $request->anggaran_id)->delete();
            });

            return back()->with('success', "Berhasil membersihkan data RKAS dan AKB untuk {$anggaran->singkatan} {$anggaran->tahun}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus: '.$e->getMessage());
        }
    }
}
