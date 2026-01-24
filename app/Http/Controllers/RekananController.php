<?php

namespace App\Http\Controllers;

use App\Models\Rekanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekananController extends Controller
{
    /**
     * Tampilkan daftar rekanan milik sekolah yang sedang login.
     */
    public function index()
    {
        // Ambil ID Sekolah dari user yang login
        // Pastikan tabel users Anda punya kolom sekolah_id
        $sekolahId = Auth::user()->sekolah_id;

        $rekanans = Rekanan::where('sekolah_id', $sekolahId)
            ->latest()
            ->paginate(10);

        return view('rekanan.index', compact('rekanans'));
    }

    /**
     * Form tambah data.
     */
    public function create()
    {
        return view('rekanan.create');
    }

    /**
     * Simpan data ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $validated = $request->validate([
            'nama_rekanan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'alamat2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',

            // Personalia
            'nama_pimpinan' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:100',

            // Keuangan
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string|max:50',
            'ket' => 'nullable|string',
        ]);

        // 2. Tambahkan sekolah_id secara otomatis
        // Asumsi: User yang login terhubung ke sekolah
        $validated['sekolah_id'] = Auth::user()->sekolah_id;

        // Jika user belum punya sekolah_id, bisa hardcode dulu untuk testing:
        // $validated['sekolah_id'] = 1;

        // 3. Simpan
        Rekanan::create($validated);

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil ditambahkan.');
    }

    /**
     * Form edit data.
     */
    public function edit(Rekanan $rekanan)
    {
        // Keamanan: Pastikan user hanya bisa edit rekanan sekolahnya sendiri
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return view('rekanan.edit', compact('rekanan'));
    }

    /**
     * Update data di database.
     */
    public function update(Request $request, Rekanan $rekanan)
    {
        // Keamanan
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403);
        }

        // 1. Validasi
        $validated = $request->validate([
            'nama_rekanan' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'alamat2' => 'nullable|string',
            'kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
            'nama_pimpinan' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'pkp' => 'nullable|string|max:50',
            'ket' => 'nullable|string',
        ]);

        // 2. Update (sekolah_id tidak perlu di-update agar tidak pindah sekolah)
        $rekanan->update($validated);

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil diperbarui.');
    }

    /**
     * Hapus data.
     */
    public function destroy(Rekanan $rekanan)
    {
        if ($rekanan->sekolah_id !== Auth::user()->sekolah_id) {
            abort(403);
        }

        $rekanan->delete();

        return redirect()->route('setting.rekanan.index')
            ->with('success', 'Data Rekanan berhasil dihapus.');
    }
}
