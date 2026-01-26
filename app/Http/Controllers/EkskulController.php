<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\RefEkskul;
use App\Models\Rekanan;
use App\Models\Sekolah;
use App\Models\SpjEkskul;
use App\Models\SpjEkskulDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EkskulController extends Controller
{
    /**
     * LOGIKA UTAMA:
     * Cek apakah SPJ sudah ada?
     * - Jika SUDAH: Tampilkan detail pertemuan (Jurnal).
     * - Jika BELUM: Lempar (Redirect) ke form Create.
     */
    public function index($belanjaId)
    {
        // 1. Cek Data Belanja
        $belanja = Belanja::findOrFail($belanjaId);

        // 2. Cek apakah SPJ Header sudah dibuat
        $spj = SpjEkskul::with(['details', 'pelatih', 'ekskul'])
            ->where('belanja_id', $belanjaId)
            ->first();

        // 3. JIKA BELUM ADA, Redirect ke halaman Create
        if (! $spj) {
            return redirect()->route('ekskul.create', $belanjaId);
        }

        // 4. JIKA SUDAH ADA, Tampilkan halaman Index/Jurnal
        return view('ekskul.index', compact('spj', 'belanja'));
    }

    /**
     * HALAMAN FORM INPUT (CREATE)
     * Method ini yang sebelumnya error "undefined"
     */
    public function create(Request $request, $belanjaId)
    {
        // 1. Ambil Data Belanja beserta relasi Rekanan-nya
        // Pastikan model Belanja punya fungsi relasi 'rekanan()'
        $belanja = Belanja::with('rekanan', 'rincis')->findOrFail($belanjaId);
        $sekolahId = $request->anggaran_data->sekolah_id;
        $sekolah = Sekolah::findOrFail($sekolahId);
        $twaktif = $sekolah->triwulan_aktif;

        // 2. Ambil Master Ekskul (tetap butuh list ini)
        $daftarEkskul = RefEkskul::all();

        // Kita tidak butuh variabel $pelatih (list banyak orang) lagi
        // karena pelatihnya sudah spesifik ada di $belanja->rekanan

        return view('ekskul.create', compact('belanja', 'daftarEkskul', 'twaktif'));
    }

    /**
     * PROSES SIMPAN DATA
     */
    public function store(Request $request)
    {
        // 1. Validasi tetap sama
        $request->validate([
            'belanja_id' => 'required|exists:belanjas,id',
            'rekanan_id' => 'required|exists:rekanans,id',
            'ref_ekskul_id' => 'required|exists:ref_ekskul,id',
            'tw' => 'required|integer|min:1|max:4',
            'honor' => 'required',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal' => 'required|date',
            'pertemuan.*.materi' => 'required|string',
            'pertemuan.*.foto' => 'required|image|max:10240', // Max 10MB
        ]);

        DB::beginTransaction();

        try {
            $pelatih = Rekanan::findOrFail($request->rekanan_id);
            $tarifPajak = ! empty($pelatih->npwp) ? 5 : 6;

            $honorPerPertemuan = (float) str_replace('.', '', $request->honor);
            $jumlahPertemuan = count($request->pertemuan);

            $totalBruto = $jumlahPertemuan * $honorPerPertemuan;
            $pphNominal = $totalBruto * ($tarifPajak / 100);
            $totalNetto = $totalBruto - $pphNominal;

            // Simpan Header
            $spj = SpjEkskul::create([
                'belanja_id' => $request->belanja_id,
                'rekanan_id' => $pelatih->id,
                'ref_ekskul_id' => $request->ref_ekskul_id,
                'tw' => $request->tw,
                'jumlah_pertemuan' => $jumlahPertemuan,
                'honor' => $honorPerPertemuan,
                'total_honor' => $totalBruto,
                'pph_persen' => $tarifPajak,
                'pph_nominal' => $pphNominal,
                'total_netto' => $totalNetto,
            ]);

            // Simpan Detail + Proses Watermark
            foreach ($request->pertemuan as $item) {
                // Panggil fungsi privat untuk olah foto
                $pathFoto = $this->processWatermark($item['foto'], $request->belanja_id, $item['tanggal']);

                SpjEkskulDetail::create([
                    'spj_ekskul_id' => $spj->id,
                    'tanggal_kegiatan' => $item['tanggal'],
                    'materi' => $item['materi'],
                    'foto_kegiatan' => $pathFoto,
                ]);
            }

            DB::commit();

            return redirect()->route('ekskul.index', $request->belanja_id)->with('success', 'SPJ Berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Kesalahan: '.$e->getMessage())->withInput();
        }
    }

    /**
     * CETAK KWITANSI
     */
    public function cetak($id)
    {
        $spj = SpjEkskul::with(['details', 'pelatih', 'ekskul', 'belanja'])->findOrFail($id);

        return view('ekskul.cetak_kwitansi', compact('spj'));
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        $spj = SpjEkskul::with('details')->findOrFail($id);

        // Hapus Foto Fisik
        foreach ($spj->details as $detail) {
            if ($detail->foto_kegiatan) {
                Storage::disk('public')->delete($detail->foto_kegiatan);
            }
        }

        $belanjaId = $spj->belanja_id; // Simpan ID belanja untuk redirect
        $spj->delete();

        // Redirect kembali ke halaman Create (karena data sudah habis)
        return redirect()->route('ekskul.create', $belanjaId)
            ->with('success', 'Data SPJ berhasil dihapus.');
    }

    /**
     * HALAMAN EDIT
     */
    public function edit($id)
    {
        $spj = SpjEkskul::with(['details', 'belanja.rekanan', 'belanja.rincis'])->findOrFail($id);
        $belanja = $spj->belanja;
        $daftarEkskul = RefEkskul::all();

        // Ambil TW Aktif dari Sekolah (Sama seperti create)
        $sekolah = \App\Models\Sekolah::first();
        $twaktif = $sekolah->triwulan_aktif ?? ceil(date('n') / 3);

        return view('ekskul.edit', compact('spj', 'belanja', 'daftarEkskul', 'twaktif'));
    }

    /**
     * PROSES UPDATE
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi (Mirip Store, tapi foto pertemuan boleh null/kosong jika tidak diganti)
        $request->validate([
            'ref_ekskul_id' => 'required',
            'pertemuan' => 'required|array|min:1',
            'pertemuan.*.tanggal' => 'required|date',
            'pertemuan.*.materi' => 'required|string',
            'pertemuan.*.foto' => 'nullable|image|max:2048', // Boleh null kalau pakai foto lama
            'pertemuan.*.old_foto' => 'nullable|string', // Path foto lama
        ]);

        DB::beginTransaction();

        try {
            $spj = SpjEkskul::findOrFail($id);

            // A. Hitung Ulang Keuangan (Siapa tahu jumlah pertemuan berubah)
            // Ambil tarif pajak & honor lama (karena read only)
            $tarifPajak = $spj->pph_persen;
            $honorPerPertemuan = $spj->honor;

            $jumlahPertemuan = count($request->pertemuan);
            $totalBruto = $jumlahPertemuan * $honorPerPertemuan;
            $pphNominal = $totalBruto * ($tarifPajak / 100);
            $totalNetto = $totalBruto - $pphNominal;

            // B. Update Header SPJ
            $spj->update([
                'ref_ekskul_id' => $request->ref_ekskul_id,
                'jumlah_pertemuan' => $jumlahPertemuan,
                'total_honor' => $totalBruto,
                'pph_nominal' => $pphNominal,
                'total_netto' => $totalNetto,
            ]);

            // C. Update Detail (Strategi: Hapus Semua Detail Lama -> Insert Ulang)
            // 1. Tapi jangan hapus file fisiknya dulu, karena mungkin dipakai lagi
            SpjEkskulDetail::where('spj_ekskul_id', $id)->delete();

            // 2. Insert Ulang
            foreach ($request->pertemuan as $item) {
                // Logika Foto: Pakai foto baru KALO ADA, kalau tidak pakai foto lama
                $fotoPath = $item['old_foto'] ?? null;

                if (isset($item['foto']) && $item['foto']) {
                    // Jika user upload foto baru, simpan & update path
                    $fotoPath = $item['foto']->store('spj/foto_kegiatan', 'public');

                    // (Opsional) Hapus file lama jika ingin hemat storage
                    // if ($item['old_foto']) Storage::disk('public')->delete($item['old_foto']);
                }

                SpjEkskulDetail::create([
                    'spj_ekskul_id' => $spj->id,
                    'tanggal_kegiatan' => $item['tanggal'],
                    'materi' => $item['materi'],
                    'foto_kegiatan' => $fotoPath,
                ]);
            }

            DB::commit();

            return redirect()->route('ekskul.index', $spj->belanja_id)
                ->with('success', 'Data SPJ berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal update: '.$e->getMessage());
        }
    }

    private function processWatermark($file, $belanjaId, $tanggalPertemuan)
    {
        // 1. Ambil Data Sekolah
        $belanja = Belanja::with('anggaran.sekolah')->findOrFail($belanjaId);
        $objSekolah = $belanja->anggaran->sekolah;

        $namaSekolah = strtoupper($objSekolah->nama_sekolah ?? 'SEKOLAH');
        $alamat1 = strtoupper($objSekolah->alamat ?? 'ALAMAT');
        $alamat2 = strtoupper('Kel. '.($objSekolah->kelurahan ?? '-').', Kec. '.($objSekolah->kecamatan ?? '-'));
        $lat = $objSekolah->latitude ?? '-6.175113';
        $lng = $objSekolah->longitude ?? '106.865039';

        // 2. Olah Waktu
        $tglFormatted = \Carbon\Carbon::parse($tanggalPertemuan)->translatedFormat('l, d F Y');
        $waktu = sprintf('%02d:%02d:%02d', rand(13, 16), rand(0, 59), rand(0, 59));

        // 3. Inisialisasi Manager
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver);
        $img = $manager->read($file);

        // Standardisasi ukuran
        $img->scale(width: 1200);
        $width = $img->width();
        $height = $img->height();

        // 4. Background Layer (Hitam Transparan)
        $backgroundLayer = $manager->create($width, 250)->fill('rgba(0, 0, 0, 0.5)');
        $img->place($backgroundLayer, 'bottom-center');

        // 5. Peta Statis
        try {
            $mapUrl = "https://static-maps.yandex.ru/1.x/?lang=en_US&ll=$lng,$lat&z=16&l=map&size=200,200&pt=$lng,$lat,pm2rdm";
            $mapContent = @file_get_contents($mapUrl);
            if ($mapContent) {
                $mapImage = $manager->read($mapContent);
                $img->place($mapImage, 'bottom-right', 20, 25);
            }
        } catch (\Exception $e) {
        }

        // 6. Teks Watermark
        $fontPath = public_path('fonts/Roboto-Regular.ttf');
        $watermarkText = "$tglFormatted $waktu\n$alamat1 ($lat, $lng)\n$alamat2\n$namaSekolah";

        // Pastikan font tersedia, jika tidak watermark tidak akan dirender
        if (file_exists($fontPath)) {
            $img->text($watermarkText, 40, $height - 210, function ($font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(30);
                $font->color('ffffff');
                $font->lineHeight(2);
                $font->valign('top');
            });
        }

        // 7. Simpan Foto
        $filename = 'SPJ_EKS_'.time().'_'.uniqid().'.jpg';
        $path = 'spj/foto_kegiatan/'.$filename;

        // Pastikan direktori ada
        if (! Storage::disk('public')->exists('spj/foto_kegiatan')) {
            Storage::disk('public')->makeDirectory('spj/foto_kegiatan');
        }

        Storage::disk('public')->put($path, $img->toJpeg(80));

        return $path;
    }
}
