<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SekolahController extends Controller
{
    /**
     * Menampilkan daftar semua sekolah (untuk Dashboard Admin)
     */
    public function index()
    {
        // Memuat relasi anggaranAktif tidak akan menduplikasi baris sekolah
        $sekolahs = Sekolah::with('anggaranAktif')->paginate(10);

        return view('admin.sekolah.index', compact('sekolahs'));
    }

    /**
     * Form tambah sekolah baru
     */
    public function create()
    {
        // Karena sekolah baru belum punya anggaran, kita kirim koleksi kosong
        $anggarans = collect();

        return view('admin.sekolah.create', compact('anggarans'));
    }

    public function store(Request $request)
    {
        // 1. Validasi (Sama dengan Update, tapi 'logo' wajib/required jika perlu)
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'tahun' => 'required|integer', // Dropdown Anggaran
            'email' => 'nullable|email|max:255',
            'telp' => 'nullable|string|max:20',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Siapkan Data
        $data = $request->except(['logo', '_token']);
        $data['user_id'] = Auth::id();
        // 3. Simpan Data Baru ke Database
        $sekolah = Sekolah::create($data);

        // 4. Handle Upload Logo (Jika ada)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $sekolah->update(['logo' => $path]);
        }

        Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => $request->tahun,
            'nama_anggaran' => 'Bantuan Operasional Satuan Pendidikan (BOSP) '.$request->tahun,
            'singkatan' => 'bos', // Sesuaikan dengan kolom di database Anda (misal: jenis/kategori)
            'is_aktif' => false,   // Set BOS sebagai default aktif
        ]);

        // B. Buat Anggaran BOP (Default Tidak Aktif)
        Anggaran::create([
            'sekolah_id' => $sekolah->id,
            'tahun' => $request->tahun,
            'nama_anggaran' => 'Bantuan Operasional Pendidikan (BOP) '.$request->tahun,
            'singkatan' => 'bop', // Sesuaikan dengan kolom di database Anda (misal: jenis/kategori)
            'is_aktif' => false,   // Set BOS sebagai default aktif
        ]);

        // 6. Redirect
        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data instansi berhasil ditambahkan!');
    }

    /**
     * Form edit sekolah tertentu
     */
    public function edit(Sekolah $sekolah)
    {
        $setting = $sekolah;
        $anggarans = Anggaran::where('sekolah_id', $sekolah->id)->get();

        return view('admin.sekolah.edit', compact('setting', 'anggarans', 'sekolah'));
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::findOrFail($id);

        // 1. Validasi
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:20',

            // PASTIKAN INI ADA (Karena di View Anda pakai select option name="anggaran_id_aktif")
            'anggaran_id_aktif' => 'required|exists:anggarans,id',

            'email' => 'nullable|email|max:255',
            'telp' => 'nullable|string|max:20',
            'nama_kepala_sekolah' => 'required|string|max:255',
            'nip_kepala_sekolah' => 'required|string|max:30',
            'nama_bendahara' => 'required|string|max:255',
            'nip_bendahara' => 'required|string|max:30',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Simpan Data (Kecuali yang tidak perlu)
        $data = $request->except(['logo', '_token', '_method']);
        $sekolah->update($data);

        // 3. Handle Logo
        if ($request->hasFile('logo')) {
            if ($sekolah->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $sekolah->update(['logo' => $path]);
        }

        // 4. Update Status Anggaran (Penting!)
        \App\Models\Anggaran::where('sekolah_id', $sekolah->id)->update(['is_aktif' => false]);
        \App\Models\Anggaran::where('id', $request->anggaran_id_aktif)->update(['is_aktif' => true]);

        // 5. Redirect dengan Success Message
        return redirect()->route('admin.sekolah.index')
            ->with('success', "Data {$sekolah->nama_sekolah} berhasil diperbarui!");
    }
}
