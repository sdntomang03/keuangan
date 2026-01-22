<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Rkas;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $sekolahId = Auth::user()->sekolah_id;
        $anggaran = $request->anggaran_data; // Data dari Middleware

        // 1. CEK APAKAH ANGGARAN ADA
        if (! $anggaran) {
            // Jika tidak ada anggaran aktif, kirim statistik nol agar view tidak error
            $stats = [
                'total_bos' => 0, 'harga_bos' => 0, 'pajak_bos' => 0,
                'total_bop' => 0, 'harga_bop' => 0, 'pajak_bop' => 0,
            ];
            $setting = Sekolah::where('id', $sekolahId)->first();

            // Anda bisa tambahkan pesan warning untuk ditampilkan di dashboard
            session()->now('warning', 'Silakan pilih atau import anggaran terlebih dahulu di menu Pengaturan.');

            return view('dashboard', compact('setting', 'stats'));
        }

        // 2. JIKA ANGGARAN ADA, JALANKAN LOGIKA NORMAL
        $tahunAktif = $anggaran->tahun;
        $setting = Sekolah::where('id', $sekolahId)->first();

        // Gunakan query yang aman terhadap huruf besar/kecil (BOS/bos)
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

        return view('dashboard', compact('setting', 'stats'));
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
