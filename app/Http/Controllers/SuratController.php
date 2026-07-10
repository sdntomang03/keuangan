<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Belanja;
use App\Models\BelanjaFoto;
use App\Models\Korek;
use App\Models\Rekanan;
use App\Models\Sekolah;
use App\Models\Surat;
use App\Models\Talangan;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use ZipArchive;

// use PhpOffice\PhpWord\Writer\PDF;

class SuratController extends Controller
{
    /**
     * 3. Halaman Index Manajemen Surat
     */
    public function index($belanjaId)
    {
        $belanja = Belanja::with([
            'surats' => function ($query) {
                $query->orderBy('tanggal_surat', 'asc');
            },
            'rincis',
            'fotos',
        ])->findOrFail($belanjaId);

        $triwulanSurat = $belanja->surats->first()->triwulan ?? 1;

        $jenisSuratList = [
            'PH' => 'Permintaan Harga',
            'NH' => 'Negosiasi Harga',
            'SP' => 'Surat Pesanan',
            'BAPB' => 'Berita Acara Penerimaan Barang',
        ];

        return view('surat.index', compact('belanja', 'jenisSuratList', 'triwulanSurat'));
    }

    /**
     * 4. Generate Otomatis (Weekdays & Auto Number)
     */
    public function generateDefault($belanjaId)
    {
        $belanja = Belanja::findOrFail($belanjaId);
        $baseDate = Carbon::parse($belanja->tanggal);

        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        $triwulanAktif = $user->sekolah->triwulan_aktif ?? 1;

        $timeline = [
            'BAPB' => 0, // Hari H
            'SP' => 1, // H-1
            'NH' => 3, // H-3
            'PH' => 5, // H-5
        ];

        DB::transaction(function () use ($baseDate, $timeline, $belanjaId, $sekolahId, $triwulanAktif) {
            // A. Create Data
            foreach ($timeline as $jenis => $mundur) {
                $exists = Surat::where('belanja_id', $belanjaId)
                    ->where('jenis_surat', $jenis)
                    ->exists();

                if (! $exists) {
                    $tanggalSurat = $baseDate->copy()->subWeekdays($mundur);

                    Surat::create([
                        'sekolah_id' => $sekolahId,
                        'belanja_id' => $belanjaId,
                        'triwulan' => $triwulanAktif,
                        'jenis_surat' => $jenis,
                        'nomor_surat' => 'DRAFT',
                        'tanggal_surat' => $tanggalSurat,
                    ]);
                }
            }

            // B. Re-sequence
            $tahun = $baseDate->format('Y');
            $this->urutkanUlangNomorSurat($sekolahId, $baseDate->format('Y'), $triwulanAktif);
        });

        return back()->with('success', 'Surat berhasil digenerate dan diurutkan sesuai tanggal.');
    }

    /**
     * 5. Helper Pengurutan Ulang
     */
    private function urutkanUlangNomorSurat($sekolahId, $tahun, $triwulanAktif)
    {
        // 1. Ambil data sekolah untuk mendapatkan "nomor_surat" awal pada triwulan aktif
        $sekolah = Sekolah::find($sekolahId);

        // Default jika field kosong
        $baseNumber = 1;

        if ($sekolah && $sekolah->nomor_surat) {
            // Ambil angka depan dari field nomor_surat di tabel sekolahs
            // Contoh isi field: "045" atau "045/UD.02.02"
            $parts = explode('/', $sekolah->nomor_surat);
            $baseNumber = (int) $parts[0];
        }

        // 2. Ambil surat hanya di triwulan aktif untuk diurutkan
        $surats = Surat::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal_surat', $tahun)
            ->where('triwulan', $triwulanAktif)
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 3. Tentukan Nomor Urut Mulai
        // Karena base_number adalah nomor urut surat awal untuk TW ini,
        // maka nomor urut langsung dimulai dari nilai tersebut (tanpa ditambah 1)
        $noUrut = $baseNumber;

        foreach ($surats as $surat) {
            $strNoUrut = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
            $nomorBaru = "{$strNoUrut}/".$sekolah->kode_surat;

            if ($surat->nomor_surat !== $nomorBaru) {
                $surat->update(['nomor_surat' => $nomorBaru]);
            }
            $noUrut++;
        }
    }

    /**
     * 6. Update Manual
     */
    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        // 1. Validasi Dinamis
        // Jika jenis surat adalah BAPB, nomor_bast wajib diisi
        $rules = [
            'tanggal_surat' => 'required|date',
        ];

