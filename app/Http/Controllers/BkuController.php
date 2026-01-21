<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class BkuController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan pilih Anggaran Aktif (BOS/BOP) terlebih dahulu.');
        }

        // 2. Ambil data sekolah untuk header laporan
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);

        // 3. Ambil data BKU khusus untuk anggaran_id yang aktif
        $bkus = Bku::with(['belanja.kegiatan', 'belanja.rekanan', 'belanja.korek'])
            ->where('anggaran_id', $anggaran->id)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_urut', 'asc')
            ->get();

        return view('bku.index', compact('bkus', 'anggaran', 'sekolah'));
    }
}
