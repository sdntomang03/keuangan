<?php

namespace App\Http\Controllers;

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
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        // Proteksi jika user belum memilih anggaran aktif
        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        // 2. Ambil parameter tampilan (tetap dipertahankan jika user ingin ganti view)
        $tampilan = $request->get('tampilan', 'bulanan');

        // 3. Query RKAS berdasarkan anggaran_id yang sedang aktif
        // Kita tidak perlu lagi menggunakan $tahun dan $jenis karena sudah terwakili oleh $anggaran->id
        $dataRkas = Rkas::with([
            'akbrincis',
            'kegiatan',
            'korek',
        ])
            ->where('anggaran_id', $anggaran->id)
            ->get();

        // 4. Kirim data ke view beserta informasi anggaran aktifnya
        return view('akb.rkas', compact('dataRkas', 'tampilan', 'anggaran'));
    }

    public function generate(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return back()->with('error', 'Pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        // 2. Filter AKB Master hanya untuk anggaran yang sedang aktif
        // Penting agar tidak mengolah data tahun/sumber dana lain
        $records = Akb::with('rkas')
            ->where('anggaran_id', $anggaran->id)
            ->get();

        if ($records->isEmpty()) {
            return back()->with('error', 'Data AKB Master untuk anggaran ini kosong.');
        }

        DB::transaction(function () use ($records, $anggaran) {
            // 3. JANGAN gunakan truncate(). Gunakan delete() dengan filter anggaran_id.
            // Truncate akan menghapus SELURUH data di tabel, termasuk milik sekolah/anggaran lain.
            AkbRinci::where('anggaran_id', $anggaran->id)->delete();

            foreach ($records as $record) {
                // Validasi relasi RKAS
                if (! $record->rkas) {
                    continue;
                }

                $dataToInsert = [];
                $hargaSatuan = (float) $record->rkas->hargasatuan;
                $pajak = (float) ($record->pajak ?? 0);

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
                            'anggaran_id' => $anggaran->id, // Set ke ID aktif
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Simpan per baris AKB (isi 12 bulan sekaligus)
                if (! empty($dataToInsert)) {
                    AkbRinci::insert($dataToInsert);
                }
            }
        });

        return back()->with('success', "Rincian bulanan untuk anggaran {$anggaran->singkatan} berhasil di-generate!");
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
}
