<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DasarPajak;
use App\Models\Rkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiJsonController extends Controller
{
    /**
     * Menampilkan data JSON RKAS dan Realisasi.
     */
    public function getRkasRealisasi(Request $request)
    {
        // Validasi input untuk memastikan anggaran_id tersedia
        $request->validate([
            'anggaran_id' => 'required|integer',
            'bulan' => 'nullable|array',
        ]);

        $anggaranId = $request->anggaran_id;
        $bulanArray = $request->bulan; // Opsional: filter berdasarkan bulan

        // Mengambil persentase PPN untuk kalkulasi realisasi dengan pajak
        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
        $multiplier = 1 + ($persenPpn / 100);

        // Query data RKAS beserta relasi dan sum (agregat) anggarannya
        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            // Kalkulasi Total Anggaran dari tabel akbrincis
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaranId, $bulanArray) {
                $query->where('anggaran_id', $anggaranId)
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')

            // Kalkulasi Total Realisasi dari tabel belanja_rincis
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaranId, $multiplier, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaranId))
                    ->select(DB::raw("SUM(
                        CASE
                            WHEN (SELECT ppn FROM belanjas WHERE belanjas.id = belanja_rincis.belanja_id) > 0
                            THEN (volume * harga_satuan * $multiplier)
                            ELSE (volume * harga_satuan)
                        END
                    )"));
            }], 'total_bruto')

            ->where('anggaran_id', $anggaranId)
            ->get();

        // Mengembalikan format JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Data RKAS dan Realisasi berhasil dimuat.',
            'data' => $dataRkas,
        ], 200);
    }
}
