<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        // Mengambil setting milik user yang sedang login
        $setting = Setting::where('user_id', Auth::id())->first();

        return view('settings.index', compact('setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'tahun_aktif' => 'required|digits:4',
            'triwulan_aktif' => 'required',
            'anggaran_aktif' => 'required',
        ]);

        // 1. Simpan hasil updateOrCreate ke dalam variabel $setting
        $setting = Setting::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->all()
        );

        // 2. Ambil user yang sedang login
        $user = Auth::user();

        // 3. Update setting_id di tabel users jika belum terisi atau berubah
        $user->update([
            'setting_id' => $setting->id,
        ]);

        return back()->with('success', 'Pengaturan berhasil disimpan dan profil diperbarui!');
    }
}
