<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use Illuminate\Http\Request;

class RealisasiController extends Controller
{
    public function komponen(Request $request)
    {
        $user = auth()->user();
        $setting = $user->setting;

        $tahun = $request->get('tahun', $setting->tahun_aktif ?? date('Y'));
        $jenis = $request->get('jenis_anggaran', $setting->anggaran_aktif ?? 'bos');

        $dataRkas = Rkas::with(['kegiatan', 'korek', 'akb']) // Memuat relasi akb
            ->withSum('akbrincis as total_anggaran', 'nominal')
            ->withSum('belanjaRincis as total_realisasi', 'total_bruto')
            ->withSum('belanjaRincis as volume_realisasi', 'volume')
            ->where('setting_id', $user->setting_id)
            ->where('tahun', $tahun)
            ->when($jenis, fn ($q) => $q->where('jenis_anggaran', 'LIKE', "%$jenis%"))
            ->get()
            ->groupBy(['idbl', 'kodeakun']);

        return view('realisasi.komponen', compact('dataRkas', 'setting', 'tahun', 'jenis'));
    }

    public function korek(Request $request)
    {
        $user = auth()->user();
        $setting = $user->setting;

        $tahun = $request->get('tahun', $setting->tahun_aktif ?? date('Y'));
        $jenis = $request->get('jenis_anggaran', $setting->anggaran_aktif ?? 'bos');

        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            // GANTI 'total' menjadi 'nominal' di bawah ini
            ->withSum('akbrincis as total_anggaran', 'nominal')
            ->withSum('belanjaRincis as total_realisasi', 'total_bruto')
            ->where('setting_id', $user->setting_id)
            ->where('tahun', $tahun)
            ->when($jenis, fn ($q) => $q->where('jenis_anggaran', 'LIKE', "%$jenis%"))
            ->get()
            ->groupBy(['idbl', 'kodeakun']);

        return view('realisasi.korek', compact('dataRkas', 'setting', 'tahun', 'jenis'));
    }
}
