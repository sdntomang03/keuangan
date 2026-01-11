<?php

namespace App\Http\Controllers;

use App\Exports\RincianAkbExport;
use App\Models\Akb;
use App\Models\AkbRinci;
use App\Models\Rkas;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AkbController extends Controller
{
    protected $userId;

    public function __construct()
    {
        // Gunakan middleware closure untuk menangkap User ID setelah login tervalidasi
        $this->middleware(function ($request, $next) {
            $this->userId = Auth::id();

            return $next($request);
        });
    }

    public function index()
    {
        return view('akb.index');
    }

    public function import(Request $request)
    {

        $request->validate([
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',
            'jenis_anggaran' => 'required',
            'tahun' => 'required',
        ]);
        $mapAnggaran = [
            'bos' => 10,
            'bop' => 20,
        ];
        $jenisAnggaran = $mapAnggaran[$request->jenis_anggaran] ?? 0;
        $tahunAnggaran = $request->tahun;
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
                        'user_id' => $this->userId,
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
                        'jenis_anggaran' => $request->jenis_anggaran,
                        'tahun' => $tahunAnggaran,
                        'created_at' => now(),
                        'updated' => now(),
                    ]
                );
                $count++;
            }
        }

        return back()->with('success', "Berhasil mengimpor $count baris data dari ".count($request->file('json_files')).' file.');

    }

    public function rincian()
    {
        // Eager loading semua relasi: kegiatan (idbl), akb (idblrinci), korek (kodeakun)
        $data = Rkas::with(['kegiatan', 'akb', 'korek'])->paginate(20);

        return view('akb.rincian', compact('data'));
    }

    public function generate()
    {
        // Mengambil data AKB Master beserta data RKAS (untuk ambil harga & idbl)
        $records = Akb::with('rkas')->get();

        DB::transaction(function () use ($records) {
            // Kosongkan tabel rincian sebelum isi ulang
            AkbRinci::truncate();

            foreach ($records as $record) {
                // Validasi: Jika data induk RKAS tidak ditemukan, lewati baris ini
                if (! $record->rkas) {
                    continue;
                }

                $dataToInsert = [];
                $hargaSatuan = (float) $record->rkas->hargasatuan;
                $pajak = (float) ($record->pajak ?? 0);

                // Jika harga nol, tidak bisa dibagi (menghindari division by zero)
                if ($hargaSatuan <= 0) {
                    continue;
                }

                for ($i = 1; $i <= 12; $i++) {
                    $fieldBulan = "bulan$i";
                    $nominalBulan = (float) $record->$fieldBulan;

                    if ($nominalBulan > 0) {
                        // Kalkulasi Volume: Nominal / (Harga * (1 + Pajak%))
                        $faktorPajak = ($pajak > 0) ? (1 + ($pajak / 100)) : 1;
                        $volume = $nominalBulan / ($hargaSatuan * $faktorPajak);

                        $dataToInsert[] = [

                            'akb_id' => $record->id,
                            'idblrinci' => $record->idblrinci,
                            'bulan' => $i,
                            'nominal' => $nominalBulan,
                            'volume' => $volume,
                            'tahun' => $record->tahun ?? 2026,
                            'jenis_anggaran' => $record->jenis_anggaran,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Simpan per kelompok record untuk efisiensi
                if (! empty($dataToInsert)) {
                    AkbRinci::insert($dataToInsert);
                }
            }
        });

        return back()->with('success', 'Rincian volume bulanan berhasil di-generate!');
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
        $namaFile = 'Rincian_Anggaran_'.($request->tahun ?? 'Semua').'.xlsx';

        return Excel::download(new RincianAkbExport($request), $namaFile);
    }
}
