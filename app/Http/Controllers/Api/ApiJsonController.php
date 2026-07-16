<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rkas;
use Illuminate\Http\Request;

class ApiJsonController extends Controller
{
    /**
     * Menampilkan data JSON RKAS dan Realisasi.
     */
    public function getRkas(Request $request)
    {
        // Validasi input untuk memastikan anggaran_id tersedia
        $request->validate([
            'anggaran_id' => 'required|integer',
        ]);

        $anggaranId = $request->anggaran_id;

        // Query data RKAS beserta relasi kegiatan, korek, dan akbRincis
        $dataRkas = Rkas::with(['kegiatan', 'korek', 'akbRincis'])
            ->where('anggaran_id', $anggaranId)
            ->whereNotNull('koefisien')         // Memastikan koefisien tidak bernilai NULL
            ->where('totalharga', '>', 0)      // Memastikan totalharga bukan string kosong ("")
            ->get();

        // Mengembalikan format JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Data RKAS berhasil dimuat.',
            'data' => $dataRkas,
        ], 200);
    }
}
