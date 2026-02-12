<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\DasarPajak;
use App\Models\RefEkskul;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use App\Models\SpjEkskul;
use App\Models\SpjEkskulDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Pastikan model ini ada

class EkskulController extends Controller
{
    // ... method index, edit, update, dll biarkan ...

    /**
     * 1. FORM INPUT EKSKUL (Logic RKAS)
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        $sekolah = Sekolah::find($user->sekolah_id);

        // A. Data Pelatih
        $pelatih = Rekanan::where('sekolah_id', $sekolah->id)
            ->where('ket', 1)
            ->orderBy('nama_rekanan', 'asc')
            ->get();

        // B. List Kegiatan (RKAS)
        $listKegiatan = DB::table('rkas')
            ->leftJoin('kegiatans', 'rkas.idbl', '=', 'kegiatans.idbl')
            ->where('rkas.anggaran_id', $anggaran->id)
            ->select('rkas.idbl', DB::raw('COALESCE(kegiatans.namagiat, rkas.giatsubteks) as namagiat'))
            ->distinct()
            ->get();

        // C. Pajak PPh 21
        $pajakPPh21 = DasarPajak::where('nama_pajak', 'like', '%PPh 21%')->get();

        // D. Daftar Ekskul (Untuk Dropdown di setiap baris)
        $daftarEkskul = RefEkskul::where('sekolah_id', $sekolah->id)
            ->orderBy('nama', 'asc')->get();

        return view('ekskul.create', compact('pelatih', 'listKegiatan', 'pajakPPh21', 'anggaran', 'sekolah', 'daftarEkskul'));
    }

    /**
     * 2. API AJAX: Ambil Rekening
     */
    public function getRekening(Request $request)
    {
        $anggaran = $request->anggaran_data;
        $rekening = DB::table('rkas')
            ->join('koreks', 'rkas.kodeakun', '=', 'koreks.id')
            ->where('rkas.anggaran_id', $anggaran->id)
            ->where('rkas.idbl', $request->idbl)
            ->select('koreks.id as kodeakun', 'koreks.uraian_singkat as namarekening')
            ->distinct()
            ->get();

        return response()->json($rekening);
    }

    public function getByPelatih(Request $request)
    {
        // Pastikan Anda sudah import model RefEkskul di bagian atas file
        // use App\Models\RefEkskul;

        if (! $request->rekanan_id) {
            return response()->json(null);
        }

        $ekskul = RefEkskul::where('rekanan_id', $request->rekanan_id)->first();

        return response()->json($ekskul);
    }

