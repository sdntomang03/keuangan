<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\LaporanEkskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EkskulLaporanController extends Controller
{
    /**
     * Menampilkan daftar kegiatan ekskul milik pelatih / sekolah terkait
     */
    public function index()
    {
        $user = Auth::user();

        // Jika dia admin pusat, dia bisa melihat semua ekskul sekolah
        if ($user->can('akses-admin-pusat')) {
            $ekskuls = Ekskul::with(['laporans', 'user', 'sekolah'])->latest()->paginate(10);
        } else {
            // Jika pelatih/bendahara sekolah, filter berdasarkan sekolah_id dan user_id mereka
            $ekskuls = Ekskul::with('laporans')
                ->where('sekolah_id', $user->sekolah_id)
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return view('ekskul.laporan.index', compact('ekskuls'));
    }

    /**
     * Menyimpan data induk Ekskul baru beserta MULTIPLE laporan pertemuannya
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Berbasis Form Array Dinamis
        $request->validate([
            'nama_ekskul' => 'required|string|max:255',
            'periode' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal_kegiatan' => 'required|date',
            'pertemuan.*.materi' => 'required|string|max:255',
            'pertemuan.*.foto' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB per file
        ]);

        $user = Auth::user();

        // 2. Buat Data Induk Ekskul dengan mengambil user_id dan sekolah_id secara otomatis
        $ekskul = Ekskul::create([
            'sekolah_id' => $user->sekolah_id,
            'user_id' => $user->id,
            'nama_ekskul' => $request->nama_ekskul,
            'periode' => $request->periode,
            'keterangan' => $request->keterangan,
        ]);

        // 3. Looping upload multiple file gambar pertemuan
        if ($request->has('pertemuan')) {
            foreach ($request->pertemuan as $item) {
                $file = $item['foto'];

                // Penamaan unik file gambar laporan
                $filename = 'lpr_ekskul_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('laporan_ekskul/foto', $filename, 'public');

                // Simpan baris detail ke database laporan_ekskuls
                LaporanEkskul::create([
                    'ekskul_id' => $ekskul->id,
                    'tanggal_kegiatan' => $item['tanggal_kegiatan'],
                    'materi' => $item['materi'],
                    'path_gambar' => $path,
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Kegiatan ekskul dan seluruh foto laporan berhasil disimpan.');
    }

    /**
     * Menghapus seluruh kegiatan ekskul berserta seluruh berkas fotonya
     */
    public function destroy($id)
    {
        $ekskul = Ekskul::with('laporans')->findOrFail($id);

        // Hapus fisik seluruh file foto laporan yang terikat di storage sebelum menghapus record
        foreach ($ekskul->laporans as $laporan) {
            if (Storage::disk('public')->exists($laporan->path_gambar)) {
                Storage::disk('public')->delete($laporan->path_gambar);
            }
        }

        $ekskul->delete(); // Karena on-delete cascade pada migration, data detail di DB ikut otomatis terhapus

        return back()->with('success', 'Seluruh data kegiatan ekskul beserta berkas laporan berhasil dihapus.');
    }
}