        if ($surat->jenis_surat === 'BAPB') {
            $rules['nomor_bast'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // 2. Siapkan data untuk update
        $dataUpdate = [
            'tanggal_surat' => $request->tanggal_surat,
        ];

        // Tambahkan nomor_bast ke array update hanya jika jenisnya BAPB
        if ($surat->jenis_surat === 'BAPB') {
            $dataUpdate['no_bast'] = $request->nomor_bast;
        }

        // 3. Eksekusi Update
        $surat->update($dataUpdate);

        // 4. Urutkan ulang nomor surat agar tetap konsisten dengan tanggal baru
        // Pastikan kolom 'triwulan' atau 'tw' sesuai dengan nama kolom di database Anda
        $this->urutkanUlangNomorSurat(
            $surat->sekolah_id,
            Carbon::parse($request->tanggal_surat)->format('Y'),
            $surat->triwulan ?? $surat->tw
        );

        return back()->with('success', 'Data surat berhasil diperbarui.');
    }

    public function store(Request $request, $belanjaId)
    {
        // 1. VALIDASI
        // Kita ubah validasi dari 'rinci_ids' menjadi 'items'
        $request->validate([
            'jenis_surat' => 'required',
            'nomor_surat' => 'required',
            'tanggal_surat' => 'required|date',
            'items' => 'required|array', // Array pembungkus
        ]);

        $belanja = Belanja::findOrFail($belanjaId);
        $user = Auth::user();

        // Gunakan Transaction agar aman
        DB::transaction(function () use ($request, $belanjaId, $user) {

            // 2. BUAT SURAT UTAMA
            $surat = Surat::create([
                'sekolah_id' => $user->sekolah_id,
                'belanja_id' => $belanjaId,
                'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                'jenis_surat' => $request->jenis_surat,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
            ]);

            // 3. PROSES DATA PIVOT (Relasi & Volume)
            // Kita harus memfilter item mana yang dicentang oleh user
            $pivotData = [];

            foreach ($request->items as $rinciId => $data) {
                // Cek apakah checkbox 'selected' dikirim/dicentang?
                if (isset($data['selected'])) {
                    // Format array untuk attach dengan data tambahan (pivot columns)
                    // [ ID_RINCI => ['volume' => NILAI_VOLUME], ... ]
                    $pivotData[$rinciId] = [
                        'volume' => $data['volume'],
                    ];
                }
            }

            // 4. SIMPAN KE TABEL PIVOT (surat_rinci)
            if (! empty($pivotData)) {
                $surat->rincis()->attach($pivotData);
            }
        });

        return back()->with('success', 'Surat parsial berhasil dibuat dengan volume yang disesuaikan.');
    }

    /**
     * Store Paket Parsial (SP + BAPB sekaligus)
     */
    public function storeParsial(Request $request, $belanjaId)
    {
        // 1. VALIDASI DINAMIS (Sesuai Pilihan Combo Box)
        // Kita buat aturan dasar dulu
        $rules = [
            'jenis_surat' => 'required|in:SP,BAPB',
            'keterangan' => 'required|string', // Tahap 1, dll
            'items' => 'required|array',
        ];

        // Tambahkan aturan khusus jika pilih SP
        if ($request->jenis_surat == 'SP') {
            $rules['nomor_sp'] = 'required';
            $rules['tanggal_sp'] = 'required|date';
        }
        // Tambahkan aturan khusus jika pilih BAPB
        elseif ($request->jenis_surat == 'BAPB') {
            $rules['no_bast'] = 'required|array|min:1';
            $rules['no_bast.*'] = 'required|string';
            $rules['tanggal_bast'] = 'required|array|min:1';
            $rules['tanggal_bast.*'] = 'required|date';
        }

        // Jalankan Validasi
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $belanja = Belanja::findOrFail($belanjaId);
        $user = Auth::user();

        DB::transaction(function () use ($request, $belanjaId, $user) {

            // 2. SIAPKAN DATA PIVOT (Rincian Barang)
            $pivotData = [];
            foreach ($request->items as $rinciId => $data) {
                // Hanya ambil item yang dicentang
                if (isset($data['selected']) && $data['selected'] == 1) {
                    // Pastikan volume ada isinya, kalau kosong anggap 0 atau skip
                    if ($data['volume'] > 0) {
                        $pivotData[$rinciId] = ['volume' => $data['volume']];
                    }
                }
            }

            if (empty($pivotData)) {
                throw new \Exception('Harus memilih minimal satu barang dengan volume > 0.');
            }

            // 3. LOGIKA PENYIMPANAN BERDASARKAN JENIS

            // --- SKENARIO A: BUAT SURAT PESANAN (SP) ---
            if ($request->jenis_surat == 'SP') {
                $sp = Surat::create([
                    'sekolah_id' => $user->sekolah_id,
                    'belanja_id' => $belanjaId,
                    'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                    'jenis_surat' => 'SP',
                    'nomor_surat' => $request->nomor_sp,
                    'tanggal_surat' => $request->tanggal_sp,
                    'is_parsial' => true,
                    'keterangan' => $request->keterangan,
                    'sp_referensi_id' => null, // SP adalah induk, tidak punya referensi
                ]);

                // Simpan Rincian Barang
                $sp->rincis()->attach($pivotData);
            }

            // --- SKENARIO B: BUAT BAPB (& DATA BAST) ---
            elseif ($request->jenis_surat == 'BAPB') {

                // Looping sebanyak tanggal BAST yang diinputkan user di form dinamis
                foreach ($request->no_bast as $index => $nomorBast) {
                    $tanggalBast = $request->tanggal_bast[$index];

                    // Tambahkan urutan di keterangan agar mudah dibaca jika input banyak
                    $tambahanKeterangan = count($request->no_bast) > 1 ? ' (Tahap '.($index + 1).')' : '';

                    $bapb = Surat::create([
                        'sekolah_id' => $user->sekolah_id,
                        'belanja_id' => $belanjaId,
                        'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                        'jenis_surat' => 'BAPB',

                        'nomor_surat' => $nomorBast,
                        'tanggal_surat' => $tanggalBast,

                        'no_bast' => $nomorBast,
                        'tanggal_bast' => $tanggalBast,

                        'is_parsial' => true,
                        'keterangan' => $request->keterangan.$tambahanKeterangan,

                        'sp_referensi_id' => $request->sp_referensi_id ?: null,
                    ]);

                    // Simpan Rincian Barang (Pivot) yang sama (Qty & Item Identik) ke masing-masing Surat
                    $bapb->rincis()->attach($pivotData);
                }
            }

        });

        return back()->with('success', 'Data parsial ('.$request->jenis_surat.') berhasil disimpan.');
    }

    public function destroy($id)
    {
        $surat = Surat::findOrFail($id);

        // Hapus data pivot (barang rincian) dulu jika ada
        $surat->rincis()->detach();

        // Hapus suratnya
        $surat->delete();

        // Opsional: Urutkan ulang nomor surat jika diperlukan
        // $this->urutkanUlangNomorSurat($surat->sekolah_id, $surat->tanggal_surat->format('Y'));

        return back()->with('success', 'Dokumen surat berhasil dihapus.');
    }

    /**
     * Helper untuk mengubah angka menjadi kalimat (Terbilang)
     */
    private function terbilang($x)
    {
        $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($x < 12) {
            return ' '.$angka[$x];
        } elseif ($x < 20) {
            return $this->terbilang($x - 10).' belas';
        } elseif ($x < 100) {
            return $this->terbilang($x / 10).' puluh'.$this->terbilang($x % 10);
        } elseif ($x < 200) {
            return ' seratus'.$this->terbilang($x - 100);
        } elseif ($x < 1000) {
            return $this->terbilang($x / 100).' ratus'.$this->terbilang($x % 100);
        } elseif ($x < 2000) {
            return ' seribu'.$this->terbilang($x - 1000);
        } elseif ($x < 1000000) {
            return $this->terbilang($x / 1000).' ribu'.$this->terbilang($x % 1000);
        }
    }

    public function uploadFoto(Request $request, $id)
    {
        $request->validate(['foto' => 'required|image|max:10240']);

        // 1. Ambil Data Relasi Berantai
        $belanja = Belanja::with('anggaran.sekolah', 'surats')->findOrFail($id);
        $objSekolah = $belanja->anggaran->sekolah;

        // 2. Olah Data Teks
        $namaSekolah = strtoupper($objSekolah->nama_sekolah ?? 'NAMA SEKOLAH');
        $alamatSekolah = strtoupper($objSekolah->alamat_sekolah ?? $objSekolah->alamat ?? 'ALAMAT SEKOLAH');
        $alamatSekolah2 = strtoupper('Kel. '.($objSekolah->kelurahan ?? '-').', Kec. '.($objSekolah->kecamatan ?? '-'));
        $lat = $objSekolah->latitude ?? '-6.176717';
        $lng = $objSekolah->longitude ?? '106.796351';

        // 3. Generate Waktu Acak
        $detikAcak = sprintf('%02d', rand(0, 59));

        // 2. Cek apakah ada input waktu dari form
        if ($request->has('waktu_foto') && $request->waktu_foto != null) {
            // Ambil input form (format H:i, misal 10:30) dan gabungkan dengan detik acak
            $waktuAcak = $request->waktu_foto.':'.$detikAcak;
        } else {
            // Cadangan: Jika form kosong, random jam 9-15 seperti sebelumnya
            $waktuAcak = sprintf('%02d:%02d:%02d', rand(9, 15), rand(0, 59), $detikAcak);
        }

        // 4. Ambil Tanggal BAST
        // 1. Cek apakah ada input tanggal manual dari form
        if ($request->has('tanggal_bast_foto') && $request->tanggal_bast_foto != null) {
            // Jika ada, gunakan tanggal dari input form
            $tanggalBast = \Carbon\Carbon::parse($request->tanggal_bast_foto)->translatedFormat('l, d F Y');
        } else {
            // 2. Jika form kosong, gunakan logika lama (Cari dari database atau hari ini)
            $suratBast = $belanja->surats->where('jenis_surat', 'BAPB')->first();

            $tanggalBast = $suratBast && $suratBast->tanggal_surat
                ? $suratBast->tanggal_surat->translatedFormat('l, d F Y')
                : now()->translatedFormat('l, d F Y');
        }

        // 5. Inisialisasi Image Manager
        $file = $request->file('foto');
        $manager = new ImageManager(new Driver);
        $img = $manager->read($file);

        // 6. Standardisasi Canvas (1600px agar huruf & peta proporsional)
        $img->scale(width: 1200);
        $width = $img->width();
        $height = $img->height();

        $bgHeight = 300;
        $backgroundLayer = $manager->create($width, $bgHeight)->fill('rgba(0, 0, 0, 0.5)');

        // Tempelkan di bagian paling bawah gambar
        $img->place($backgroundLayer, 'bottom-center');

        // 7. Tambahkan Peta Statis (OpenStreetMap via Yandex Static)
        try {
            // Kita ambil peta ukuran 350x350 agar terlihat jelas
            $mapUrl = "https://static-maps.yandex.ru/1.x/?lang=en_US&ll=$lng,$lat&z=16&l=map&size=250,250&pt=$lng,$lat,pm2rdm";
            $mapContent = file_get_contents($mapUrl);
            if ($mapContent) {
                $mapImage = $manager->read($mapContent);
                // Tempel di pojok kanan bawah dengan margin 20px
                $img->place($mapImage, 'bottom-right', 20, 20);
            }
        } catch (\Exception $e) {
            // Jika internet mati, proses lanjut tanpa peta
        }

        // 8. Setting Ukuran Watermark
        $fontSizeLarge = 36;
        $fontSizeSmall = 26;
        $padding = 60;
        $fontReg = public_path('fonts/Roboto-Regular.ttf');

        // 10. Render Teks Baris 1: Judul
        $img->text(strtoupper($belanja->uraian), $padding, $height - 210, function ($font) use ($fontSizeLarge, $fontReg) {
            if (file_exists($fontReg)) {
                $font->filename($fontReg);
            }
            $font->size($fontSizeLarge);
            $font->color('ffffff');
            $font->valign('top');
        });

        // 11. Render Teks Baris 2-5: Detail
        $watermarkDetail = "$tanggalBast $waktuAcak\n$alamatSekolah ($lat, $lng)\n$alamatSekolah2\n$namaSekolah";

        $img->text($watermarkDetail, $padding, $height - 140, function ($font) use ($fontSizeSmall, $fontReg) {
            if (file_exists($fontReg)) {
                $font->filename($fontReg);
            }
            $font->size($fontSizeSmall);
            $font->lineHeight(1.8); // Disesuaikan agar 4 baris detail rapi
            $font->color('ffffff');
            $font->valign('top');
        });

        // 12. Simpan File & Database
        $filename = time().'.jpg';
        $path = 'dokumentasi/'.$filename;
        Storage::disk('public')->put($path, $img->toJpeg(80));

        $belanja->fotos()->create([
            'path' => $path,
            'latitude' => $lat,
            'longitude' => $lng,
            'tanggal' => $request->input('tanggal_bast_foto'),
            'status' => $request->input('status', 'umum'),
        ]);

        return redirect(url()->previous().'#foto')->with('success', 'Foto berhasil diunggah');
    }

    public function destroyFoto($id)
    {
        $foto = BelanjaFoto::findOrFail($id);
        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        return redirect(url()->previous().'#foto')->with('success', 'Foto berhasil dihapus');
    }

    public function cetakFotoSpj($id)
    {
        // 1. Ambil Data
        $belanja = Belanja::with(['fotos', 'user.sekolah', 'korek', 'anggaran'])->findOrFail($id);

        if ($belanja->fotos->isEmpty()) {
            return back()->with('error', 'Belum ada foto dokumentasi yang diunggah.');
        }

        $sekolah = $belanja->user->sekolah;

        // 2. Data Tambahan
        $mapRomawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        $triwulan = $mapRomawi[$belanja->triwulan ?? 1] ?? 'I';
        $tahun = $sekolah->tahun_aktif ?? date('Y');

        // 3. Load View PDF (GUNAKAN ALIAS 'DomPdf' DISINI)
        // Perhatikan: Menggunakan DomPdf::loadView, bukan Pdf::loadView
        $pdf = PDF::loadView('surat.pdf_foto_spj', compact('belanja', 'sekolah', 'triwulan', 'tahun'));

        // Set ukuran kertas F4/Folio (Width: 609.448 pt, Height: 935.433 pt)
        $customPaper = [0, 0, 609.448, 935.433];
        $pdf->setPaper($customPaper, 'portrait');

        // Download / Stream
        return $pdf->stream('Dokumentasi_SPJ_'.$belanja->id.'.pdf');
    }

    public function regenerateAllNumbers()
    {
        $user = Auth::user();
        $sekolah = Sekolah::find($user->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $triwulanAktif = $sekolah->triwulan_aktif;
        $tahunAktif = $sekolah->tahun_aktif ?? date('Y');

        // Gunakan Transaction biar aman datanya
        DB::transaction(function () use ($sekolah, $tahunAktif, $triwulanAktif) {
            // HANYA panggil fungsi pengurutan untuk Triwulan Aktif dan Tahun Aktif
            $this->urutkanUlangNomorSurat(
                $sekolah->id,
                $tahunAktif,
                $triwulanAktif
            );
        });

        return back()->with('success', "Semua nomor surat pada Triwulan $triwulanAktif berhasil diurutkan ulang.");
    }

    /**
     * API: Toggle Status Pembina (Ket)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $rekanan = Rekanan::findOrFail($id);

            // Update status: Jika dikirim 1 simpan 1, jika 0 simpan 0
            $rekanan->ket = $request->status;
            $rekanan->save();

            return response()->json([
                'success' => true,
                'message' => 'Status Pembina berhasil diperbarui.',
                'new_status' => $rekanan->ket,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status.',
            ], 500);
        }
    }

    public function cetakSatuan($id, $jenis)
    {
        Carbon::setLocale('id');

        // 1. AMBIL DATA BELANJA (Sesuai snippet Anda)
        $belanja = Belanja::with([
            'rincis.rkas',
            'rekanan',
            'korek',
            'user.sekolah',
            'surats',
            'anggaran',
        ])->findOrFail($id);
        // 2. AMBIL DATA PENDUKUNG
        // Fallback: Jika user pembuat belanja tidak punya sekolah, ambil dari user login
        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;

        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
            if (! $sekolah) {
                return back()->with('error', 'Data Sekolah tidak ditemukan');
            }
        }

        $rekanan = $belanja->rekanan;

        // 3. MAPPING DATA PEJABAT (Agar rapi di View)
        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah,
            'nip' => $sekolah->nip_kepala_sekolah,
        ];

        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // 4. LOGIKA JENIS SURAT & PENGAMBILAN DARI DB
        $mapJenis = [
            'permintaan' => 'PH',
            'negosiasi' => 'NH',
            'pesanan' => 'SP',
            'berita_acara' => 'BAPB',
            'pemeriksaan' => 'BAPB', // Alias
        ];

        if (! isset($mapJenis[$jenis])) {
            abort(404, 'Jenis surat tidak valid');
        }

        // Ambil kode (PH/NH/dll)
        $kodeJenis = $mapJenis[$jenis];

        // Cari data surat di database berdasarkan jenisnya
        $suratDb = $belanja->surats->where('jenis_surat', $kodeJenis)->first();

        // 5. FORMAT DATA SURAT (Agar View tidak error jika surat belum digenerate)
        // Default values jika surat belum ada (Draft)
        $noSurat = 'DRAFT (Belum Generate)';
        $tglSurat = now(); // Default hari ini
        $sifat = 'Segera';
        $lampiran = '-';

        if ($suratDb) {
            $noSurat = $suratDb->nomor_surat;
            $tglSurat = $suratDb->tanggal_surat; // Carbon Object
            $sifat = $suratDb->sifat ?? 'Segera';
            $lampiran = $suratDb->lampiran ?? '-';
        }

        // Khusus BAPB: Cek tanggal realisasi/BAST
        if ($kodeJenis == 'BAPB') {
            // Prioritas: Tanggal Input BAST -> Tanggal Surat BAPB -> Hari Ini
            $tglSurat = $belanja->tanggal_bast
            ? Carbon::parse($belanja->tanggal_bast)
            : ($suratDb ? $suratDb->tanggal_surat : now());
        }

        // Buat Object Surat Final untuk dikirim ke View
        $surat = (object) [
            'nomor_surat' => $noSurat,
            'tanggal_surat' => $tglSurat->format('Y-m-d'), // String Y-m-d aman untuk Blade

            // Data Umum Belanja
            'anggaran' => $belanja->anggaran,
            'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
            'kode_rekening' => $belanja->korek->ket ?? '-',

            'nama_kegiatan' => $belanja->uraian,

            // Data Spesifik Surat
            'perihal' => ucwords(str_replace('_', ' ', $jenis)).' Harga '.$belanja->uraian,
            'sifat' => $sifat,
            'lampiran' => $lampiran,

            // Data Spesifik BAPB
            'nama_pekerjaan' => $belanja->uraian,
            'hari_ini' => $tglSurat->translatedFormat('l'),
            'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
        ];

        // 6. FORMAT ITEM BARANG (Mapping Harga & Satuan)
        $items = $belanja->rincis->map(function ($item) {
            return (object) [
                'nama_barang' => $item->namakomponen,
                // Logika Satuan: Ambil dari RKAS jika ada, kalau tidak ambil dari inputan
                'satuan' => $item->rkas->satuan ?? $item->satuan,

                'qty' => $item->volume,

                // Harga Penawaran vs Harga Deal
                'harga_satuan' => $item->harga_satuan, // Harga Awal
                'harga_penawaran' => $item->harga_penawaran,

                // Qty untuk BAPB
                'qty_pesan' => $item->volume,
                'qty_terima' => $item->volume,
                'qty_tolak' => 0,
                'qty_sesuai' => $item->volume,
            ];
        });

        // Jika akses 'pemeriksaan', arahkan ke view 'berita_acara'
        if ($jenis == 'pemeriksaan') {
            $jenis = 'berita_acara';
        }

        return view('surat.print_manager', [
            'mode' => 'satuan',
            'jenis_surat' => $jenis,
            'surat' => $surat, // Object Surat lengkap
            'sekolah' => $sekolah,
            'rekanan' => $rekanan,
            'items' => $items, // Collection Items yang sudah dimapping
            'kepala_sekolah' => $kepalaSekolah, // Object Pejabat
            'pengurus_barang' => $pengurusBarang, // Object Pejabat
        ]);
    }

    public function cetakBundel($id)
    {
        Carbon::setLocale('id');

        // 1. DATA BELANJA
        $belanja = Belanja::with([
            'rincis.rkas',
            'rekanan',
            'korek',
            'user.sekolah',
            'surats',
            'anggaran',
        ])->findOrFail($id);

        // 2. DATA SEKOLAH
        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;

        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
            if (! $sekolah) {
                return back()->with('error', 'Data Sekolah tidak ditemukan');
            }
        }

        $rekanan = $belanja->rekanan;

        // 3. PEJABAT
        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah,
            'nip' => $sekolah->nip_kepala_sekolah,
        ];

        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // 4. ITEM (SAMA PERSIS cetakSatuan)
        $items = $belanja->rincis->map(function ($item) {
            return (object) [
                'nama_barang' => $item->namakomponen,
                'satuan' => $item->rkas->satuan ?? $item->satuan,
                'qty' => $item->volume,

                'harga_satuan' => $item->harga_satuan,
                'harga_penawaran' => $item->harga_penawaran,

                'qty_pesan' => $item->volume,
                'qty_terima' => $item->volume,
                'qty_tolak' => 0,
                'qty_sesuai' => $item->volume,
            ];
        });

        /**
         * 5. HELPER PEMBUAT $surat
         * IDENTIK dengan cetakSatuan
         */
        $buatSurat = function ($kodeJenis, $jenisLabel) use ($belanja) {

            $suratDb = $belanja->surats->where('jenis_surat', $kodeJenis)->first();

            $noSurat = 'DRAFT (Belum Generate)';
            $tglSurat = now();
            $sifat = 'Segera';
            $lampiran = '-';

            if ($suratDb) {
                $noSurat = $suratDb->nomor_surat;
                $tglSurat = $suratDb->tanggal_surat;
                $sifat = $suratDb->sifat ?? 'Segera';
                $lampiran = $suratDb->lampiran ?? '-';
            }

            if ($kodeJenis === 'BAPB') {
                $tglSurat = $belanja->tanggal_bast
                    ? Carbon::parse($belanja->tanggal_bast)
                    : ($suratDb ? $suratDb->tanggal_surat : now());
            }

            return (object) [
                'nomor_surat' => $noSurat,
                'tanggal_surat' => $tglSurat->format('Y-m-d'),

                'anggaran' => $belanja->anggaran,
                'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
                'kode_rekening' => $belanja->korek->ket ?? '-',
                'nama_kegiatan' => $belanja->uraian,

                'perihal' => $jenisLabel.' '.$belanja->uraian,
                'sifat' => $sifat,
                'lampiran' => $lampiran,

                'nama_pekerjaan' => $belanja->uraian,
                'hari_ini' => $tglSurat->translatedFormat('l'),
                'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
            ];
        };

        /**
         * 6. RENDER BUNDEL (loop di controller)
         */
        $html = '';

        $jenisSurat = [
            'permintaan' => ['PH', 'Permintaan Harga'],
            'negosiasi' => ['NH', 'Negosiasi Harga'],
            'pesanan' => ['SP', 'Pesanan Barang'],
            'pemeriksaan' => ['BAPB', 'Berita Acara Pemeriksaan'],
        ];

        foreach ($jenisSurat as $jenis => [$kode, $label]) {
            $surat = $buatSurat($kode, $label);

            $html .= view('surat.print_manager', [
                'mode' => 'satuan',
                'jenis_surat' => $jenis === 'pemeriksaan' ? 'berita_acara' : $jenis,
                'surat' => $surat,
                'sekolah' => $sekolah,
                'rekanan' => $rekanan,
                'items' => $items,
                'kepala_sekolah' => $kepalaSekolah,
                'pengurus_barang' => $pengurusBarang,
                'belanja' => $belanja,
            ])->render();

            $html .= '<div style="page-break-after: always;"></div>';
        }

        return $html;
    }

    public function cetakSatuanPdf($id, $jenis)
    {
        Carbon::setLocale('id');

        // ==========================================
        // 1 - 4. LOGIKA DATA (SAMA PERSIS)
        // ==========================================

        // 1. DATA BELANJA
        $belanja = Belanja::with([
            'rincis.rkas', 'rekanan', 'korek', 'user.sekolah', 'surats', 'anggaran',
        ])->findOrFail($id);

        // 2. DATA SEKOLAH
        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;
        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
            if (! $sekolah) {
                return back()->with('error', 'Data Sekolah tidak ditemukan');
            }
        }
        $rekanan = $belanja->rekanan;

        // 3. PEJABAT
        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah,
            'nip' => $sekolah->nip_kepala_sekolah,
        ];
        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // 4. ITEM
        $items = $belanja->rincis->map(function ($item) {
            return (object) [
                'nama_barang' => $item->namakomponen,
                'satuan' => $item->rkas->satuan ?? $item->satuan,
                'qty' => $item->volume,
                'harga_satuan' => $item->harga_satuan,
                'harga_penawaran' => $item->harga_penawaran,
                'qty_pesan' => $item->volume,
                'qty_terima' => $item->volume,
                'qty_tolak' => 0,
                'qty_sesuai' => $item->volume,
            ];
        });

