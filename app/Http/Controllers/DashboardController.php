<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\DasarPajak;
use App\Models\Rkas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $sekolahId = Auth::user()->sekolah_id;
        $anggaran = $request->anggaran_data; // Data dari Middleware
        $tw = $request->get('tw', 'tahun'); // Ambil filter Triwulan dari request

        $setting = Sekolah::where('id', $sekolahId)->first();

        // 1. CEK APAKAH ANGGARAN ADA
        if (! $anggaran) {
            // Jika tidak ada anggaran aktif, kirim statistik dan data kosong agar view tidak error
            $stats = [
                'total_bos' => 0, 'harga_bos' => 0, 'pajak_bos' => 0,
                'total_bop' => 0, 'harga_bop' => 0, 'pajak_bop' => 0,
            ];
            $dataRkas = collect();
            $persenPpn = 11;

            session()->now('warning', 'Silakan pilih atau import anggaran terlebih dahulu di menu Pengaturan.');

            return view('dashboard', compact('setting', 'stats', 'anggaran', 'dataRkas', 'tw', 'persenPpn'));
        }

        // 2. JIKA ANGGARAN ADA, HITUNG STATISTIK BOS & BOP
        $tahunAktif = $anggaran->tahun;

        $idBos = Anggaran::where('tahun', $tahunAktif)
            ->whereIn('singkatan', ['BOS', 'bos'])
            ->where('sekolah_id', $anggaran->sekolah_id)
            ->value('id');

        $idBop = Anggaran::where('tahun', $tahunAktif)
            ->whereIn('singkatan', ['BOP', 'bop'])
            ->where('sekolah_id', $anggaran->sekolah_id)
            ->value('id');

        $stats = [
            'total_bos' => $idBos ? Rkas::where('anggaran_id', $idBos)->count() : 0,
            'harga_bos' => $idBos ? Rkas::where('anggaran_id', $idBos)->sum('totalharga') : 0,
            'pajak_bos' => $idBos ? Rkas::where('anggaran_id', $idBos)->sum('totalpajak') : 0,

            'total_bop' => $idBop ? Rkas::where('anggaran_id', $idBop)->count() : 0,
            'harga_bop' => $idBop ? Rkas::where('anggaran_id', $idBop)->sum('totalharga') : 0,
            'pajak_bop' => $idBop ? Rkas::where('anggaran_id', $idBop)->sum('totalpajak') : 0,
        ];

        // 3. LOGIKA KOREK (REKAP REALISASI VS ANGGARAN BERDASARKAN FILTER TW)
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
            ->filter(function ($item) {
                // Hanya ambil yang ada anggaran atau ada realisasi
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl']);

        // 4. KEMBALIKAN KE VIEW DENGAN SEMUA DATA
        return view('dashboard', compact('setting', 'stats', 'anggaran', 'dataRkas', 'tw', 'persenPpn'));
    }

    public function switch(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'singkatan' => 'required',
            'tahun' => 'required',
        ]);

        $sekolahId = auth()->user()->sekolah_id;

        // 2. Cari data anggaran tujuan (misal pindah dari BOS ke BOP)
        $target = Anggaran::where('sekolah_id', $sekolahId)
            ->where('tahun', $request->tahun)
            ->where('singkatan', $request->singkatan)
            ->first();

        // 3. Jika data tidak ada (belum diimport), kembalikan dengan pesan error
        if (! $target) {
            return back()->with('error', "Data Anggaran {$request->singkatan} {$request->tahun} belum tersedia.");
        }

        // 4. Nonaktifkan semua anggaran sekolah ini, lalu aktifkan yang dipilih
        Anggaran::where('sekolah_id', $sekolahId)->update(['is_aktif' => false]);

        $target->is_aktif = true;
        $target->save();

        return back()->with('success', "Berhasil beralih ke {$target->singkatan} {$target->tahun}");
    }
}
