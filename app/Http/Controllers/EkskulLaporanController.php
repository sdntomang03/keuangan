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

        if ($user->can('akses-admin-pusat')) {
            $ekskuls = Ekskul::with(['laporans.fotos', 'user', 'sekolah'])->latest()->paginate(10);

            // Admin bisa melihat semua pilihan ekskul di dropdown
            $dropdownEkskuls = Ekskul::with(['user'])->latest()->get();
        } else {
            $ekskuls = Ekskul::with('laporans.fotos')
                ->where('sekolah_id', $user->sekolah_id)
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            // MENGAMBIL DATA EKSKUL SESUAI USER_ID PELATIH SAJA
            $dropdownEkskuls = Ekskul::where('user_id', $user->id)->latest()->get();
        }

        return view('ekskul.laporan.index', compact('ekskuls', 'dropdownEkskuls'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Menggunakan ekskul_id dari dropdown)
        $request->validate([
            'ekskul_id' => 'required|exists:ekskuls,id', // Wajib memilih ekskul yang sudah ada
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal_kegiatan' => 'required|date',
            'pertemuan.*.materi' => 'required|string|max:255',
            'pertemuan.*.fotos' => 'required|array|min:1',
            'pertemuan.*.fotos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // 2. Cari Data Induk Ekskul yang dipilih
        $ekskul = Ekskul::findOrFail($request->ekskul_id);

        // 3. Langsung Loop Baris Pertemuan dan masukkan ke Ekskul tersebut
        foreach ($request->pertemuan as $item) {
            $laporan = LaporanEkskul::create([
                'ekskul_id' => $ekskul->id,
                'tanggal_kegiatan' => $item['tanggal_kegiatan'],
                'materi' => $item['materi'],
                'catatan' => $item['catatan'] ?? null,
            ]);

            // 4. Proses Simpan Gambar Multiple
            if (isset($item['fotos']) && is_array($item['fotos'])) {
                foreach ($item['fotos'] as $file) {
                    $filename = 'img_'.time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = $file->storeAs('laporan_ekskul/foto', $filename, 'public');

                    LaporanEkskulFoto::create([
                        'laporan_ekskul_id' => $laporan->id,
                        'path_foto' => $path,
                    ]);
                }
            }
        }

        return back()->with('success', 'Laporan foto pertemuan berhasil ditambahkan ke dalam '.$ekskul->nama_ekskul);
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
