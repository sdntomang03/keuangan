<?php

namespace App\Http\Controllers;

use App\Models\DasarPajak;
use App\Models\Rkas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealisasiController extends Controller
{
    public function komponen(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // 1. Logic Periode Triwulan (Bulan)
        $tw = $request->get('tw', 'tahun');
        $bulanArray = match ($tw) {
            '1' => [1, 2, 3],
            '2' => [4, 5, 6],
            '3' => [7, 8, 9],
            '4' => [10, 11, 12],
            default => null,
        };

        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

        // 2. Query RKAS
        $dataRkas = Rkas::with(['kegiatan', 'korek', 'akb'])
            // Filter Anggaran per Bulan/TW
            ->withSum(['akbrincis as total_volume_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'volume') // Kolom volume di tabel akb_rincis
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            // Filter Realisasi (Nominal) per Bulan/TW
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'total_bruto')
            // Filter Realisasi (Volume) per Bulan/TW
            ->withSum(['belanjaRincis as volume_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'volume')
            ->where('anggaran_id', $anggaran->id)
            ->get()
            // 3. Filter: Sembunyikan yang Anggaran & Realisasinya 0 pada periode ini
            ->filter(function ($item) {
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);

        return view('realisasi.komponen', compact('dataRkas', 'anggaran', 'sekolah', 'tw'));
    }

    public function korek(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;
        $tw = $request->get('tw', 'tahun');

        // Definisikan range bulan untuk filter
        $bulanArray = match ($tw) {
            '1' => [1, 2, 3],
            '2' => [4, 5, 6],
            '3' => [7, 8, 9],
            '4' => [10, 11, 12],
            default => null, // Tahunan (ambil semua)
        };

        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
        $multiplier = 1 + ($persenPpn / 100);

        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $multiplier, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id))
                    ->select(DB::raw("SUM(
                    CASE
                        WHEN (SELECT ppn FROM belanjas WHERE belanjas.id = belanja_rincis.belanja_id) > 0
                        THEN (volume * harga_satuan * $multiplier)
                        ELSE (volume * harga_satuan)
                    END
              )"));
            }], 'total_bruto')
            ->where('anggaran_id', $anggaran->id)
            ->get()
    // --- TAMBAHKAN FILTER DI SINI ---
            ->filter(function ($item) {
                // Hanya ambil yang ada anggaran atau ada realisasi
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);
        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

        return view('realisasi.korek', compact('dataRkas', 'anggaran', 'tw', 'persenPpn', 'sekolah'));
    }
}
