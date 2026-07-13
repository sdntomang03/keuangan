<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Ekskul;
use App\Models\LaporanEkskul;
use App\Models\LaporanEkskulFoto;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;

        // =========================================================================
        // 1. INISIALISASI & LOGIKA DASHBOARD EKSKUL
        // =========================================================================
        $totalEkskul = 0;
        $totalPertemuan = 0;
        $totalFoto = 0;
        $laporanTerbaru = collect();
        $myEkskuls = collect();

        // Jika user memiliki izin mengelola ekskul, jalankan perhitungannya
        if ($user->can('input-ekskul')) {
            // --- TAMBAHKAN BARIS INI untuk mengambil data objek ekskul lengkap ---
            $myEkskuls = Ekskul::where('user_id', $user->id)->get();
            $totalEkskul = $myEkskuls->count();

            $myEkskulIds = $myEkskuls->pluck('id');

            $totalPertemuan = LaporanEkskul::whereIn('ekskul_id', $myEkskulIds)->count();

            $totalFoto = LaporanEkskulFoto::whereHas('laporanEkskul', function ($query) use ($myEkskulIds) {
                $query->whereIn('ekskul_id', $myEkskulIds);
            })->count();

            $laporanTerbaru = LaporanEkskul::with(['ekskul'])
                ->withCount('fotos')
                ->whereIn('ekskul_id', $myEkskulIds)
                ->latest()
                ->take(5)
                ->get();
        }

        // =========================================================================
        // PENGECEKAN PERMISSION (Pemisah View)
        // =========================================================================
        // Jika user TIDAK memiliki permission 'view-anggaran', langsung return ke view khusus
        // (Pastikan nama permission 'view-anggaran' disesuaikan dengan yang ada di database Anda)
        if (! $user->can('view-anggaran')) {
            return view('dashboard-ekskul', compact(
                'totalEkskul', 'totalPertemuan', 'totalFoto', 'laporanTerbaru', 'myEkskuls'
            ));
        }

        // =========================================================================
        // 2. INISIALISASI DASAR KEUANGAN (Hanya dieksekusi jika punya permission)
        // =========================================================================
        $anggaran = $request->anggaran_data; // Data dari Middleware
        $tw = $request->get('tw', 'tahun'); // Ambil filter Triwulan dari request
        $setting = Sekolah::where('id', $sekolahId)->first();

        // CEK APAKAH ANGGARAN ADA
        if (! $anggaran) {
            $stats = [
                'total_bos' => 0, 'harga_bos' => 0, 'pajak_bos' => 0,
                'total_bop' => 0, 'harga_bop' => 0, 'pajak_bop' => 0,
            ];
            $dataRkas = collect();
            $persenPpn = 11;

            session()->now('warning', 'Silakan pilih atau import anggaran terlebih dahulu di menu Pengaturan.');

            // Kembalikan view utama
            return view('dashboard', compact(
                'setting', 'stats', 'anggaran', 'dataRkas', 'tw', 'persenPpn',
                'totalEkskul', 'totalPertemuan', 'totalFoto', 'laporanTerbaru'
            ));
        }

        // =========================================================================
        // 3. HITUNG STATISTIK BOS & BOP
        // =========================================================================
        $tahunAktif = $anggaran->tahun;

        $idBos = \App\Models\Anggaran::where('tahun', $tahunAktif)
            ->whereIn('singkatan', ['BOS', 'bos'])
            ->where('sekolah_id', $anggaran->sekolah_id)
            ->value('id');

        $idBop = \App\Models\Anggaran::where('tahun', $tahunAktif)
            ->whereIn('singkatan', ['BOP', 'bop'])
            ->where('sekolah_id', $anggaran->sekolah_id)
            ->value('id');

        $stats = [
            'total_bos' => $idBos ? \App\Models\Rkas::where('anggaran_id', $idBos)->count() : 0,
            'harga_bos' => $idBos ? \App\Models\Rkas::where('anggaran_id', $idBos)->sum('totalharga') : 0,
            'pajak_bos' => $idBos ? \App\Models\Rkas::where('anggaran_id', $idBos)->sum('totalpajak') : 0,

            'total_bop' => $idBop ? \App\Models\Rkas::where('anggaran_id', $idBop)->count() : 0,
            'harga_bop' => $idBop ? \App\Models\Rkas::where('anggaran_id', $idBop)->sum('totalharga') : 0,
            'pajak_bop' => $idBop ? \App\Models\Rkas::where('anggaran_id', $idBop)->sum('totalpajak') : 0,
        ];

        // =========================================================================
        // 4. LOGIKA REKAP (REALISASI VS ANGGARAN)
        // =========================================================================
        $bulanArray = match ($tw) {
            '1' => [1, 2, 3],
            '2' => [4, 5, 6],
            '3' => [7, 8, 9],
            '4' => [10, 11, 12],
            default => null,
        };

        $persenPpn = \App\Models\DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
        $multiplier = 1 + ($persenPpn / 100);

        $dataRkas = \App\Models\Rkas::with(['kegiatan', 'korek'])
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id)
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $multiplier, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id))
                    ->select(\Illuminate\Support\Facades\DB::raw("SUM(
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
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl']);

        // Final Return menggabungkan data Keuangan dan Ekskul (Untuk Admin/Kepsek)
        return view('dashboard', compact(
            'setting', 'stats', 'anggaran', 'dataRkas', 'tw', 'persenPpn',
            'totalEkskul', 'totalPertemuan', 'totalFoto', 'laporanTerbaru'
        ));
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

        // 5. UPDATE UTAMA: Sinkronkan anggaran_id_aktif di tabel sekolah
        $sekolah = auth()->user()->sekolah;
        if ($sekolah) {
            $sekolah->anggaran_id_aktif = $target->id;
            $sekolah->save();
        }

        return back()->with('success', "Berhasil beralih ke {$target->singkatan} {$target->tahun}");
    }

    public function switchTw(Request $request)
    {
        $request->validate([
            'tw' => 'required|in:1,2,3,4,tahun',
        ]);

        // Ambil relasi sekolah dari user yang sedang login
        $sekolah = auth()->user()->sekolah;

        if ($sekolah) {
            // Karena triwulan_aktif bertipe integer, kita ubah 'tahun' menjadi 0
            $tw = $request->tw === 'tahun' ? 0 : (int) $request->tw;

            $sekolah->triwulan_aktif = $tw;
            $sekolah->save();
        }

        return back()->with('success', 'Periode Triwulan berhasil diubah.');
    }
}
