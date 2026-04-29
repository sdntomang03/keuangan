<?php

namespace App\Http\Controllers;

use App\Models\Akb;
use App\Models\AkbRinci;
use App\Models\Rkas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AkbController extends Controller
{
    protected $userId;

    protected $setting_id;

    public function __construct()
    {
        // Gunakan middleware closure untuk menangkap User ID setelah login tervalidasi
        $this->middleware(function ($request, $next) {
            $this->userId = Auth::id();
            $this->setting_id = auth()->user()->setting_id;

            return $next($request);
        });
    }

    public function index()
    {
        return view('akb.index');
    }

    public function import(Request $request)
    {

        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }
        $request->validate([
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',

        ]);
        $mapAnggaran = [
            'bos' => 10,
            'bop' => 20,
        ];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30; // Default 30 jika tidak terdaftar
        $count = 0;
        foreach ($request->file('json_files') as $file) {
            $content = json_decode(file_get_contents($file), true);
            $items = $content['data'] ?? [];

            foreach ($items as $item) {
                Akb::updateOrCreate(
                    [
                        // KUNCI PENCARIAN: Cari yang ID-nya sama DAN Tipenya sama
                        'idblrinci' => $jenisAnggaran.$item['idblrinci'],
                    ], // Kunci unik untuk cek data
                    [

                        // Identitas Akun & Komponen
                        'idakun' => $item['idakun'] ?? null,
                        // Detail Satuan & Volume
                        'volume' => $item['volume'] ?? null,
                        'pajak' => (int) ($item['pajak'] ?? 0),
                        'totalrincian' => (float) ($item['totalrincian'] ?? 0),

                        // Anggaran Bulanan (Bulan 1 - 12)
                        'bulan1' => (float) ($item['bulan1'] ?? 0),
                        'bulan2' => (float) ($item['bulan2'] ?? 0),
                        'bulan3' => (float) ($item['bulan3'] ?? 0),
                        'bulan4' => (float) ($item['bulan4'] ?? 0),
                        'bulan5' => (float) ($item['bulan5'] ?? 0),
                        'bulan6' => (float) ($item['bulan6'] ?? 0),
                        'bulan7' => (float) ($item['bulan7'] ?? 0),
                        'bulan8' => (float) ($item['bulan8'] ?? 0),
                        'bulan9' => (float) ($item['bulan9'] ?? 0),
                        'bulan10' => (float) ($item['bulan10'] ?? 0),
                        'bulan11' => (float) ($item['bulan11'] ?? 0),
                        'bulan12' => (float) ($item['bulan12'] ?? 0),

                        // Data Total & Selisih
                        'totalakb' => (float) ($item['totalakb'] ?? 0),
                        'selisih' => (float) ($item['selisih'] ?? 0),
                        // Realisasi Triwulan
                        'realtw1' => (float) ($item['realtw1'] ?? 0),
                        'realtw2' => (float) ($item['realtw2'] ?? 0),
                        'realtw3' => (float) ($item['realtw3'] ?? 0),
                        'realtw4' => (float) ($item['realtw4'] ?? 0),
                        'anggaran_id' => $anggaran->id,
                        'setting_id' => $this->setting_id,
                        'created_at' => now(),
                        'updated' => now(),
                    ]
                );
                $count++;
            }
        }

        return back()->with('success', "Berhasil mengimpor $count baris data dari ".count($request->file('json_files')).' file.');

    }

    public function rincian(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tampilan = $request->get('tampilan', 'bulanan');

        // =========================================================
        // 1. AMBIL DATA DENGAN RELASI YANG SUDAH DIGEMBOK AMAN
        // =========================================================
        $dataRkas = \App\Models\Rkas::with([
            'kegiatan',
            'korek',
            'akbrincis' => function ($q) use ($anggaran) {
                // Kunci relasi agar murni hanya mengambil data anggaran ini
                $q->where('anggaran_id', $anggaran->id);
            },
        ])
            ->where('anggaran_id', $anggaran->id)
            ->get();

        // =========================================================
        // 2. REKAP ANGKA SECARA MANUAL LEWAT COLLECTION (PASTI AKURAT)
        // =========================================================
        $dataRkas = $dataRkas->map(function ($rkas) {
            // Hitung nominal per bulan 1 sampai 12
            for ($i = 1; $i <= 12; $i++) {
                $rkas->{"bln_$i"} = $rkas->akbrincis->where('bulan', $i)->sum('nominal');
            }

            // Hitung nominal per Triwulan
            $rkas->tw_1 = $rkas->bln_1 + $rkas->bln_2 + $rkas->bln_3;
            $rkas->tw_2 = $rkas->bln_4 + $rkas->bln_5 + $rkas->bln_6;
            $rkas->tw_3 = $rkas->bln_7 + $rkas->bln_8 + $rkas->bln_9;
            $rkas->tw_4 = $rkas->bln_10 + $rkas->bln_11 + $rkas->bln_12;

            // Hitung total keseluruhan setahun
            $rkas->total_akb_setahun = $rkas->akbrincis->sum('nominal');

            return $rkas;
        });

        return view('akb.rkas', compact('dataRkas', 'tampilan', 'anggaran'));
    }

    public function generate(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return back()->with('error', 'Pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        try {
            // 2. Filter AKB Master hanya untuk anggaran yang sedang aktif
            $records = Akb::with('rkas')
                ->where('anggaran_id', $anggaran->id)
                ->get();

            if ($records->isEmpty()) {
                return back()->with('warning', 'Data AKB Master untuk anggaran ini kosong. Silakan import file AKB terlebih dahulu.');
            }

            DB::transaction(function () use ($records, $anggaran) {
                // =================================================================
                // 3. HAPUS DULU data rincian lama untuk anggaran aktif ini
                // =================================================================
                AkbRinci::where('anggaran_id', $anggaran->id)->delete();

                // =================================================================
                // 4. GENERATE BARU (Dengan optimasi Batch Insert)
                // =================================================================
                $dataToInsert = [];

                foreach ($records as $record) {
                    // Validasi relasi RKAS
                    if (! $record->rkas) {
                        continue;
                    }

                    $hargaSatuan = (float) $record->rkas->hargasatuan;
                    $pajak = (float) ($record->pajak ?? 0);

                    // Hindari pembagian dengan nol
                    if ($hargaSatuan <= 0) {
                        continue;
                    }

                    for ($i = 1; $i <= 12; $i++) {
                        $fieldBulan = "bulan$i";
                        $nominalBulan = (float) $record->$fieldBulan;

                        if ($nominalBulan > 0) {
                            $faktorPajak = ($pajak > 0) ? (1 + ($pajak / 100)) : 1;
                            $volume = $nominalBulan / ($hargaSatuan * $faktorPajak);

                            $dataToInsert[] = [
                                'akb_id' => $record->id,
                                'idblrinci' => $record->idblrinci,
                                'bulan' => $i,
                                'nominal' => $nominalBulan,
                                'volume' => $volume,
                                'anggaran_id' => $anggaran->id, // Mengikat ke ID aktif
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // --- OPTIMASI RAM: Eksekusi Insert setiap kali array mencapai 500 baris ---
                    if (count($dataToInsert) >= 500) {
                        AkbRinci::insert($dataToInsert);
                        $dataToInsert = []; // Kosongkan array kembali setelah disimpan
                    }
                }

                // Insert sisa data terakhir yang belum mencapai 500 baris
                if (! empty($dataToInsert)) {
                    AkbRinci::insert($dataToInsert);
                }
            });

            return back()->with('success', "Sukses! Rincian bulan untuk anggaran <b>{$anggaran->nama_anggaran}</b> berhasil di-generate ulang.");

        } catch (\Exception $e) {
            // Tangkap pesan error jika ada proses yang gagal (misal: kolom tabel salah)
            return back()->with('error', 'Gagal memproses generate AKB: '.$e->getMessage());
        }
    }

    // File: app/Http/Controllers/AkbController.php
    public function indexRincian(Request $request)
    {
        // Ambil input filter dari request
        $anggaranSelected = $request->input('jenis_anggaran');
        $tahunSelected = $request->input('tahun');

        $query = Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis']);

        // Filter berdasarkan Tahun (Asumsi kolom di tabel rkas adalah 'tahun')
        $query->when($tahunSelected, function ($q) use ($tahunSelected) {
            return $q->where('tahun', $tahunSelected);
        });

        // Filter berdasarkan Anggaran (Asumsi kolom 'sumber_dana' atau silakan sesuaikan)
        $query->when($anggaranSelected, function ($q) use ($anggaranSelected) {
            return $q->where('jenis_anggaran', $anggaranSelected);
        });

        // Urutkan dan Paginate (appends digunakan agar link pagination tetap membawa filter)
        $data = $query->paginate(20)->appends($request->all());

        // Ambil daftar tahun dan anggaran unik untuk isi dropdown di View
        $listTahun = Rkas::select('tahun')->distinct()->orderBy('tahun', 'desc')->get();
        $listAnggaran = Rkas::select('jenis_anggaran')->distinct()->get();

        return view('akb.rincian_index', compact('data', 'listTahun', 'listAnggaran'));
    }

    public function exportExcel(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return back()->with('error', 'Pilih anggaran aktif terlebih dahulu untuk melakukan export.');
        }

        // 2. Buat nama file yang dinamis (Contoh: Rincian_AKB_BOS_2025.xlsx)
        $namaFile = 'Rincian_AKB_'.strtoupper($anggaran->singkatan).'_'.$anggaran->tahun.'.xlsx';

        // 3. Kirim objek $anggaran ke dalam class Export agar data di dalam Excel terfilter
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RincianAkbExport($anggaran),
            $namaFile
        );
    }

    public function satuan(Request $request)
    {
        // Asumsi: $anggaran diambil dari middleware atau request
        $anggaran = $request->anggaran_data;

        $rkas = Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis'])
            ->where('anggaran_id', $anggaran->id)
            ->get();

        return view('akb.satuan', compact('rkas', 'anggaran'));
    }

    public function ringkas(Request $request)
    {
        // 1. Ambil anggaran aktif
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        // 2. Return View SAJA (Tanpa data berat)
        // Data akan diambil oleh Alpine.js via method getData() di bawah
        return view('akb.ringkas', compact('anggaran'));
    }

    /**
     * 2. Method API untuk Data JSON (Dipanggil AJAX)
     */
    public function getData(Request $request, $anggaranId)
    {
        // Query Dasar
        $query = Rkas::with([
            'akbRincis' => function ($q) {
                $q->orderBy('bulan', 'asc');
            },
            'kegiatan',
            'korek',
        ])->where('anggaran_id', $anggaranId);

        // A. Logic Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('namakomponen', 'like', "%{$search}%");
            });
        }

        // B. Logic Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validasi kolom agar tidak error SQL Injection
        $allowedSorts = ['created_at', 'namakomponen', 'hargasatuan', 'idkomponen'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        // Ambil Data
        $data = $query->get();

        // C. Formatting Data (PENTING: Agar JSON ringan & sesuai format View)
        $formattedData = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'namakomponen' => $item->namakomponen,
                'spek' => $item->spek,
                'satuan' => $item->satuan,
                'hargasatuan' => $item->hargasatuan,
                'idkomponen' => $item->idkomponen, // Pastikan field ini ada

                // Hitung total di server biar Client enteng
                'total_volume' => $item->akbRincis->sum('volume'),
                'total_pagu' => $item->akbRincis->sum('nominal'),

                // Data Relasi (Handle null dengan '??')
                'snp' => $item->kegiatan->snp ?? '-',
                'kegiatan' => Str::after($item->namasub ?? '-', ' '), // Bersihkan angka di depan nama sub
                'kode_rekening' => $item->korek->singkat ?? '-',
                'nama_rekening' => $item->korek->uraian_singkat ?? '-',

                // Rincian Lengkap (Untuk Modal)
                'rincian' => $item->akbRincis->mapWithKeys(function ($r) {
                    return [$r->bulan => [
                        'volume' => $r->volume,
                        'nominal' => $r->nominal,
                    ]];
                }),

                // Alokasi Aktif (Untuk Badge di Tabel Depan)
                'alokasi_aktif' => $item->akbRincis->filter(function ($rinci) {
                    return $rinci->nominal > 0 || $rinci->volume > 0;
                })->values()->map(function ($r) {
                    return [
                        'nama_bulan' => Carbon::create()->month($r->bulan)->translatedFormat('F'),
                        'volume' => $r->volume,
                    ];
                }),
            ];
        });

        return response()->json($formattedData);
    }

    public function updateIdKomponen(Request $request, $id)
    {
        $request->validate([
            'idkomponen' => 'required|string|max:255', // Sesuaikan validasi
        ]);

        $item = Rkas::findOrFail($id);
        $item->idkomponen = $request->idkomponen;
        $item->save();

        return response()->json(['status' => 'success', 'message' => 'Berhasil diupdate']);
    }

    /**
     * Menampilkan halaman awal komparasi dan modal upload
     */
    /**
     * Menampilkan halaman awal komparasi dan modal upload
     */
    public function indexPerbandingan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // Kirim koleksi kosong karena user belum mengupload file
        $koleksiPerbandingan = collect([]);

        // Pastikan nama file view sesuai
        return view('akb.perbandingan', compact('koleksiPerbandingan', 'anggaran'));
    }

    public function perbandingan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        $request->validate([
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',
            'jenis_json' => 'required|in:baru,lama', // Validasi pilihan dari modal
        ]);

        $jenisJson = $request->jenis_json;
        $mapAnggaran = ['bos' => 10, 'bop' => 20];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30;

        // 1. Ambil Data DB Lokal
        $dataDb = \App\Models\Akb::where('anggaran_id', $anggaran->id)->get()->keyBy('idblrinci');

        // 2. Ambil dan Gabungkan Data JSON
        $dataJsonMerged = [];
        foreach ($request->file('json_files') as $file) {
            $content = json_decode(file_get_contents($file->getRealPath()), true);
            foreach ($content['data'] ?? [] as $item) {
                $idblrinciUnik = $jenisAnggaran.$item['idblrinci'];
                $dataJsonMerged[$idblrinciUnik] = $item;
            }
        }

        // 3. Kumpulkan semua ID yang unik dari DB maupun JSON (Untuk dicek silang)
        $semuaIdUnik = collect($dataDb->keys())->merge(array_keys($dataJsonMerged))->unique();

        $hasilPerbandingan = [];

        foreach ($semuaIdUnik as $id) {
            $itemDb = $dataDb->get($id);
            $itemJson = $dataJsonMerged[$id] ?? null;

            // --- Tentukan Mana yang Lama dan Mana yang Baru ---
            if ($jenisJson == 'baru') {
                $sumberLama = 'Lokal';
                $sumberBaru = 'JSON';
                $totalLama = $itemDb ? (float) $itemDb->totalakb : 0;
                $totalBaru = $itemJson ? (float) ($itemJson['totalakb'] ?? 0) : 0;
                $namaKomponen = $itemJson['namakomponen'] ?? ($itemDb->rkas->namakomponen ?? "ID: $id");
                $koefisien = $itemJson['koefisien'] ?? ($itemDb->rkas->koefisien ?? '-');
            } else {
                // Jika user memilih JSON sebagai AKB Lama
                $sumberLama = 'JSON';
                $sumberBaru = 'Lokal';
                $totalLama = $itemJson ? (float) ($itemJson['totalakb'] ?? 0) : 0;
                $totalBaru = $itemDb ? (float) $itemDb->totalakb : 0;
                $namaKomponen = $itemDb->rkas->namakomponen ?? ($itemJson['namakomponen'] ?? "ID: $id");
                $koefisien = $itemDb->rkas->koefisien ?? ($itemJson['koefisien'] ?? '-');
            }

            // --- Cek Pergeseran Bulanan ---
            $bulanLama = [];
            $bulanBaru = [];
            $selisihBulan = [];
            $adaPergeseranBulan = false;

            for ($i = 1; $i <= 12; $i++) {
                if ($jenisJson == 'baru') {
                    $bLama = $itemDb ? (float) $itemDb->{"bulan$i"} : 0;
                    $bBaru = $itemJson ? (float) ($itemJson["bulan$i"] ?? 0) : 0;
                } else {
                    $bLama = $itemJson ? (float) ($itemJson["bulan$i"] ?? 0) : 0;
                    $bBaru = $itemDb ? (float) $itemDb->{"bulan$i"} : 0;
                }

                $bulanLama[$i] = $bLama;
                $bulanBaru[$i] = $bBaru;
                $sBulan = $bBaru - $bLama;
                $selisihBulan[$i] = $sBulan;

                if ($sBulan != 0) {
                    $adaPergeseranBulan = true;
                }
            }

            $selisihTotal = $totalBaru - $totalLama;

            // --- ATURAN PENENTUAN STATUS (Sesuai Permintaan) ---
            if ($totalLama == 0 && $totalBaru > 0) {
                $status = 'Baru';
            } elseif ($totalBaru == 0 && $totalLama > 0) {
                $status = 'Dihapus';
            } elseif ($selisihTotal != 0) {
                $status = 'Berubah Pagu';
            } elseif ($adaPergeseranBulan) {
                $status = 'Geser Jadwal';
            } else {
                $status = 'Tetap';
            }

            $hasilPerbandingan[] = [
                'idblrinci' => $id,
                'namakomponen' => $namaKomponen,
                'koefisien' => $koefisien,
                'status' => $status,
                'harga_lama' => $totalLama,
                'harga_baru' => $totalBaru,
                'selisih' => $selisihTotal,
                'bulan_lama' => $bulanLama,
                'bulan_baru' => $bulanBaru,
                'selisih_bulan' => $selisihBulan,
            ];
        }

        $koleksiPerbandingan = collect($hasilPerbandingan);

        // Kirim label tabel agar View bisa menyesuaikan
        $labelLama = $jenisJson == 'baru' ? 'Data Lokal (Lama)' : 'JSON Dinas (Lama)';
        $labelBaru = $jenisJson == 'baru' ? 'JSON Dinas (Baru)' : 'Data Lokal (Baru)';

        return view('akb.perbandingan', compact('koleksiPerbandingan', 'anggaran', 'labelLama', 'labelBaru'));
    }
}
