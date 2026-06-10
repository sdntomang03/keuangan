<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\LaporanEkskul;
use App\Models\LaporanEkskulFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Drivers\Imagick\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

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
        $request->validate([
            'ekskul_id' => 'required|exists:ekskuls,id',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal_kegiatan' => 'required|date',
            'pertemuan.*.materi' => 'required|string|max:255',
            'pertemuan.*.fotos' => 'required|array|min:1',
            'pertemuan.*.fotos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $ekskul = Ekskul::findOrFail($request->ekskul_id);

        // INISIALISASI MANAGER (Di luar loop agar memori server lebih efisien)
        $manager = ImageManager::usingDriver(Driver::class);

        foreach ($request->pertemuan as $item) {
            $laporan = LaporanEkskul::create([
                'ekskul_id' => $ekskul->id,
                'tanggal_kegiatan' => $item['tanggal_kegiatan'],
                'materi' => $item['materi'],
                'catatan' => $item['catatan'] ?? null,
            ]);

            // PROSES FOTO MULTIPLE KE WEBP
            if (isset($item['fotos']) && is_array($item['fotos'])) {
                foreach ($item['fotos'] as $file) {

                    // Generate nama file dengan folder (Mirip dengan pola Anda)
                    $filename = 'laporan_ekskul/foto/img_'.time().'_'.Str::random(10).'.webp';

                    // Proses Encode ke Webp Kualitas 85
                    $encoded = $manager
                        ->decode($file->getPathname())
                        ->encode(new WebpEncoder(quality: 85));

                    // Simpan menggunakan Storage Facade
                    Storage::disk('public')->put($filename, (string) $encoded);

                    // Simpan path relatif ke Database
                    LaporanEkskulFoto::create([
                        'laporan_ekskul_id' => $laporan->id,
                        'path_foto' => $filename,
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