        // ==========================================
        // 5. MEMILIH JENIS SURAT (ADAPTASI DISINI)
        // ==========================================

        // Mapping URL param ke Kode Database & Label
        $configSurat = [
            'permintaan' => ['PH', 'Permintaan Harga'],
            'negosiasi' => ['NH', 'Negosiasi Harga'],
            'pesanan' => ['SP', 'Pesanan Barang'],
            'berita_acara' => ['BAPB', 'Berita Acara Pemeriksaan'],
            'pemeriksaan' => ['BAPB', 'Berita Acara Pemeriksaan'], // Alias
        ];

        if (! isset($configSurat[$jenis])) {
            abort(404, 'Jenis surat tidak ditemukan');
        }

        // Ambil konfigurasi untuk jenis yang dipilih
        [$kode, $label] = $configSurat[$jenis];

        // Helper Pembuat Surat (Dijalankan sekali saja utk jenis ini)
        $buatSurat = function ($kodeJenis, $jenisLabel) use ($belanja) {
            $suratDb = $belanja->surats->where('jenis_surat', $kodeJenis)->first();
            $teks = $belanja->korek->singkat ?? '';
            $is_penggandaan = \Illuminate\Support\Str::contains(strtolower($teks), [
                'penggandaan',
                'fotokopi',
                'fotocopy',
                'foto copy',
                'PENGGANDAAN',
            ]);
            $noSurat = 'DRAFT (Belum Generate)';
            $tglSurat = now();
            $sifat = 'Segera';
            $lampiran = '-';

            if ($suratDb) {
                $noSurat = $suratDb->nomor_surat;
                $tglSurat = $suratDb->tanggal_surat;
                $sifat = $suratDb->sifat ?? 'Segera';
                $lampiran = $suratDb->lampiran ?? '-';
            }

            if ($kodeJenis === 'BAPB') {
                $tglSurat = $belanja->tanggal_bast
                    ? Carbon::parse($belanja->tanggal_bast)
                    : ($suratDb ? $suratDb->tanggal_surat : now());
            }

            $no_bast = '-';
            if ($suratDb && ! empty($suratDb->no_bast)) {
                $no_bast = $suratDb->no_bast;
            } elseif (! empty($belanja->no_bast)) {
                $no_bast = $belanja->no_bast;
            }

            return (object) [
                'nomor_surat' => $noSurat,
                'tanggal_surat' => $tglSurat->format('Y-m-d'),
                'anggaran' => $belanja->anggaran,
                'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
                'kode_rekening' => $belanja->korek->ket ?? '-',
                'nama_kegiatan' => $belanja->uraian,
                'perihal' => $jenisLabel.' '.$belanja->uraian,
                'sifat' => $sifat,
                'lampiran' => $lampiran,
                'nama_pekerjaan' => $belanja->uraian,
                'hari_ini' => $tglSurat->translatedFormat('l'),
                'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
                'is_penggandaan' => $is_penggandaan,
                'no_bast' => $no_bast,
            ];
        };

