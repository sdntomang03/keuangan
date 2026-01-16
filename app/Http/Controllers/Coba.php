<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Services\RkasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Coba extends Controller
{
    protected $rkasService;

    /**
     * Inject RkasService ke dalam controller
     */
    public function __construct(RkasService $rkasService)
    {
        $this->rkasService = $rkasService;
    }

    public function index(Request $request)
    {
        $tw = $request->get('tw', 1);
        $results = $this->rkasService->getPivotComparison($tw);

        $anggaranAktif = DB::table('settings')->where('key', 'anggaran_aktif')->value('value') ?? 'BOS';

        return view('coba.index', compact('results', 'tw', 'anggaranAktif'));
    }

    public function banding()
    {
        // Mengambil setting anggaran aktif
        $anggaranAktif = DB::table('settings')->where('key', 'anggaran_aktif')->value('value') ?? 'BOS';

        // Mengambil data dari service khusus tahunan
        $results = $this->rkasService->getFullYearComparison();

        return view('coba.banding', compact('results', 'anggaranAktif'));
    }

    public function rkas(Request $request)
    {
        $tahun = $request->get('tahun');
        $jenis = $request->get('jenis_anggaran');
        $tampilan = $request->get('tampilan', 'bulanan'); // Default ke bulanan

        $dataRkas = Rkas::with(['akbrincis', 'kegiatan'])
            ->when($tahun, fn ($q) => $q->where('tahun', $tahun))
            ->when($jenis, fn ($q) => $q->where('jenis_anggaran', $jenis))
            ->get();

        return view('coba.rkas', compact('dataRkas', 'tampilan'));
    }

    public function anggaran(Request $request)
    {
        $tahun = $request->get('tahun', '2026');
        $jenis = $request->get('jenis_anggaran');
        $tampilan = $request->get('tampilan', 'bulanan');

        $dataRkas = Rkas::with(['akbrincis', 'kegiatan', 'korek'])
            ->when($tahun, fn ($q) => $q->where('tahun', $tahun))
            ->when($jenis, fn ($q) => $q->where('jenis_anggaran', $jenis))
            ->get()
            ->groupBy(['idbl', 'kodeakun']); // Tetap kelompokkan agar mudah diloop

        return view('coba.anggaran', compact('dataRkas', 'tampilan'));
    }
}
