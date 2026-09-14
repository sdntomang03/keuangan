<?php

namespace App\Http\Controllers;

use App\Models\PenomoranSurat;
use Illuminate\Http\Request;

class PenomoranSuratController extends Controller
{
    public function index()
    {
        $sekolahId = auth()->user()->sekolah_id;

        $penomorans = PenomoranSurat::where('sekolah_id', $sekolahId)
            ->orderBy('tahun', 'desc')
            ->orderBy('triwulan', 'asc')
            ->get();

        return view('penomoran_surat.index', compact('penomorans'));
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|min:1|max:4',
            'nomor_awal' => 'required|integer|min:1',
        ]);

        try {
            // 2. Simpan Data
            PenomoranSurat::updateOrCreate(
                [
                    'sekolah_id' => auth()->user()->sekolah_id,
                    'tahun' => $request->tahun,
                    'triwulan' => $request->triwulan,
                ],
                [
                    'nomor_awal' => $request->nomor_awal,
                ]
            );

            return back()->with('success', "Nomor awal untuk Triwulan {$request->triwulan} Tahun {$request->tahun} berhasil disimpan.");

        } catch (\Exception $e) {
            // 3. Tangkap jika terjadi error database
            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $penomoran = PenomoranSurat::findOrFail($id);

        // Otorisasi: Pastikan data milik sekolah user yang login
        if ($penomoran->sekolah_id !== auth()->user()->sekolah_id) {
            abort(403, 'Unauthorized action.');
        }

        $penomoran->delete();

        return back()->with('success', 'Pengaturan penomoran berhasil dihapus.');
    }
}