        // Jalankan Helper
        $surat = $buatSurat($kode, $label);

        // ==========================================
        // 6. RENDER KONTEN HTML (SATU FILE SAJA)
        // ==========================================

        // Normalisasi nama view (jika 'pemeriksaan' ubah jadi 'berita_acara')
        $viewJenis = ($jenis == 'pemeriksaan') ? 'berita_acara' : $jenis;

        // Render View Partial (Konten Bersih)
        $contentHtml = view('surat.print_manager', [
            'jenis_surat' => $viewJenis,
            'surat' => $surat,
            'sekolah' => $sekolah,
            'rekanan' => $rekanan,
            'items' => $items,
            'kepala_sekolah' => $kepalaSekolah,
            'pengurus_barang' => $pengurusBarang,
            'belanja' => $belanja,

        ])->render();

        // ==========================================
        // 7. WRAPPER PDF (STYLE, FONT, MARGIN)
        // ==========================================

        $fontDir = storage_path('fonts');
        if (! file_exists($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        // Kita bungkus $contentHtml dengan Struktur HTML Lengkap + CSS
        // Ini PENTING agar font Arial dan Margin F4 terbaca
        $finalHtml = $contentHtml;

        // ==========================================
        // 8. GENERATE PDF
        // ==========================================

        $pdf = Pdf::loadHTML($finalHtml);

        $pdf->setOptions([
            'font_dir' => $fontDir,
            'font_cache' => $fontDir,
            'default_font' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $customPaper = [0, 0, 609.448, 935.433];
        $pdf->setPaper($customPaper, 'portrait');

        // Nama File
        $namaFile = strtoupper($jenis).'_'.preg_replace('/[^A-Za-z0-9\-]/', '-', $belanja->no_bukti).'.pdf';

        return $pdf->stream($namaFile);
    }

    public function cetakParsialPdf($id)
    {
        Carbon::setLocale('id');

        // ==========================================
        // 1 - 4. LOGIKA DATA (SAMA PERSIS)
        // ==========================================

        // 1. DATA BELANJA
        $suratDipilih = Surat::with(['belanja.rekanan', 'belanja.korek', 'belanja.user.sekolah'])
            ->findOrFail($id);
        $belanja = $suratDipilih->belanja;
        $kegiatan = Str::lower($belanja->korek->singkat ?? '');
        $is_penggandaan = Str::contains($kegiatan, ['penggandaan', 'fotocopy', 'cetak', 'duplikasi']);

        // 2. DATA SEKOLAH
        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;
        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
            if (! $sekolah) {
                return back()->with('error', 'Data Sekolah tidak ditemukan');
            }
        }
        $rekanan = $belanja->rekanan;

        // 3. PEJABAT
        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah,
            'nip' => $sekolah->nip_kepala_sekolah,
        ];
        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // 4. ITEM
        $daftarBapb = Surat::where('belanja_id', $belanja->id)
            ->where('jenis_surat', 'BAPB')
            ->with(['rincis.rkas'])
            ->orderBy('tanggal_surat', 'asc')
            ->get();

        // ==========================================
        // 5. MEMILIH JENIS SURAT (ADAPTASI DISINI)
        // ==========================================

        // Mapping URL param ke Kode Database & Label
        $configSurat = [
            'permintaan' => ['PH', 'Permintaan Harga'],
            'negosiasi' => ['NH', 'Negosiasi Harga'],
            'pesanan' => ['SP', 'Pesanan Barang'],
            'berita_acara' => ['BAPB', 'Berita Acara Pemeriksaan'],
            'pemeriksaan' => ['BAPB', 'Berita Acara Pemeriksaan'], // Alias
        ];

        // Cari Key View berdasarkan Kode Database (SP, BAPB, dll) dari surat yang dipilih
        $dbKode = $suratDipilih->jenis_surat;
        $jenisView = null;
        $labelSurat = 'Dokumen';

        foreach ($configSurat as $key => $val) {
            if ($val[0] === $dbKode) {
                $jenisView = ($key == 'pemeriksaan') ? 'berita_acara' : $key;
                $labelSurat = $val[1];
                break;
            }
        }

        if (! $jenisView) {
            abort(404, 'Jenis surat tidak dikenali dalam konfigurasi.');
        }

        // ==========================================
        // 6. FORMAT DATA SURAT ($surat)
        // ==========================================

        $tglSurat = $suratDipilih->tanggal_surat;

        // Khusus BAPB: Jika tanggal surat null, gunakan tanggal BAST belanja, atau fallback ke now
        if ($dbKode === 'BAPB') {
            if (! $tglSurat) {
                $tglSurat = $belanja->tanggal_bast ? Carbon::parse($belanja->tanggal_bast) : now();
            }
        }

        $surat = (object) [
            'nomor_surat' => $suratDipilih->nomor_surat ?? '...',
            'tanggal_surat' => $tglSurat->format('Y-m-d'),

            'anggaran' => $belanja->anggaran,
            'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
            'kode_rekening' => $belanja->korek->ket ?? '-',
            'nama_kegiatan' => $belanja->uraian,
            'is_penggandaan' => $is_penggandaan,
            'perihal' => $labelSurat.' '.$belanja->uraian,
            'sifat' => $suratDipilih->sifat ?? 'Segera',
            'lampiran' => $suratDipilih->lampiran ?? '-',
            'no_bast' => $suratDipilih->no_bast ?? '-',
            'nama_pekerjaan' => $belanja->uraian,
            'hari_ini' => $tglSurat->translatedFormat('l'),
            'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
            'is_parsial' => true,
        ];

        // ==========================================
        // 7. FORMAT ITEM BARANG ($items)
        // ==========================================

        // LOGIKA PENTING:
        // 1. Jika Surat ini punya relasi ke rincis (via pivot surat_rincis), pakai itu (BAPB Parsial).
        // 2. Jika Surat ini adalah SP (Pesanan), kita ingin menampilkan rekap dari semua BAPB (SP Parsial).
        // 3. Jika tidak ada keduanya, ambil semua item belanja (Fallback).

        $sourceItems = collect();

        if ($dbKode === 'SP' && $daftarBapb->isNotEmpty()) {
            // KASUS SP: Gabungkan semua item dari daftar BAPB yang sudah diambil di Step 4
            foreach ($daftarBapb as $bapb) {
                foreach ($bapb->rincis as $rinci) {
                    // Clone object agar volume bisa diset per baris (jika ada item sama beda tanggal)
                    $itemClone = clone $rinci;
                    // Ambil volume dari pivot BAPB terkait
                    $itemClone->volume_cetak = $rinci->pivot->volume;
                    // Simpan info tanggal kirim untuk ditampilkan di tabel SP
                    $itemClone->tanggal_kirim = $bapb->tanggal_surat;
                    $sourceItems->push($itemClone);
                }
            }
        } elseif ($suratDipilih->rincis->isNotEmpty()) {
            // KASUS BAPB PARSIAL: Ambil item yang nempel di surat ini saja
            $sourceItems = $suratDipilih->rincis;
        } else {
            // FALLBACK: Ambil semua item belanja asli
            $sourceItems = $belanja->rincis;
        }

        // Mapping ke format standard View
        $items = $sourceItems->map(function ($item) {
            // Tentukan volume yang dipakai (Pivot > Property Custom > Volume Asli)
            $qty = $item->pivot->volume ?? $item->volume_cetak ?? $item->volume;

            return (object) [
                'nama_barang' => $item->namakomponen,
                'satuan' => $item->rkas->satuan ?? $item->satuan,
                'qty' => $qty,

                // Info tambahan (berguna untuk SP Parsial)
                'tanggal_kirim_raw' => $item->tanggal_kirim ?? null,
                'tanggal_kirim' => isset($item->tanggal_kirim) ? $item->tanggal_kirim->translatedFormat('d F Y') : null,

                'harga_satuan' => $item->harga_satuan,
                'harga_penawaran' => $item->harga_penawaran,

                // Field BAST
                'qty_pesan' => $qty,
                'qty_terima' => $qty,
                'qty_tolak' => 0,
                'qty_sesuai' => $qty,
            ];
        });

        // ==========================================
        // 8. RENDER VIEW (PARTIAL PRINT)
        // ==========================================

        $contentHtml = view('surat.print_manager', [
            'jenis_surat' => $jenisView,
            'surat' => $surat,
            'sekolah' => $sekolah,
            'rekanan' => $rekanan,
            'items' => $items->values()->all(),
            'kepala_sekolah' => $kepalaSekolah,
            'pengurus_barang' => $pengurusBarang,
            'belanja' => $belanja,
        ])->render();

        // ==========================================
        // 9. WRAPPER PDF (ARIAL + F4)
        // ==========================================

        $fontDir = storage_path('fonts');
        if (! file_exists($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $finalHtml = $contentHtml;

        // ==========================================
        // 10. GENERATE & STREAM
        // ==========================================

        $pdf = Pdf::loadHTML($finalHtml);

        $pdf->setOptions([
            'font_dir' => $fontDir,
            'font_cache' => $fontDir,
            'default_font' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        $customPaper = [0, 0, 609.448, 935.433];
        $pdf->setPaper($customPaper, 'portrait');

        $namaFile = strtoupper($jenisView).'_'.preg_replace('/[^A-Za-z0-9\-]/', '-', $suratDipilih->nomor_surat).'.pdf';

        return $pdf->stream($namaFile);
    }

    // --- Helper Terbilang (Sama seperti sebelumnya) ---
    private function terbilangTanggal($carbonDate)
    {
        // 1. Pastikan angka tanggal & tahun menjadi huruf kecil (lowercase)
        // trim() digunakan untuk menghapus spasi di depan/belakang jika ada
        $tanggal = strtolower(trim($this->penyebut($carbonDate->day)));
        $tahun = strtolower(trim($this->penyebut($carbonDate->year)));

        // 2. Ambil nama bulan (Format 'F' di Carbon sudah otomatis Kapital di awal: "Januari")
        $bulan = $carbonDate->translatedFormat('F');

        // 3. Gabungkan manual tanpa ucwords()
        // Output contoh: "sepuluh bulan Januari tahun dua ribu dua puluh enam"
        return "$tanggal bulan $bulan tahun $tahun";
    }

    private function penyebut($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';
        if ($nilai < 12) {
            $temp = ' '.$huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = $this->penyebut($nilai - 10).' belas';
        } elseif ($nilai < 100) {
            $temp = $this->penyebut($nilai / 10).' puluh'.$this->penyebut($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' seratus'.$this->penyebut($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = $this->penyebut($nilai / 100).' ratus'.$this->penyebut($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' seribu'.$this->penyebut($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = $this->penyebut($nilai / 1000).' ribu'.$this->penyebut($nilai % 1000);
        }

        return $temp;
    }

    /**
     * Cetak Kop Surat (PDF - DomPDF)
     */
    public function cetakKopPdf()
    {
        // 1. Ambil Data Sekolah
        $user = Auth::user();

        // Fallback data sekolah
        $sekolah = $user->sekolah ?? Sekolah::find($user->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data Sekolah tidak ditemukan.');
        }

        // 2. Load View
        // Pastikan Anda membuat file view ini (lihat langkah 2)
        $pdf = PDF::loadView('surat.cetak_kop', [
            'sekolah' => $sekolah,
        ]);

        // 3. Konfigurasi Kertas F4 (215mm x 330mm)
        // Konversi mm ke point (1 mm = 2.83465 pt)
        // Width: 215 * 2.83465 = ~609.45
        // Height: 330 * 2.83465 = ~935.43
        $customPaper = [0, 0, 609.4488, 935.433];
        $pdf->setPaper($customPaper, 'portrait');

        // 4. Konfigurasi Opsi (Penting agar Gambar Logo muncul)
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'default_font' => 'Arial',
        ]);

        // 5. Stream PDF
        return $pdf->stream('Kop_Surat_'.time().'.pdf');
    }

    public function downloadSemuaParsial($belanjaId)
    {
        $belanja = Belanja::with(['surats' => function ($q) {
            $q->where('is_parsial', true);
        }])->findOrFail($belanjaId);

        if ($belanja->surats->isEmpty()) {
            return back()->with('error', 'Tidak ada surat parsial untuk didownload.');
        }

        $zip = new ZipArchive;
        $fileName = 'Surat_Parsial_'.str_replace(['/', '\\'], '-', $belanja->no_bukti).'.zip';
        $zipPath = storage_path('app/public/'.$fileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($belanja->surats as $surat) {
                // Generate PDF menggunakan fungsi cetakSatuanPdf yang sudah ada, tapi ambil kontennya saja
                // Kita gunakan logic yang sama dengan cetakParsialPdf
                $pdf = $this->generatePdfContent($surat->id);

                $namaFileDalamZip = strtoupper($surat->jenis_surat).'_'.str_replace(['/', '\\'], '-', $surat->nomor_surat).'.pdf';
                $zip->addFromString($namaFileDalamZip, $pdf->output());
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Helper untuk generate konten PDF agar kode tidak duplikat.
     * Digunakan oleh: downloadSemuaParsial
     */
    private function generatePdfContent($suratId)
    {
        Carbon::setLocale('id');

        // 1. AMBIL DATA SURAT & RELASI
        $suratDipilih = Surat::with([
            'belanja.rekanan',
            'belanja.korek',
            'belanja.user.sekolah',
            'rincis.rkas', // Penting untuk mengambil satuan/data barang di pivot
        ])->findOrFail($suratId);

        $belanja = $suratDipilih->belanja;

        // Cek apakah kegiatan penggandaan (untuk penyesuaian label jika perlu)
        $kegiatan = Str::lower($belanja->korek->singkat ?? '');
        $is_penggandaan = Str::contains($kegiatan, ['penggandaan', 'fotocopy', 'cetak', 'duplikasi']);

        // 2. DATA SEKOLAH (Fallback logic)
        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;
        if (! $sekolah) {
            $sekolah = Sekolah::find($belanja->user->sekolah_id ?? Auth::user()->sekolah_id);
        }

        $rekanan = $belanja->rekanan;

        // 3. PEJABAT
        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah ?? '-',
            'nip' => $sekolah->nip_kepala_sekolah ?? '-',
        ];
        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // 4. CONFIG SURAT
        $configSurat = [
            'permintaan' => ['PH', 'Permintaan Harga'],
            'negosiasi' => ['NH', 'Negosiasi Harga'],
            'pesanan' => ['SP', 'Pesanan Barang'],
            'berita_acara' => ['BAPB', 'Berita Acara Pemeriksaan'],
            'pemeriksaan' => ['BAPB', 'Berita Acara Pemeriksaan'],
        ];

        $dbKode = $suratDipilih->jenis_surat;
        $jenisView = null;
        $labelSurat = 'Dokumen';

        // Cari View & Label berdasarkan Kode DB
        foreach ($configSurat as $key => $val) {
            if ($val[0] === $dbKode) {
                $jenisView = ($key == 'pemeriksaan') ? 'berita_acara' : $key;
                $labelSurat = $val[1];
                break;
            }
        }

        // 5. FORMAT DATA SURAT
        $tglSurat = $suratDipilih->tanggal_surat;
        if ($dbKode === 'BAPB' && ! $tglSurat) {
            $tglSurat = $belanja->tanggal_bast ? Carbon::parse($belanja->tanggal_bast) : now();
        }

        $surat = (object) [
            'nomor_surat' => $suratDipilih->nomor_surat ?? '...',
            'tanggal_surat' => $tglSurat->format('Y-m-d'),
            'anggaran' => $belanja->anggaran,
            'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
            'kode_rekening' => $belanja->korek->ket ?? '-',
            'nama_kegiatan' => $belanja->uraian,
            'is_penggandaan' => $is_penggandaan,
            'perihal' => $labelSurat.' '.$belanja->uraian,
            'sifat' => $suratDipilih->sifat ?? 'Segera',
            'lampiran' => $suratDipilih->lampiran ?? '-',
            'no_bast' => $suratDipilih->no_bast ?? '-',
            'nama_pekerjaan' => $belanja->uraian,
            'hari_ini' => $tglSurat->translatedFormat('l'),
            'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
            'is_parsial' => true,
        ];

        // 6. FILTER ITEM BARANG (LOGIKA PARSIAL)
        $sourceItems = collect();

        // Ambil daftar BAPB lain jika ini adalah SP (untuk rekap SP Parsial)
        $daftarBapb = Surat::where('belanja_id', $belanja->id)
            ->where('jenis_surat', 'BAPB')
            ->with(['rincis'])
            ->orderBy('tanggal_surat', 'asc')
            ->get();

        if ($dbKode === 'SP' && $daftarBapb->isNotEmpty()) {
            // Kasus SP: Gabungkan item dari semua BAPB
            foreach ($daftarBapb as $bapb) {
                foreach ($bapb->rincis as $rinci) {
                    $itemClone = clone $rinci;
                    $itemClone->volume_cetak = $rinci->pivot->volume;
                    $itemClone->tanggal_kirim = $bapb->tanggal_surat;
                    $sourceItems->push($itemClone);
                }
            }
        } elseif ($suratDipilih->rincis->isNotEmpty()) {
            // Kasus BAPB Parsial / SP tanpa BAPB lain: Ambil pivot surat ini
            $sourceItems = $suratDipilih->rincis;
        } else {
            // Fallback (Jaga-jaga)
            $sourceItems = $belanja->rincis;
        }

        // 7. MAPPING ITEM
        $items = $sourceItems->map(function ($item) {
            // Priority Volume: Pivot > Custom Property > Master
            $qty = $item->pivot->volume ?? $item->volume_cetak ?? $item->volume;

            return (object) [
                'nama_barang' => $item->namakomponen,
                'satuan' => $item->rkas->satuan ?? $item->satuan,
                'qty' => $qty,
                'tanggal_kirim' => isset($item->tanggal_kirim) ? $item->tanggal_kirim->translatedFormat('d F Y') : null,
                'harga_satuan' => $item->harga_satuan,
                'harga_penawaran' => $item->harga_penawaran,
                // Field BAST
                'qty_pesan' => $qty,
                'qty_terima' => $qty,
                'qty_tolak' => 0,
                'qty_sesuai' => $qty,
                'is_parsial' => true,
            ];
        });

        // 8. RENDER VIEW
        $contentHtml = view('surat.print_manager', [
            'jenis_surat' => $jenisView,
            'surat' => $surat,
            'sekolah' => $sekolah,
            'rekanan' => $rekanan,
            'items' => $items,
            'kepala_sekolah' => $kepalaSekolah,
            'pengurus_barang' => $pengurusBarang,
            'belanja' => $belanja,
        ])->render();

        // 9. CONFIG DOMPDF
        $fontDir = storage_path('fonts');
        if (! file_exists($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $pdf = PDF::loadHTML($contentHtml);
        $pdf->setOptions([
            'font_dir' => $fontDir,
            'font_cache' => $fontDir,
            'default_font' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        // KEMBALIKAN OBJEK PDF (BUKAN STREAM/DOWNLOAD)
        return $pdf;
    }

    public function rekapKeseluruhanTriwulanPdf()
    {
        Carbon::setLocale('id');
        $user = Auth::user();

        // Pastikan mengambil objek Sekolah
        $sekolah = Sekolah::with('relasiSudin')->find($user->sekolah_id);
        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $triwulan = $sekolah->triwulan_aktif;
        $tahun = $sekolah->tahun_aktif ?? date('Y');

        // Query langsung ke tabel surats dengan eger loading yang lengkap
        $listSurat = Surat::with(['belanja.rekanan', 'belanja.korek'])
            ->where('sekolah_id', $sekolah->id)
            ->where('triwulan', $triwulan)
            ->whereYear('tanggal_surat', $tahun)
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('nomor_surat', 'asc')
            ->get();

        // Mapping Label Jenis Surat
        $labelJenis = [
            'PH' => 'Permintaan Harga',
            'NH' => 'Negosiasi Harga',
            'SP' => 'Surat Pesanan',
            'BAPB' => 'Berita Acara Penyerahan Barang',
            'talangan' => 'Pernyataan Dana Talangan',
            'NPD' => 'Nota Permintaan Dana',
            'STS' => 'Surat Tanda Setoran',
        ];

        // --- TAMBAHAN: Hitung Statistik Ringkasan untuk PDF ---
        $totalSurat = $listSurat->count();
        $statistik = $listSurat->groupBy('jenis_surat')->map(function ($row) {
            return $row->count();
        });

        $pdf = PDF::loadView('surat.rekap_pdf', [
            'listSurat' => $listSurat,
            'sekolah' => $sekolah,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'labelJenis' => $labelJenis,
            'totalSurat' => $totalSurat,
            'statistik' => $statistik,
        ]);

        // Ukuran kertas F4 / Custom Folio Landscape
        $customPaper = [0, 0, 609.448, 935.433];
        $pdf->setPaper($customPaper, 'landscape');

        return $pdf->stream("AGENDA_SURAT_KELUAR_TW_{$triwulan}.pdf");
    }

    public function createTalangan()
    {
        $user = Auth::user();
        $sekolah = Sekolah::find($user->sekolah_id);
        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $anggaranId = $sekolah->anggaran_id_aktif;
        $triwulanAktif = $sekolah->triwulan_aktif;

        // Ambil SEMUA data Belanja di TW ini, format ke Array untuk dibaca JavaScript
        $listBelanja = Belanja::with(['korek', 'rincis'])
            ->where('anggaran_id', $anggaranId)
            ->where('tw', $triwulanAktif)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'kodeakun' => $b->kodeakun,
                    'nama_rekening' => $b->korek->ket ?? '-',
                    'tanggal' => \Carbon\Carbon::parse($b->tanggal)->format('d/m/Y'),
                    'uraian' => $b->uraian,
                    'jumlah' => $b->rincis->sum('total_bruto'),
                ];
            });

        // Ambil daftar Kode Akun unik untuk Dropdown
        $listRekening = collect($listBelanja)->unique('kodeakun')->values();

        // Ambil Riwayat Talangan (Digabungkan berdasarkan Nomor Surat agar rapi)
        $riwayatTalangan = Talangan::with(['korek', 'surat'])
            ->where('anggaran_id', $anggaranId)
            ->where('triwulan', $triwulanAktif)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('surat_id'); // Grouping tetap berdasarkan ID surat

        return view('surat.talangan_input', compact('sekolah', 'anggaranId', 'triwulanAktif', 'listBelanja', 'listRekening', 'riwayatTalangan'));
    }

    public function storeTalangan(Request $request)
    {
        $user = Auth::user();
        $sekolah = Sekolah::find($user->sekolah_id);
        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $anggaranId = $sekolah->anggaran_id_aktif;

        // --- PERBAIKAN: Ambil data anggaran spesifik yang sedang aktif ---
        // Pastikan namespace model Anggaran sudah di-import di atas: use App\Models\Anggaran;
        $anggaranAktif = Anggaran::find($anggaranId);
        $namaAnggaran = $anggaranAktif ? $anggaranAktif->nama_anggaran : 'Tidak Diketahui';
        $tahunAnggaran = $anggaranAktif ? $anggaranAktif->tahun : ($sekolah->tahun_aktif ?? date('Y'));

        $request->validate([
            'nomor_surat' => 'required|string',
            'tanggal_surat' => 'required|date',
            'items' => 'required|array',
        ]);

        // 1. Simpan identitas ke model Surat
        $surat = Surat::create([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'jenis_surat' => 'talangan', // Jika ada kolom pembeda tipe
            'sekolah_id' => auth()->user()->sekolah_id,
            'triwulan' => $sekolah->triwulan_aktif,

            'keterangan' => 'Surat Talangan '.$namaAnggaran.' Triwulan '.$sekolah->triwulan_aktif.' Tahun '.$tahunAnggaran,
            'belanja_id' => null, // Bisa null karena kita simpan item di tabel Talangan
        ]);

        // 2. Simpan rincian ke model Talangan menggunakan ID surat tadi
        foreach ($request->items as $belanjaId => $item) {
            if (isset($item['selected']) && $item['selected'] == 1) {
                $belanja = Belanja::find($belanjaId);

                if ($belanja) {
                    Talangan::create([
                        'surat_id' => $surat->id, // Mengacu pada primary key model Surat
                        'anggaran_id' => $belanja->anggaran_id,
                        'triwulan' => $belanja->tw,
                        'kodeakun' => $belanja->kodeakun,
                        'kodepelanggan' => $item['kodepelanggan'],
                        'bulan' => $item['bulan'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
            }
        }

        return back()->with('success', 'Surat Talangan berhasil disimpan.');
    }

    public function destroyTalangan($surat_id)
    {
        try {
            // 1. Cari data suratnya
            $surat = Surat::findOrFail($surat_id);

            // 2. Hapus rincian talangan yang terkait (jika tidak pakai cascade delete di DB)
            Talangan::where('surat_id', $surat->id)->delete();

            // 3. Hapus data induk suratnya
            $surat->delete();

            return back()->with('success', 'Seluruh rincian surat talangan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    public function cetakTalanganPdf($suratId) // Nama variabel disesuaikan agar jelas
    {
        \Carbon\Carbon::setLocale('id');

        // 1. Cari data Surat Induk
        $suratInduk = Surat::findOrFail($suratId);

        // 2. Cari rincian talangan yang memiliki surat_id tersebut
        // Sertakan 'korek' agar nama rekening muncul di PDF
        $listTalangan = Talangan::with('korek')
            ->where('surat_id', $suratId)
            ->orderBy('id', 'asc')
            ->get();

        if ($listTalangan->isEmpty()) {
            return back()->with('error', 'Rincian item talangan tidak ditemukan.');
        }

        // Ambil baris pertama sebagai referensi data Anggaran & Triwulan
        $referensi = $listTalangan->first();

        $sekolah = Sekolah::with('sudin')->find(Auth::user()->sekolah_id);
        $anggaran = Anggaran::find($referensi->anggaran_id);
        $triwulanAktif = $referensi->triwulan; // Ambil triwulan dari data talangan

        $items = [];
        foreach ($listTalangan as $t) {
            $items[] = (object) [
                'nama_barang' => $t->kodepelanggan ?? 'ID Kosong',
                'bulan' => $t->bulan ?? '-',
                'qty' => 1,
                'harga_satuan' => $t->jumlah,
            ];
        }

        $rentangBulan = match ((int) $triwulanAktif) {
            1 => 'Januari - Maret',
            2 => 'April - Juni',
            3 => 'Juli - September',
            4 => 'Oktober - Desember',
            default => 'Sesuai Tagihan'
        };

        // 3. Susun data surat untuk View
        $suratData = (object) [
            'nomor_surat' => $suratInduk->nomor_surat ?? '-',
            'tanggal_surat_format' => $suratInduk->tanggal_surat
                ? \Carbon\Carbon::parse($suratInduk->tanggal_surat)->translatedFormat('d F Y')
                : now()->translatedFormat('d F Y'),
        ];

        $romawiMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        $romawiTriwulan = $romawiMap[(int) $triwulanAktif] ?? 'I';

        $pdf = PDF::loadView('surat.pdf_talangan', [
            'sekolah' => $sekolah,
            'referensi' => $referensi,
            'anggaran' => $anggaran,
            'surat' => $suratData,
            'rentangBulan' => $rentangBulan,
            'items' => $items,
            'romawiTriwulan' => $romawiTriwulan,
        ]);

        // Ukuran kertas F4
        $customPaper = [0, 0, 609.4488, 935.433];
        $pdf->setPaper($customPaper, 'portrait');

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'default_font' => 'Arial',
        ]);

        $filename = 'Surat_Talangan_'.str_replace('/', '_', $suratData->nomor_surat).'.pdf';

        return $pdf->stream($filename);
    }

    public function createCoverLpj(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // 2. Ambil data sekolah
        $sekolah = \App\Models\Sekolah::find(auth()->user()->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        // 3. Logika Fallback Triwulan (Anggaran -> Sekolah)
        $triwulanAktif = ! empty($anggaran->triwulan_aktif) ? $anggaran->triwulan_aktif : $sekolah->triwulan_aktif;

        // 4. Mengambil daftar rekening unik dari tabel Belanja di TW aktif & Anggaran aktif
        $listRekening = \App\Models\Belanja::with('korek')
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $triwulanAktif)
            ->get()
            ->unique('kodeakun')
            ->values();

        // 5. Passing $anggaran juga ke view jika dibutuhkan untuk judul/kop
        return view('surat.cover_input', compact('sekolah', 'triwulanAktif', 'listRekening', 'anggaran'));
    }

    public function generateCoverPdf(Request $request)
    {
        $request->validate([
            'jenis_bantuan' => 'required|in:BOP,BOSP',
            'nomor_spj' => 'required|string',
            'rekening_terpilih' => 'required|array|min:1',
            'logo' => 'nullable|image|max:2048',
        ]);

        $sekolah = Sekolah::with('sudin')->find(auth()->user()->sekolah_id);

        // Konversi logo ke Base64 jika user upload
        $logoBase64 = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoBase64 = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
        }

        $data = [
            'sekolah' => $sekolah,
            'triwulanAktif' => $sekolah->triwulan_aktif,
            'tahun' => date('Y'), // Atau ambil dari setting
            'jenisBantuan' => $request->jenis_bantuan,
            'nomorSpj' => $request->nomor_spj,
            'rekeningTerpilih' => $request->rekening_terpilih,
            'logoBase64' => $logoBase64,
        ];

        $pdf = PDF::loadView('surat.pdf_cover_lpj', $data);
        $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait');
        $pdf->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream('Cover_LPJ.pdf');
    }

    public function daftarSurat(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // 2. Ambil data sekolah untuk fallback triwulan_aktif
        $sekolah = \App\Models\Sekolah::find(auth()->user()->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        // 3. Logika Fallback Triwulan (Anggaran -> Sekolah)
        // Jika triwulan_aktif di anggaran kosong/null, gunakan milik sekolah
        $defaultTw = ! empty($anggaran->triwulan_aktif) ? $anggaran->triwulan_aktif : $sekolah->triwulan_aktif;

        // Tangkap request 'tw', jika tidak ada, gunakan $defaultTw
        $selectedTw = $request->input('tw', $defaultTw);

        // 4. Query Utama Belanja
        $query = \App\Models\Belanja::with(['korek', 'surats', 'rekanan'])
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $selectedTw);

        // Filter berdasarkan kode rekening jika ada
        if ($request->filled('kode_rekening')) {
            $query->where('kodeakun', $request->kode_rekening);
        }

        $listBelanja = $query->orderBy('tanggal', 'asc')
            ->paginate(15)
            ->withQueryString();

        // 5. Ambil daftar Kode Rekening untuk dropdown filter
        $listKorek = \App\Models\Belanja::with('korek')
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $selectedTw)
            ->get()
            ->unique('kodeakun')
            ->values();

        // 6. Passing data ke view
        return view('surat.daftar_surat', compact('listBelanja', 'listKorek', 'selectedTw', 'anggaran'));
    }

    public function daftarTalanganNpd(Request $request)
    {
        $user = Auth::user();
        $sekolah = Sekolah::find($user->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $anggaranId = $sekolah->anggaran_id_aktif;

        // 1. FOKUS KE MODEL SURAT: Gunakan relasi untuk menarik data belanja dan korek
        $listSurat = Surat::where('sekolah_id', $sekolah->id)
            ->where('triwulan', $sekolah->triwulan_aktif)
            ->whereIn('jenis_surat', ['talangan', 'NPD', 'STS'])
            ->get();

        // 5. Ubah variabel yang dikirim ke view menjadi 'listSurat'
        return view('surat.daftar_talangan_npd', compact('listSurat'));
    }

    public function hapusSurat($id)
    {
        $user = Auth::user();

        // Cari data surat berdasarkan ID
        $surat = Surat::find($id);

        // Jika data surat tidak ditemukan
        if (! $surat) {
            return back()->with('error', 'Data surat tidak ditemukan.');
        }

        // KEAMANAN: Pastikan user hanya bisa menghapus surat milik sekolahnya sendiri
        if ($surat->sekolah_id != $user->sekolah_id) {
            return back()->with('error', 'Anda tidak memiliki otorisasi untuk menghapus surat ini.');
        }

        try {
            // Hapus data surat
            $surat->delete();

            return back()->with('success', 'Surat berhasil dihapus.');
        } catch (\Exception $e) {
            // Tangani error jika terjadi masalah pada database (misal: terkait relasi tabel)
            return back()->with('error', 'Terjadi kesalahan saat menghapus surat: '.$e->getMessage());
        }
    }

    /**
     * Memperbarui Triwulan pada Belanja dan semua Surat terkait secara massal
     */
    public function updateTw(Request $request, $belanja_id)
    {
        // 1. Validasi input: pastikan 'tw' wajib diisi dan berupa angka 1 sampai 4
        $request->validate([
            'tw' => 'required|integer|min:1|max:4',
        ]);

        // 4. MASS UPDATE: Perbarui triwulan di semua Surat yang terhubung dengan belanja_id ini
        // Query ini sangat ringan karena langsung dieksekusi di level database
        Surat::where('belanja_id', $belanja_id)->update([
            'triwulan' => $request->tw,
        ]);

        // 5. Hitung jumlah surat yang terdampak untuk ditampilkan di pesan sukses
        $jumlahSurat = Surat::where('belanja_id', $belanja_id)->count();

        // 6. Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', $jumlahSurat.' dokumen surat berhasil dipindah ke Triwulan '.$request->tw);
    }

    public function indexSeluruhSurat(Request $request)
    {
        $user = Auth::user();
        $sekolah = \App\Models\Sekolah::find($user->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $triwulanAktif = $sekolah->triwulan_aktif;
        $tahunAktif = $sekolah->tahun_aktif ?? date('Y');

        // Mengambil semua surat milik sekolah di triwulan dan tahun aktif
        $listSurat = Surat::with(['belanja']) // Load relasi belanja jika dibutuhkan
            ->where('sekolah_id', $sekolah->id)
            ->where('triwulan', $triwulanAktif)
            ->whereYear('tanggal_surat', $tahunAktif)
            ->orderBy('tanggal_surat', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('surat.index_seluruh', compact('listSurat', 'triwulanAktif', 'tahunAktif'));
    }

    /**
     * Menghapus beberapa surat sekaligus (Bulk Delete)
     */
    public function hapusBanyakSurat(Request $request)
    {
        // 1. Validasi input pastikan array 'surat_ids' dikirim
        $request->validate([
            'surat_ids' => 'required|array|min:1',
            'surat_ids.*' => 'exists:surats,id',
        ], [
            'surat_ids.required' => 'Pilih minimal satu surat untuk dihapus.',
        ]);

        $user = Auth::user();

        // 2. Ambil data surat yang dipilih, pastikan hanya milik sekolah user (Keamanan)
        $surats = Surat::whereIn('id', $request->surat_ids)
            ->where('sekolah_id', $user->sekolah_id)
            ->get();

        if ($surats->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen surat valid yang dipilih.');
        }

        try {
            // 3. Gunakan DB Transaction agar aman
            DB::transaction(function () use ($surats) {
                foreach ($surats as $surat) {
                    // Hapus relasi pivot (rincian barang) terlebih dahulu jika ada
                    $surat->rincis()->detach();

                    // Hapus surat induk
                    $surat->delete();
                }
            });

            return back()->with('success', count($surats).' dokumen surat berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus surat: '.$e->getMessage());
        }
    }

    /**
     * Download banyak surat sekaligus dalam format ZIP (Multi-select)
     */
    public function downloadBanyakPdf(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'surat_ids' => 'required|array|min:1',
            'surat_ids.*' => 'exists:surats,id',
        ]);

        // 2. Ambil data surat yang dicentang
        $surats = Surat::whereIn('id', $request->surat_ids)->get();

        if ($surats->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen surat valid yang dipilih.');
        }

        // 3. Siapkan file ZIP
        $zip = new ZipArchive;
        $fileName = 'Kumpulan_Surat_SPJ_'.time().'.zip';
        $zipPath = storage_path('app/public/'.$fileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($surats as $surat) {

                // --- PENGECEKAN LOGIKA SURAT (NORMAL VS PARSIAL) ---
                if ($surat->is_parsial) {
                    // Jika Surat Parsial: Panggil helper generatePdfContent() eksisting
                    $pdf = $this->generatePdfContent($surat->id);
                } else {
                    // Jika Surat Normal: Panggil helper baru
                    $pdf = $this->generateNormalPdfContent($surat->id);
                }

                // 4. Buat nama file di dalam ZIP
                $safeNomor = str_replace(['/', '\\'], '-', $surat->nomor_surat);
                $labelParsial = $surat->is_parsial ? '_PARSIAL' : '';
                $namaFileDalamZip = strtoupper($surat->jenis_surat).$labelParsial.'_'.$safeNomor.'.pdf';

                // 5. Masukkan PDF ke ZIP
                $zip->addFromString($namaFileDalamZip, $pdf->output());
            }
            $zip->close();
        } else {
            return back()->with('error', 'Sistem gagal membuat file ZIP.');
        }

        // 6. Download ZIP dan bersihkan file sampah dari server
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Helper Baru: Untuk generate PDF Surat Normal (Logika murni dari cetakSatuanPdf)
     */
    private function generateNormalPdfContent($suratId)
    {
        Carbon::setLocale('id');

        // Ambil Data Surat & Belanja
        $suratDipilih = Surat::with([
            'belanja.rincis.rkas', 'belanja.rekanan', 'belanja.korek', 'belanja.user.sekolah', 'belanja.anggaran',
        ])->findOrFail($suratId);

        $belanja = $suratDipilih->belanja;

        $sekolah = $belanja->user->sekolah ?? Auth::user()->sekolah;
        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
        }
        $rekanan = $belanja->rekanan;

        $kepalaSekolah = (object) [
            'nama' => $sekolah->nama_kepala_sekolah,
            'nip' => $sekolah->nip_kepala_sekolah,
        ];
        $pengurusBarang = (object) [
            'nama' => $sekolah->nama_pengurus_barang ?? '...................',
            'nip' => $sekolah->nip_pengurus_barang ?? '-',
            'jabatan' => 'Pengurus Barang',
        ];

        // Rincian Barang Surat Normal (Ambil total volume dari tabel rincis)
        $items = $belanja->rincis->map(function ($item) {
            return (object) [
                'nama_barang' => $item->namakomponen,
                'satuan' => $item->rkas->satuan ?? $item->satuan,
                'qty' => $item->volume,
                'harga_satuan' => $item->harga_satuan,
                'harga_penawaran' => $item->harga_penawaran,
                'qty_pesan' => $item->volume,
                'qty_terima' => $item->volume,
                'qty_tolak' => 0,
                'qty_sesuai' => $item->volume,
            ];
        });

        // Mapping Konfigurasi Surat
        $configSurat = [
            'PH' => ['permintaan', 'Permintaan Harga'],
            'NH' => ['negosiasi', 'Negosiasi Harga'],
            'SP' => ['pesanan', 'Pesanan Barang'],
            'BAPB' => ['berita_acara', 'Berita Acara Pemeriksaan'],
        ];

        $dbKode = $suratDipilih->jenis_surat;
        $jenisView = $configSurat[$dbKode][0] ?? 'permintaan';
        $labelSurat = $configSurat[$dbKode][1] ?? 'Dokumen';

        $tglSurat = $suratDipilih->tanggal_surat;
        if ($dbKode === 'BAPB' && ! $tglSurat) {
            $tglSurat = $belanja->tanggal_bast ? Carbon::parse($belanja->tanggal_bast) : now();
        }
        $teks = $belanja->korek->singkat ?? '';
        $is_penggandaan = \Illuminate\Support\Str::contains(strtolower($teks), [
            'penggandaan',
            'fotokopi',
            'fotocopy',
            'foto copy',
            'PENGGANDAAN',
        ]);
        // Susun Object Surat
        $surat = (object) [
            'nomor_surat' => $suratDipilih->nomor_surat ?? 'DRAFT',
            'tanggal_surat' => $tglSurat->format('Y-m-d'),
            'anggaran' => $belanja->anggaran,
            'periode' => 'Triwulan '.($belanja->triwulan ?? 1),
            'kode_rekening' => $belanja->korek->ket ?? '-',
            'nama_kegiatan' => $belanja->uraian,
            'perihal' => $labelSurat.' '.$belanja->uraian,
            'sifat' => $suratDipilih->sifat ?? 'Segera',
            'lampiran' => $suratDipilih->lampiran ?? '-',
            'nama_pekerjaan' => $belanja->uraian,
            'hari_ini' => $tglSurat->translatedFormat('l'),
            'tanggal_terbilang' => $this->terbilangTanggal($tglSurat),
            'is_parsial' => false,
            'is_penggandaan' => $is_penggandaan,
            'no_bast' => $suratDipilih->no_bast ?? '',
        ];

        // Render HTML
        $contentHtml = view('surat.print_manager', [
            'jenis_surat' => $jenisView,
            'surat' => $surat,
            'sekolah' => $sekolah,
            'rekanan' => $rekanan,
            'items' => $items,
            'kepala_sekolah' => $kepalaSekolah,
            'pengurus_barang' => $pengurusBarang,
            'belanja' => $belanja,
        ])->render();

        // Render PDF (Jangan gunakan return stream, tapi kembalikan object PDF)
        $fontDir = storage_path('fonts');
        if (! file_exists($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $pdf = PDF::loadHTML($contentHtml);
        $pdf->setOptions([
            'font_dir' => $fontDir,
            'font_cache' => $fontDir,
            'default_font' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf;
    }

    /**
     * Download semua surat Normal (Bukan Parsial) berdasarkan ID Belanja dalam bentuk ZIP
     */
    public function downloadSemuaNormalZip(Request $request)
    {
        // 1. Perpanjang waktu eksekusi server (KARENA GENERATE BANYAK PDF BUTUH WAKTU LAMA)
        ini_set('max_execution_time', 300); // 5 Menit
        ini_set('memory_limit', '512M'); // Amankan memory

        $user = Auth::user();
        $sekolah = \App\Models\Sekolah::find($user->sekolah_id);

        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        // 2. Ambil Anggaran Aktif dari Middleware
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // Tentukan Triwulan Aktif
        $triwulanAktif = ! empty($anggaran->triwulan_aktif) ? $anggaran->triwulan_aktif : $sekolah->triwulan_aktif;

        // 3. Ambil SEMUA surat normal pada anggaran dan triwulan aktif
        $surats = Surat::where('sekolah_id', $sekolah->id)
            ->where(function ($query) {
                // Pastikan HANYA surat normal (bukan parsial/talangan)
                $query->where('is_parsial', 0)
                    ->orWhere('is_parsial', false)
                    ->orWhereNull('is_parsial');
            })
            // Relasikan ke tabel belanja untuk memastikan anggaran_id dan triwulan cocok
            ->whereHas('belanja', function ($q) use ($anggaran, $triwulanAktif) {
                $q->where('anggaran_id', $anggaran->id)
                    ->where('tw', $triwulanAktif);
            })
            // Ambil hanya jenis surat SPJ utama
            ->whereIn('jenis_surat', ['PH', 'NH', 'SP', 'BAPB'])
            ->get();

        if ($surats->isEmpty()) {
            return back()->with('error', "Tidak ada dokumen surat normal pada {$anggaran->singkatan} Triwulan {$triwulanAktif} yang bisa didownload.");
        }

        // 4. Inisialisasi ZipArchive
        $zip = new ZipArchive;
        $fileName = 'Arsip_Surat_'.$anggaran->singkatan.'_TW'.$triwulanAktif.'_'.time().'.zip';
        $zipPath = storage_path('app/public/'.$fileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            // 5. Looping setiap surat, generate PDF, dan masukkan ke ZIP
            foreach ($surats as $surat) {
                // Generate konten PDF menggunakan helper yang sudah ada
                $pdf = $this->generateNormalPdfContent($surat->id);

                // Bersihkan karakter garis miring
                $safeNomor = str_replace(['/', '\\'], '-', $surat->nomor_surat);

                // Format Nama File di dalam ZIP: {belanja_id}_{JENIS}_{id}_{nomor_surat}.pdf
                $namaFileDalamZip = $surat->belanja_id.'_'.strtoupper($surat->jenis_surat).'_'.$surat->id.'_'.$safeNomor.'.pdf';

                // Tambahkan PDF ke dalam ZIP
                $zip->addFromString($namaFileDalamZip, $pdf->output());
            }

            $zip->close();
        } else {
            return back()->with('error', 'Sistem gagal membuat file ZIP.');
        }

        // 6. Download ZIP dan hapus file sampah dari server setelah terkirim
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
