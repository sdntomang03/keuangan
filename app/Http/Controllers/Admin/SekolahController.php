<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\Sekolah;
use App\Models\Sudin; // <-- 1. PASTIKAN MODEL SUDIN DI-IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SekolahController extends Controller
{
    public function index()
    {
        $sekolahs = Sekolah::with('anggaranAktif')->paginate(10);

        return view('admin.sekolah.index', compact('sekolahs'));
    }

    public function create()
    {
        $anggarans = collect();
        // 2. Ambil data Sudin
        $sudins = Sudin::orderBy('nama', 'asc')->get();

        // Kirim $sudins ke view
        return view('admin.sekolah.create', compact('anggarans', 'sudins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'sudin' => 'nullable|exists:sudins,id', // <-- Lebih aman pakai exists
            'tahun' => 'required|integer',
            'email' => 'nullable|email|max:255',
            'telp' => 'nullable|string|max:20',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'nama_pengurus_barang' => 'nullable|string|max:255',
            'nip_pengurus_barang' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['logo', '_token']);
        $data['user_id'] = Auth::id();

        $sekolah = Sekolah::create($data);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $sekolah->update(['logo' => $path]);
        }

        Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => $request->tahun,
            'nama_anggaran' => 'Bantuan Operasional Satuan Pendidikan (BOSP) '.$request->tahun,
            'singkatan' => 'bos',
            'is_aktif' => false,
        ]);

        Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => $request->tahun,
            'nama_anggaran' => 'Bantuan Operasional Pendidikan (BOP) '.$request->tahun,
            'singkatan' => 'bop',
            'is_aktif' => false,
        ]);

        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data instansi berhasil ditambahkan!');
    }

    public function edit(Sekolah $sekolah)
    {
        $setting = $sekolah;
        $anggarans = Anggaran::where('sekolah_id', $sekolah->id)->get();
        // 3. Ambil data Sudin untuk halaman Edit
        $sudins = Sudin::orderBy('nama', 'asc')->get();

        // Kirim $sudins ke view
        return view('admin.sekolah.edit', compact('setting', 'anggarans', 'sekolah', 'sudins'));
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::findOrFail($id);

        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'sudin' => 'nullable|exists:sudins,id', // <-- Validasi exists
            'anggaran_id_aktif' => 'required|exists:anggarans,id',
            'email' => 'nullable|email|max:255',
            'telp' => 'nullable|string|max:20',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['logo', '_token', '_method']);
        $sekolah->update($data);

        if ($request->hasFile('logo')) {
            if ($sekolah->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $sekolah->update(['logo' => $path]);
        }

        \App\Models\Anggaran::where('sekolah_id', $sekolah->id)->update(['is_aktif' => false]);
        \App\Models\Anggaran::where('id', $request->anggaran_id_aktif)->update(['is_aktif' => true]);

        return redirect()->route('admin.sekolah.index')
            ->with('success', "Data {$sekolah->nama_sekolah} berhasil diperbarui!");
    }
}
