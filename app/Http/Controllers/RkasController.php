<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Services\RekeningService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class RkasController extends Controller
{
    protected $rekeningService;

    protected $userId;

    public function __construct(RekeningService $rekeningService)
    {
        // 1. Inisialisasi Service (Dependency Injection)
        $this->rekeningService = $rekeningService;

        // 2. Inisialisasi User ID via Middleware Closure
        $this->middleware(function ($request, $next) {
            $this->userId = Auth::id();

            return $next($request);
        });
    }

    public function index()
    {
        return view('rkas.index');
    }

    public function import(Request $request)
    {
        // 1. Validasi bahwa yang diupload adalah array file
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

        // 2. Loop setiap file yang diupload
        foreach ($request->file('json_files') as $file) {
            $content = json_decode(file_get_contents($file), true);

            // Pastikan key "data" ada di dalam JSON
            $dataRinci = $content['data'] ?? [];

            foreach ($dataRinci as $item) {
                // 3. Simpan ke database
                // updateOrCreate mencegah duplikasi jika ID rinci sudah ada
                Rkas::updateOrCreate(
                    ['idblrinci' => $jenisAnggaran.$item['idblrinci']],
                    [
                        'user_id' => $this->userId,
                        'idbl' => $item['idbl'],
                        'idsubtitle' => $item['idsubtitle'],
                        'keterangan' => $item['keterangan'],
                        'namasub' => strip_tags($item['namasub']),
                        'kodeakun' => $this->rekeningService->getIdByKode($item['kodeakun']),
                        'namaakun' => $item['namaakun'],
                        'idkomponen' => $item['idkomponen'],
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $item['spek'],
                        'satuan' => $item['satuan'],
                        'koefisien' => $item['koefisien'],
                        'hargasatuan' => (float) $item['hargasatuan'],
                        'totalharga' => (float) $item['totalharga'],
                        'totalpajak' => (float) $item['totalpajak'],
                        'giatsubteks' => $item['giatsubteks'],
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
        // Mengambil data RKAS beserta relasi ke Master Kegiatan dan Master Akun (Korek)
        $data = Rkas::with(['kegiatan', 'korek'])->paginate(25);

        return view('rkas.rincian', compact('data'));
    }
}
