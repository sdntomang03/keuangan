<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Bku;
use App\Models\Sekolah;
use App\Services\BkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BkuController extends Controller
{
    public function index(Request $request, BkuService $bkuService)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // Panggil service untuk mendapatkan data yang sudah ada saldonya
        $bkus = $bkuService->getBkuWithBalance($anggaran->id);
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);

        return view('bku.index', compact('bkus', 'anggaran', 'sekolah'));
    }

    /**
     * Membatalkan posting transaksi (Mundur dari 'posted' ke 'draft')
     */
    public function unpost($id, Request $request)
    {

        // Ambil data anggaran dari middleware (untuk memastikan sinkronisasi tahun anggaran)
        $anggaran = $request->anggaran_data;
        // 1. Cari data belanja dengan proteksi user_id dan relasi pajaks
        $belanja = Belanja::with('pajaks')
            ->where('anggaran_id', $anggaran->id)
            ->findOrFail($id);

        // 2. Validasi status
        if ($belanja->status !== 'posted') {
            return back()->with('error', 'Hanya transaksi berstatus "posted" yang dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($belanja, $anggaran) {

                // 3. Hapus semua catatan BKU yang terkait (Belanja Utama + Pajak-pajaknya)
                DB::table('bkus')
                    ->where('belanja_id', $belanja->id)
                    ->where('anggaran_id', $anggaran->id)
                    ->delete();

                // 4. Kembalikan status pajak menjadi belum diterima
                foreach ($belanja->pajaks as $pajak) {
                    $pajak->update(['is_terima' => false]);
                }

                // 5. Kembalikan status belanja ke draft agar bisa diedit kembali
                $belanja->update(['status' => 'draft']);
            });

            return back()->with('success', "Posting transaksi {$belanja->no_bukti} berhasil dibatalkan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        // 1. Cari data BKU berdasarkan ID dan proteksi user_id
        $bku = Bku::where('user_id', auth()->id())->findOrFail($id);

        // 2. Proteksi: Jangan izinkan hapus jika baris berasal dari Belanja (harus lewat Unpost)
        if ($bku->belanja_id) {
            return back()->with('error', 'Transaksi belanja tidak bisa dihapus langsung. Gunakan tombol "Batal Post".');
        }

        try {
            DB::transaction(function () use ($bku) {
                // 3. Eksekusi hapus baris BKU
                // Ini akan memicu Model Event 'deleted' di Model Bku untuk menghapus Penerimaan
                $bku->delete();
            });

            return back()->with('success', 'Baris BKU dan data sumber berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dana: '.$e->getMessage());
        }
    }
}
