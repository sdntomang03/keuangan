<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akb;
use App\Models\AkbRinci;
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

        // Validasi Keamanan: Pastikan anggaran_id yang akan dihapus BENAR-BENAR milik sekolah tersebut
        if ($anggaran->sekolah_id != $request->sekolah_id) {
            return back()->with('error', 'Akses ditolak: Data anggaran ini tidak terdaftar untuk sekolah Anda.');
        }

        try {
            DB::transaction(function () use ($request) {
                // Hapus murni berdasarkan anggaran_id
                // URUTAN PENTING: Hapus data anak (rincian) dulu, baru data induk (master)

                AkbRinci::where('anggaran_id', $request->anggaran_id)->delete(); // 1. Hapus rincian bulanannya
                Akb::where('anggaran_id', $request->anggaran_id)->delete();      // 2. Hapus master AKB-nya
                Rkas::where('anggaran_id', $request->anggaran_id)->delete();     // 3. Hapus master RKAS-nya
            });

            return back()->with('success', "Berhasil membersihkan seluruh data RKAS, Master AKB, dan Rincian AKB untuk {$anggaran->singkatan} {$anggaran->tahun}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus: '.$e->getMessage());
        }
    }
}