    /**
     * 3. API AJAX: Ambil Komponen
     */
    public function getKomponen(Request $request)
    {
        try {
            // 1. AMBIL USER & SEKOLAH
            $user = auth()->user();
            $sekolah = Sekolah::find($user->sekolah_id);

            // 2. AMBIL DATA ANGGARAN (Dengan Fallback jika Middleware Gagal)
            $anggaran = $request->anggaran_data;

            // Jika null, cari manual
            if (! $anggaran) {
                $anggaran = Anggaran::where('sekolah_id', $user->sekolah_id)
                    ->where('is_aktif', true)
                    ->first();
            }

            // Jika masih null, lempar error agar JS tahu
            if (! $anggaran) {
                throw new \Exception('Anggaran Tahun Aktif tidak ditemukan.');
            }

            // 3. LOGIKA TRIWULAN
            // Bersihkan string (misal "Triwulan 1" jadi 1)
            $tw = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

            $bulanRange = match ($tw) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
                default => range(1, 12) // Default setahun jika tidak ada triwulan
            };

            // 4. QUERY DATABASE (FIXED GROUP BY)
            $komponen = Rkas::join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')
                ->where('rkas.anggaran_id', $anggaran->id)
                ->where('rkas.idbl', $request->idbl)
                ->where('rkas.kodeakun', $request->kodeakun)
                ->whereIn('akb_rincis.bulan', $bulanRange)
                ->select(
                    'rkas.id',
                    'rkas.idblrinci',
                    'rkas.namakomponen',
                    'rkas.hargasatuan',
                    'rkas.satuan',
                    'rkas.keterangan', // <--- Kolom ini di-select...
                    DB::raw('SUM(akb_rincis.volume) as volume_tersedia')
                )
                // ...Maka WAJIB dimasukkan ke Group By juga:
                ->groupBy(
                    'rkas.id',
                    'rkas.idblrinci',
                    'rkas.namakomponen',
                    'rkas.hargasatuan',
                    'rkas.satuan',
                    'rkas.keterangan' // <--- PERBAIKAN UTAMA DISINI
                )
                ->havingRaw('SUM(akb_rincis.volume) > 0')
                ->get();

            return response()->json($komponen);

        } catch (\Throwable $e) {
            // 5. ERROR HANDLING JSON
            // Ini akan mengirim detail error ke Console Browser (bukan cuma error 500)
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
            ], 500);
        }
    }

    /**
     * 4. PROSES SIMPAN (Looping Belanja)
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'nullable',
            'idbl' => 'required',
            'kodeakun' => 'required',
            'items' => 'required|array|min:1',
            'items.*.rekanan_id' => 'required',
            'items.*.ref_ekskul_id' => 'required',
            'items.*.volume' => 'required|numeric|min:1',
            'pph21_id' => 'nullable|exists:dasar_pajaks,id',
        ]);

        $anggaran = $request->anggaran_data;
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);

        // Data Pendukung
        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $romawiTW = match ($twAktif) {
            1 => 'TWI', 2 => 'TWII', 3 => 'TWIII', 4 => 'TWIV', default => 'TW'
        };
        $kodeAnggaran = strtoupper($anggaran->singkatan ?? 'BOS');
        $tahun = Carbon::parse($request->tanggal)->format('Y');

        // Mapping Bulan untuk Pagu Check
        $mappingBulan = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];
        $bulanDicheck = $mappingBulan[$twAktif] ?? range(1, 12);

        try {
            DB::transaction(function () use ($request, $anggaran, $bulanDicheck, $kodeAnggaran, $romawiTW, $tahun, $twAktif) {

                // Logic Counter Nomor Bukti
                $inputNo = $request->no_bukti;

                if ($inputNo && is_numeric($inputNo)) {
                    $counter = (int) $inputNo;
                } else {
                    // Cari nomor terakhir tanpa memfilter Romawi TW-nya
                    // Kita gunakan wildcard % di posisi Romawi agar semua TW di tahun tersebut terpindai
                    $patternMatch = "/KW/{$kodeAnggaran}/%/{$tahun}";

                    $lastNumber = Belanja::where('anggaran_id', $anggaran->id)
                        ->whereYear('tanggal', $tahun)
                        ->where('no_bukti', 'LIKE', "%{$patternMatch}")
                        ->get()
                        ->map(function ($row) {
                            // Ambil angka depan sebelum tanda '/'
                            return (int) explode('/', $row->no_bukti)[0];
                        })
                        ->max() ?? 0;

                    $counter = $lastNumber + 1;
                }

                $items = array_values($request->items);

                foreach ($items as $index => $item) {

                    // A. Hitung Subtotal & Validasi Pagu
                    $subtotal = $item['volume'] * $item['harga_satuan'];

                    // ... (Validasi Pagu logic sama seperti sebelumnya) ...
                    $totalPagu = DB::table('akb_rincis')->where('idblrinci', $item['idblrinci'])->whereIn('bulan', $bulanDicheck)->sum('nominal');
                    $terpakai = DB::table('belanja_rincis')->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')->where('belanja_rincis.idblrinci', $item['idblrinci'])->where('belanjas.anggaran_id', $anggaran->id)->sum('total_bruto');

                    if (($totalPagu - $terpakai) < $subtotal) {
                        throw new \Exception('Pagu tidak cukup untuk: '.$item['namakomponen']);
                    }

                    // B. Hitung Pajak & Persentase
                    $nominalPPh = 0;
                    $persenPajak = 0; // Default 0%

                    if ($request->pph21_id) {
                        $masterPajak = DasarPajak::find($request->pph21_id);
                        if ($masterPajak) {
                            $persenPajak = $masterPajak->persen; // Ambil persen (misal 5 atau 2.5)
                            $nominalPPh = floor($subtotal * ($persenPajak / 100));
                        }
                    }

                    // Hitung Netto untuk SPJ Ekskul
                    $totalNetto = $subtotal - $nominalPPh;

                    // C. Generate Nomor
                    $nomorUrutStr = str_pad($counter, 3, '0', STR_PAD_LEFT);
                    $finalNoBukti = "{$nomorUrutStr}/KW/{$kodeAnggaran}/{$romawiTW}/{$tahun}";

                    // D. Simpan Header Belanja
                    $namaEkskul = RefEkskul::find($item['ref_ekskul_id'])->nama ?? '-';
                    $pelatih = Rekanan::find($item['rekanan_id']);
                    $namaPelatih = $pelatih->nama_rekanan ?? '-';

                    $belanja = Belanja::create([
                        'user_id' => auth()->id(),
                        'anggaran_id' => $anggaran->id,
                        'rekanan_id' => $item['rekanan_id'],
                        'tanggal' => $request->tanggal,
                        'no_bukti' => $finalNoBukti,
                        'uraian' => "Honor Ekskul $namaEkskul Kepada $namaPelatih",
                        'rincian' => $request->rincian ?? 'Honorarium Pelatih Ekskul',
                        'subtotal' => $subtotal,
                        'ppn' => 0,
                        'pph' => $nominalPPh,
                        'transfer' => $subtotal - $nominalPPh,
                        'idbl' => $request->idbl,
                        'kodeakun' => $request->kodeakun,
                        'status' => 'draft',
                        'tw' => $twAktif,
                    ]);

                    // E. Simpan Rincian Belanja
                    $belanja->rincis()->create([
                        'idblrinci' => $item['idblrinci'],
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $namaEkskul,
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'total_bruto' => $subtotal,
                        'bulan' => Carbon::parse($request->tanggal)->month,
                    ]);

                    // F. Simpan Pajak Belanja
                    if ($nominalPPh > 0) {
                        $belanja->pajaks()->create([
                            'dasar_pajak_id' => $request->pph21_id,
                            'nominal' => $nominalPPh,
                            'is_terima' => false,
                            'is_setor' => false,
                        ]);
                    }

                    // G. SIMPAN DATA SPJ EKSKUL (Ini Bagian Barunya)
                    // ====================================================

                    SpjEkskul::create([
                        'belanja_id' => $belanja->id,
                        'rekanan_id' => $item['rekanan_id'],
                        'ref_ekskul_id' => $item['ref_ekskul_id'],
                        'tw' => $twAktif, // Triwulan Sekolah Aktif
                        'jumlah_pertemuan' => $item['volume'], // Volume dianggap jumlah pertemuan
                        'honor' => $item['harga_satuan'], // Harga satuan dianggap honor per pertemuan
                        'total_honor' => $subtotal, // Bruto
                        'pph_persen' => $masterPajak->persen, // Misal 5.00
                        'pph_nominal' => $nominalPPh,
                        'total_netto' => $totalNetto, // Yang diterima
                    ]);
                    // ====================================================

                    $counter++;
                }
            });

            return redirect()->route('belanja.index')->with('success', 'Data Belanja & SPJ Ekskul berhasil disimpan!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function manageDetails($belanjaId)
    {
        // Ambil Data SPJ Ekskul berdasarkan Belanja ID
        $spj = SpjEkskul::with(['belanja', 'rekanan', 'details'])
            ->where('belanja_id', $belanjaId)
            ->firstOrFail();

        // Hitung progres input
        $sudahInput = $spj->details->count();
        $targetPertemuan = $spj->jumlah_pertemuan; // Sesuai Volume saat input Belanja

        return view('ekskul.manage_details', compact('spj', 'sudahInput', 'targetPertemuan'));
    }

    /**
     * SIMPAN SATU PERTEMUAN (MATERI + FOTO)
     */
    public function storeDetail(Request $request)
    {
        $request->validate([
            'spj_ekskul_id' => 'required',
            'tanggal_kegiatan' => 'required|date',
            'materi' => 'required|string',
            'jam_kegiatan' => 'required',
            'foto_kegiatan' => 'required|image|max:5120', // Max 5MB
        ]);

        try {
            // Ambil SPJ Induk untuk referensi watermark
            $spj = SpjEkskul::findOrFail($request->spj_ekskul_id);

            // Cek apakah jumlah input sudah melebihi target
            if ($spj->details()->count() >= $spj->jumlah_pertemuan) {
                return back()->with('error', 'Jumlah pertemuan sudah memenuhi kuota ('.$spj->jumlah_pertemuan.'x). Hapus data lama jika ingin mengganti.');
            }

            $waktuAcak = sprintf('%02d:%02d:%02d', $request->jam_kegiatan, rand(0, 59), rand(0, 59));
            // Proses Upload Foto + Watermark (Menggunakan fungsi yang sudah ada)
            $pathFoto = $this->processWatermark($request->file('foto_kegiatan'), $spj->belanja_id, $request->tanggal_kegiatan, $waktuAcak);

            // Simpan ke Database
            SpjEkskulDetail::create([
                'spj_ekskul_id' => $spj->id,
                'tanggal_kegiatan' => $request->tanggal_kegiatan,
                'materi' => $request->materi,
                'foto_kegiatan' => $pathFoto,
            ]);

            return back()->with('success', 'Data pertemuan berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * HAPUS PERTEMUAN
     */
    public function deleteDetail($id)
    {
        $detail = SpjEkskulDetail::findOrFail($id);

        // Hapus file fisik
        if ($detail->foto_kegiatan && \Illuminate\Support\Facades\Storage::disk('public')->exists($detail->foto_kegiatan)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($detail->foto_kegiatan);
        }

        $detail->delete();

        return back()->with('success', 'Data pertemuan dihapus.');
    }

    private function processWatermark($file, $belanjaId, $tanggalPertemuan, $jamInput)
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
        $waktu = $jamInput;

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

    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }
        $sekolahId = auth()->user()->sekolah_id;

        // Ambil Belanja yang memiliki relasi ke spj_ekskul
        // Ini memfilter agar yang tampil HANYA belanja Ekskul, bukan ATK/Lainnya
        $belanjas = Belanja::with(['rekanan', 'spjEkskul'])
            ->where('anggaran_id', $anggaran->id)
            ->whereHas('spjEkskul') // Hanya ambil yang punya data SPJ Ekskul
            ->orderBy('tanggal', 'desc')
            ->orderBy('no_bukti', 'desc')
            ->paginate(10);

        return view('ekskul.index', compact('belanjas', 'anggaran'));
    }

    /**
     * CETAK KWITANSI (HTML)
     */
    public function cetak($id)
    {
        // 1. Ambil Data SPJ Ekskul beserta Relasinya
        // Kita butuh data Belanja (Header), Rekanan (Pelatih), dan Ekskul
        $spj = SpjEkskul::with(['belanja.korek', 'rekanan', 'ekskul'])
            ->findOrFail($id);

        // 2. Ambil Data Sekolah (Untuk KOP & Tanda Tangan)
        $sekolah = Sekolah::with('Sudin')->find(auth()->user()->sekolah_id);
        $singkatanAnggaran = strtoupper($spj->belanja->anggaran->singkatan ?? 'BOS');

        if ($singkatanAnggaran === 'BOS') {
            $sumberDana = 'DINAS PENDIDIKAN PROVINSI DKI JAKARTA';
        } else {
            // Asumsi selain BOS adalah BOP
            $sumberDana = strtoupper($sekolah->Sudin->nama ?? '');
        }
        // 3. Konversi Angka ke Terbilang (Menggunakan Total Netto / Yang Diterima)
        // Fungsi $this->terbilang() ada di bawah
        $terbilang = ucwords($this->terbilang($spj->total_netto)).' Rupiah';

        // 4. Tampilkan View
        return view('ekskul.cetak_kwitansi', compact('spj', 'sekolah', 'terbilang', 'sumberDana'));
    }

    /**
     * HELPER: FUNGSI TERBILANG
     * Mengubah angka menjadi kalimat (Contoh: 100000 -> Seratus Ribu)
     */
    private function terbilang($x)
    {
        $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $x = abs((int) $x); // Pastikan integer positif

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
        } elseif ($x < 1000000000) {
            return $this->terbilang($x / 1000000).' juta'.$this->terbilang($x % 1000000);
        }
    }

    public function cetakAbsensi($id)
    {
        // Ambil Data SPJ, urutkan detail pertemuannya berdasarkan tanggal
        $spj = SpjEkskul::with(['rekanan', 'ekskul', 'belanja'])
            ->with(['details' => function ($query) {
                $query->orderBy('tanggal_kegiatan', 'asc');
            }])
            ->findOrFail($id);

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);

        return view('ekskul.cetak_absensi', compact('spj', 'sekolah'));
    }

    /**
     * HAPUS TRANSAKSI UTAMA (BELANJA + SPJ)
     */
    public function destroy($id)
    {
        // Cari data belanja beserta relasi SPJ-nya
        $belanja = Belanja::with('spjEkskul.details')->findOrFail($id);

        // 1. Hapus File Foto Kegiatan Fisik (Jika ada) agar tidak menuhin server
        if ($belanja->spjEkskul && $belanja->spjEkskul->details) {
            foreach ($belanja->spjEkskul->details as $detail) {
                if ($detail->foto_kegiatan && Storage::disk('public')->exists($detail->foto_kegiatan)) {
                    Storage::disk('public')->delete($detail->foto_kegiatan);
                }
            }
        }

        $belanja->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    // --- CRUD REFERENSI EKSKUL ---

    public function refEkskulIndex(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }
        $sekolahId = auth()->user()->sekolah_id;

        // TAMBAHKAN PENGECEKAN INI
        if (! $anggaran) {
            // Opsi A: Tampilkan error 404 jika anggaran wajib ada
            abort(404, 'Data Anggaran tidak ditemukan.');

            // Opsi B: Redirect kembali jika anggaran null
            // return redirect()->back()->with('error', 'Anggaran tidak ditemukan');
        }

        // Ambil data ekskul + info pelatihnya
        $ekskuls = RefEkskul::with('rekanan')
            ->where('sekolah_id', $sekolahId)
            ->orderBy('nama', 'asc')
            ->paginate(10);

        // Ambil list pelatih (rekanan yang jenisnya pelatih & aktif)
        $listPelatih = Rekanan::where('sekolah_id', $sekolahId)

            ->where('ket', 1) // 1 = Aktif/Pembina
            ->orderBy('nama_rekanan', 'asc')
            ->get();

        return view('ekskul.index_ekskul', compact('ekskuls', 'listPelatih'));
    }

    public function refEkskulStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'rekanan_id' => 'nullable|exists:rekanans,id', // Validasi ke tabel rekanans
        ]);

        RefEkskul::create([
            'sekolah_id' => auth()->user()->sekolah_id,
            'nama' => $request->nama,
            'rekanan_id' => $request->rekanan_id,
        ]);

        return back()->with('success', 'Ekskul berhasil ditambahkan.');
    }

    public function refEkskulUpdate(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'rekanan_id' => 'nullable|exists:rekanans,id',
        ]);

        $ekskul = RefEkskul::where('sekolah_id', auth()->user()->sekolah_id)->findOrFail($id);

        $ekskul->update([
            'nama' => $request->nama,
            'rekanan_id' => $request->rekanan_id,
        ]);

        return back()->with('success', 'Data Ekskul diperbarui.');
    }

    public function refEkskulDestroy($id)
    {
        $ekskul = RefEkskul::where('sekolah_id', auth()->user()->sekolah_id)->findOrFail($id);
        $ekskul->delete();

        return back()->with('success', 'Ekskul dihapus.');
    }

    public function create_bulk($id)
    {
        // Ambil Data SPJ Ekskul
        $spj = SpjEkskul::with(['belanja', 'rekanan', 'ekskul'])->findOrFail($id);

        return view('ekskul.bulk_create', compact('spj'));
    }

    /**
     * 6. PROSES SIMPAN BULK (BANYAK SEKALIGUS)
     */
    /**
     * 6. PROSES SIMPAN BULK (Logic Waktu Disesuaikan dengan Store Detail Satuan)
     */
    public function store_detail_bulk(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'foto_kegiatan' => 'required|array',
            'foto_kegiatan.*' => 'image|max:5120',
            'materi_json' => 'required|json',
            'tanggals' => 'required|array',

            // LOGIKA 1: Validasi Jam Global (Sama seperti jam_kegiatan di storeDetail)
            'jam_global' => 'required|numeric|min:0|max:23',
        ]);

        try {
            $spj = SpjEkskul::findOrFail($request->spj_ekskul_id);
            $listMateri = json_decode($request->materi_json, true);

            if (! is_array($listMateri)) {
                return back()->with('error', 'Format materi tidak valid.');
            }

            $files = $request->file('foto_kegiatan');
            $tanggals = $request->tanggals;

            // LOGIKA 2: Ambil Jam Inputan User
            $jamInput = $request->jam_global;

            $savedCount = 0;
            $quotaFull = false;

            DB::transaction(function () use ($files, $tanggals, $jamInput, $listMateri, $spj, &$savedCount, &$quotaFull) {

                foreach ($files as $index => $file) {
                    // Cek Kuota (Sama persis dengan storeDetail)
                    if ($spj->details()->count() >= $spj->jumlah_pertemuan) {
                        $quotaFull = true;
                        break;
                    }

                    if (isset($tanggals[$index])) {

                        // LOGIKA 3: Konstruksi Waktu (Sama persis dengan storeDetail)
                        // Gabung: Tanggal Array + Jam Global + Menit/Detik Acak
                        $waktuAcak = sprintf('%02d:%02d:%02d', $jamInput, rand(0, 59), rand(0, 59));
                        $tanggalFull = $tanggals[$index].' '.$waktuAcak;
                        // ---------------------------------------------------------

                        // Kirim tanggalFull ke Watermark
                        $pathFoto = $this->processWatermark($file, $spj->belanja_id, $tanggalFull, $waktuAcak);

                        $materiText = isset($listMateri[$index]) ? $listMateri[$index] : '-';

                        // Simpan ke Database dengan tanggalFull
                        SpjEkskulDetail::create([
                            'spj_ekskul_id' => $spj->id,
                            'tanggal_kegiatan' => $tanggalFull,
                            'materi' => $materiText,
                            'foto_kegiatan' => $pathFoto,
                        ]);

                        $savedCount++;
                    }
                }
            });

            // Feedback Message
            if ($quotaFull && $savedCount > 0) {
                return redirect()->route('ekskul.manage_details', $spj->belanja_id)
                    ->with('warning', "Berhasil menyimpan $savedCount data. Sisanya dilewati karena kuota penuh.");
            } elseif ($quotaFull && $savedCount == 0) {
                return back()->with('error', 'Kuota pertemuan sudah penuh!');
            }

            return redirect()->route('ekskul.manage_details', $spj->belanja_id)
                ->with('success', "Berhasil menyimpan $savedCount kegiatan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage())->withInput();
        }
    }

    /**
     * UPDATE DETAIL PERTEMUAN (Dipanggil dari Modal Edit)
     */
    public function updateDetail(Request $request, $id)
    {
        // 1. Validasi
        $request->validate([
            'tanggal_kegiatan' => 'required|date',
            'materi' => 'required|string',
            // Jam & Foto opsional (nullable), hanya diisi jika ingin ganti foto/waktu
            'jam_kegiatan' => 'nullable|numeric|min:0|max:23',
            'foto_kegiatan' => 'nullable|image|max:5120',
        ]);

        try {
            // Ambil data detail yang mau diedit
            $detail = SpjEkskulDetail::with('spjEkskul')->findOrFail($id);
            $spj = $detail->spjEkskul;

            // 2. Update Data Teks
            $detail->tanggal_kegiatan = $request->tanggal_kegiatan;
            $detail->materi = $request->materi;

            // 3. Cek apakah user mengupload foto baru?
            if ($request->hasFile('foto_kegiatan')) {

                // Hapus foto lama fisik jika ada
                if ($detail->foto_kegiatan && Storage::disk('public')->exists($detail->foto_kegiatan)) {
                    Storage::disk('public')->delete($detail->foto_kegiatan);
                }

                // Generate Waktu untuk Watermark
                // Jika user input jam di modal, pakai itu. Jika kosong, pakai jam sekarang.
                $jamInput = $request->jam_kegiatan ?? date('H');
                $waktuAcak = sprintf('%02d:%02d:%02d', $jamInput, rand(0, 59), rand(0, 59));

                // Pastikan format tanggal untuk watermark menggunakan tanggal baru
                // Kita gabung tanggal baru dengan jam acak agar formatnya Y-m-d H:i:s
                $tanggalFull = $request->tanggal_kegiatan.' '.$waktuAcak;

                // Proses Watermark Baru (Menggunakan fungsi processWatermark yang sudah ada di controller ini)
                $pathFoto = $this->processWatermark(
                    $request->file('foto_kegiatan'),
                    $spj->belanja_id,
                    $tanggalFull,
                    $waktuAcak
                );

                // Update path foto di database
                $detail->foto_kegiatan = $pathFoto;
            }

            // Simpan perubahan ke database
            $detail->save();

            // Redirect kembali ke halaman kelola
            return redirect()->route('ekskul.manage_details', $spj->belanja_id)
                ->with('success', 'Data pertemuan berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: '.$e->getMessage())->withInput();
        }
    }
}
