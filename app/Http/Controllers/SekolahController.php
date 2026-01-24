<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SekolahController extends Controller
{
    public function index()
    {
        // Ambil data sekolah langsung melalui relasi user yang login
        $setting = Auth::user()->sekolah;

        if (! $setting) {
            return redirect()->route('dashboard')->with('error', 'Instansi Anda belum terdaftar.');
        }

        // Ambil anggaran melalui relasi sekolah
        $anggarans = $setting->anggarans;

        return view('sekolah.index', compact('setting', 'anggarans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'triwulan_aktif' => 'required|integer|between:1,4',
            'anggaran_id_aktif' => 'required|exists:anggarans,id',

            // Field Baru
            'alamat' => 'nullable|string',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kota' => 'nullable|string|max:100',
            'kodepos' => 'nullable|string|max:10',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        // Ambil semua input kecuali logo untuk sementara
        $data = $request->except('logo');

        // Cek jika ada upload logo baru
        if ($request->hasFile('logo')) {
            // Ambil data sekolah lama untuk hapus logo lama jika ada
            $sekolahLama = Sekolah::where('user_id', Auth::id())->first();
            if ($sekolahLama && $sekolahLama->logo) {
                \Storage::disk('public')->delete($sekolahLama->logo);
            }

            // Simpan logo baru
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        // Ambil ID sekolah dari user yang sedang login
        $sekolahId = Auth::user()->sekolah_id;

        // Simpan atau Update
        $sekolah = Sekolah::updateOrCreate(
            ['id' => $sekolahId], // Kunci pencarian: Cari sekolah yang ID-nya ini
            $data                 // Data yang akan diupdate/diisi
        );

        // 2. Update user (pastikan foreign key sinkron)
        Auth::user()->update([
            'sekolah_id' => $sekolah->id,
        ]);

        // --- LOGIKA TOGGLE IS_AKTIF PADA TABEL ANGGARAN ---

        // 3. Set semua anggaran sekolah ini menjadi false
        \App\Models\Anggaran::where('sekolah_id', $sekolah->id)
            ->update(['is_aktif' => false]);

        // 4. Set anggaran yang dipilih menjadi true
        \App\Models\Anggaran::where('id', $request->anggaran_id_aktif)
            ->update(['is_aktif' => true]);

        return back()->with('success', 'Pengaturan berhasil disimpan. Anggaran aktif telah diperbarui!');
    }
}
