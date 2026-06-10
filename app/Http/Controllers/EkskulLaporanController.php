<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\LaporanEkskul;
use App\Models\LaporanEkskulFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EkskulLaporanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Mengambil data dengan eager loading relasi bertingkat (laporans.fotos)
        if ($user->can('akses-admin-pusat')) {
            $ekskuls = Ekskul::with(['laporans.fotos', 'user', 'sekolah'])->latest()->paginate(10);
        } else {
            $ekskuls = Ekskul::with('laporans.fotos')
                ->where('sekolah_id', $user->sekolah_id)
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return view('ekskul.laporan.index', compact('ekskuls'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ekskul' => 'required|string|max:255',
            'periode' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal_kegiatan' => 'required|date',
            'pertemuan.*.materi' => 'required|string|max:255',
            'pertemuan.*.fotos' => 'required|array|min:1', // Setiap pertemuan wajib ada minimal 1 foto
            'pertemuan.*.fotos.*' => 'image|mimes:jpeg,png,jpg|max:5120', // Validasi file gambar
        ]);

        $user = Auth::user();

        // 1. Simpan Data Induk
        $ekskul = Ekskul::create([
            'sekolah_id' => $user->sekolah_id,
            'user_id' => $user->id,
            'nama_ekskul' => $request->nama_ekskul,
            'periode' => $request->periode,
            'keterangan' => $request->keterangan,
        ]);

        // 2. Loop Baris Pertemuan
        foreach ($request->pertemuan as $index => $item) {
            $laporan = LaporanEkskul::create([
                'ekskul_id' => $ekskul->id,
                'tanggal_kegiatan' => $item['tanggal_kegiatan'],
                'materi' => $item['materi'],
                'catatan' => $item['catatan'] ?? null,
            ]);

            // 3. Loop Upload Banyak Gambar untuk Pertemuan ini
            if ($request->hasFile("pertemuan.$index.fotos")) {
                foreach ($request->file("pertemuan.$index.fotos") as $file) {
                    $filename = 'img_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = $file->storeAs('laporan_ekskul/foto', $filename, 'public');

                    // Simpan ke tabel foto
                    LaporanEkskulFoto::create([
                        'laporan_ekskul_id' => $laporan->id,
                        'path_foto' => $path,
                    ]);
                }
            }
        }

        return back()->with('success', 'Laporan ekskul dan seluruh foto dokumentasi berhasil diunggah.');
    }

    public function destroy($id)
    {
        $ekskul = Ekskul::with('laporans.fotos')->findOrFail($id);

        // Hapus file fisik semua foto di storage sebelum delete record
        foreach ($ekskul->laporans as $laporan) {
            foreach ($laporan->fotos as $foto) {
                if (Storage::disk('public')->exists($foto->path_foto)) {
                    Storage::disk('public')->delete($foto->path_foto);
                }
            }
        }

        $ekskul->delete();

        return back()->with('success', 'Seluruh data laporan dan berkas foto berhasil dihapus.');
    }
}
