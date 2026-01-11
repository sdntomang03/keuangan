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

        Setting::updateOrCreate(
            ['user_id' => Auth::id()], // Cari berdasarkan user yang login
            $request->all()            // Update/Insert sisa datanya
        );

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
