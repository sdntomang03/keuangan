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

        // 1. Ambil list master ekskul dari tabel ref_ekskul untuk dropdown di View
        $refEkskuls = DB::table('ref_ekskul')->get();

        if ($user->can('akses-admin-pusat')) {
            $ekskuls = Ekskul::with(['laporans.fotos', 'user', 'sekolah'])->latest()->paginate(10);
        } else {
            $ekskuls = Ekskul::with('laporans.fotos')
                ->where('sekolah_id', $user->sekolah_id)
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        // Kirim $refEkskuls ke dalam view
        return view('ekskul.laporan.index', compact('ekskuls', 'refEkskuls'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ekskul' => 'required|string|max:255', // Menyimpan string nama dari dropdown
            'keterangan' => 'nullable|string',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal_kegiatan' => 'required|date',
            'pertemuan.*.materi' => 'required|string|max:255',
            'pertemuan.*.fotos' => 'required|array|min:1',
            'pertemuan.*.fotos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // =========================================================================
        // LOGIKA OTOMATISASI PERIODE TRIWULAN (TW)
        // =========================================================================
        // Mengambil tanggal kegiatan dari baris pertemuan pertama sebagai acuan periode
        $tanggalAcuan = $request->pertemuan[0]['tanggal_kegiatan'];
        $carbonDate = Carbon::parse($tanggalAcuan);

        $bulan = $carbonDate->month; // Ambil angka bulan (1-12)
        $tahun = $carbonDate->year;  // Ambil angka tahun

        // Rumus matematika untuk menentukan TW (Bulan dibagi 3 lalu dibulatkan ke atas)
        $angkaTw = ceil($bulan / 3);

        // Menghasilkan string format: "TW 1 - 2026"
        $periodeOtomatis = 'TW '.$angkaTw.' - '.$tahun;
        // =========================================================================

        $user = Auth::user();

        // 1. Simpan Data Induk menggunakan nama ekskul dari dropdown & periode hasil hitungan otomatis
        $ekskul = Ekskul::create([
            'sekolah_id' => $user->sekolah_id,
            'user_id' => $user->id,
            'nama_ekskul' => $request->nama_ekskul,
            'periode' => $periodeOtomatis,
            'keterangan' => $request->keterangan,
        ]);

        // 2. Loop Baris Pertemuan
        foreach ($request->pertemuan as $item) {
            $laporan = LaporanEkskul::create([
                'ekskul_id' => $ekskul->id,
                'tanggal_kegiatan' => $item['tanggal_kegiatan'],
                'materi' => $item['materi'],
                'catatan' => $item['catatan'] ?? null,
            ]);

            // 3. Loop Upload Banyak Gambar untuk Pertemuan ini
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

        return back()->with('success', 'Laporan ekskul berhasil disimpan. Sistem mendeteksi aktivitas ini masuk ke dalam '.$periodeOtomatis);
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
