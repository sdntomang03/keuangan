<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompareAkbRequest;
use App\Http\Requests\ImportAkbRequest;
use App\Models\Rkas;
use App\Services\AkbService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AkbController extends Controller
{
    protected $userId;

    protected $setting_id;

    protected $akbService;

    public function __construct(AkbService $akbService)
    {
        $this->akbService = $akbService;

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

    public function import(ImportAkbRequest $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        $files = $request->file('json_files');
        $count = $this->akbService->importData($files, $anggaran, $this->setting_id);

        return back()->with('success', "Berhasil mengimpor $count baris data dari ".count($files).' file.');
    }

    public function rincian(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tampilan = $request->get('tampilan', 'bulanan');

        $dataRkas = Rkas::with([
            'kegiatan', 'korek',
            'akbrincis' => fn ($q) => $q->where('anggaran_id', $anggaran->id),
        ])
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->map(function ($rkas) {
                for ($i = 1; $i <= 12; $i++) {
                    $rkas->{"bln_$i"} = $rkas->akbrincis->where('bulan', $i)->sum('nominal');
                }
                $rkas->tw_1 = $rkas->bln_1 + $rkas->bln_2 + $rkas->bln_3;
                $rkas->tw_2 = $rkas->bln_4 + $rkas->bln_5 + $rkas->bln_6;
                $rkas->tw_3 = $rkas->bln_7 + $rkas->bln_8 + $rkas->bln_9;
                $rkas->tw_4 = $rkas->bln_10 + $rkas->bln_11 + $rkas->bln_12;
                $rkas->total_akb_setahun = $rkas->akbrincis->sum('nominal');

                return $rkas;
            });

        return view('akb.rkas', compact('dataRkas', 'tampilan', 'anggaran'));
    }

    public function generate(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        $result = $this->akbService->generateRincian($anggaran);

        return back()->with($result['status'], $result['message']);
    }

    public function indexRincian(Request $request)
    {
        $anggaranSelected = $request->input('jenis_anggaran');
        $tahunSelected = $request->input('tahun');

        $query = Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis']);

        $query->when($tahunSelected, fn ($q) => $q->where('tahun', $tahunSelected));
        $query->when($anggaranSelected, fn ($q) => $q->where('jenis_anggaran', $anggaranSelected));

        $data = $query->paginate(20)->appends($request->all());

        $listTahun = Rkas::select('tahun')->distinct()->orderBy('tahun', 'desc')->get();
        $listAnggaran = Rkas::select('jenis_anggaran')->distinct()->get();

        return view('akb.rincian_index', compact('data', 'listTahun', 'listAnggaran'));
    }

    public function exportExcel(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Pilih anggaran aktif terlebih dahulu untuk melakukan export.');
        }

        $namaFile = 'Rincian_AKB_'.strtoupper($anggaran->singkatan).'_'.$anggaran->tahun.'.xlsx';

        return Excel::download(new \App\Exports\RincianAkbExport($anggaran), $namaFile);
    }

    public function satuan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        $rkas = Rkas::with(['kegiatan', 'korek', 'akb', 'akbRincis'])
            ->where('anggaran_id', $anggaran->id)
            ->get();

        return view('akb.satuan', compact('rkas', 'anggaran'));
    }

    public function ringkas(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        return view('akb.ringkas', compact('anggaran'));
    }

    public function getData(Request $request, $anggaranId)
    {
        $formattedData = $this->akbService->getAjaxFormattedData($request, $anggaranId);

        return response()->json($formattedData);
    }

    public function updateIdKomponen(Request $request, $id)
    {
        $request->validate(['idkomponen' => 'required|string|max:255']);
        $item = Rkas::findOrFail($id);
        $item->idkomponen = $request->idkomponen;
        $item->save();

        return response()->json(['status' => 'success', 'message' => 'Berhasil diupdate']);
    }

    public function indexPerbandingan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        $koleksiPerbandingan = collect([]);

        return view('akb.perbandingan', compact('koleksiPerbandingan', 'anggaran'));
    }

    public function perbandingan(CompareAkbRequest $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        $result = $this->akbService->compareData($request->file('json_files'), $anggaran, $request->jenis_json);

        $koleksiPerbandingan = $result['koleksi'];
        $labelLama = $result['labelLama'];
        $labelBaru = $result['labelBaru'];

        return view('akb.perbandingan', compact('koleksiPerbandingan', 'anggaran', 'labelLama', 'labelBaru'));
    }
}
