<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Exports\RealisasiKomponenExport;
use App\Exports\RekananMultipleSheetExport;
use App\Exports\SemuaRekananExport;
use App\Models\Belanja;
use App\Models\DasarPajak;
use App\Models\Rekanan;
use App\Models\Sekolah;
use App\Services\RealisasiService;
use App\Traits\FinancialContextTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RealisasiController extends Controller
{
    use FinancialContextTrait;

    protected RealisasiService $realisasiService;

    public function __construct(RealisasiService $realisasiService)
    {
        $this->realisasiService = $realisasiService;
    }

    public function komponen(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        $sekolah = auth()->user()->sekolah;
        $periode = $request->get('periode', ['tahun']);

        // Parsing periode lewat Trait
        $parsed = $this->parsePeriodeFilter($periode);

        // Ambil data lewat Service
        $dataRkas = $this->realisasiService->getRekapRkas($anggaran->id, $parsed['bulanArray'])
            ->groupBy(['idbl', 'kodeakun']);

        $periodeText = $parsed['periodeText'];

        return view('realisasi.komponen', compact('dataRkas', 'anggaran', 'sekolah', 'periode', 'periodeText'));
    }

    public function korek(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        $sekolah = auth()->user()->sekolah;
        $tw = $request->get('tw', 'tahun');

        $bulanArray = $tw === 'tahun' ? null : $this->getBulanDariTw($tw);
        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;

        // Ambil data lewat Service (aktifkan flag multiplier PPN)
        $dataRkas = $this->realisasiService->getRekapRkas($anggaran->id, $bulanArray, true, $persenPpn)
            ->groupBy(['idbl', 'kodeakun']);

        return view('realisasi.korek', compact('dataRkas', 'anggaran', 'tw', 'persenPpn', 'sekolah'));
    }

    public function jenisBelanja(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        $sekolah = auth()->user()->sekolah;
        $periode = $request->get('periode', 'tahun');

        // Parsing periode lewat Trait
        $parsed = $this->parsePeriodeFilter([$periode]);
        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;

        // Ambil data lewat Service (aktifkan flag multiplier PPN)
        $rawData = $this->realisasiService->getRekapRkas($anggaran->id, $parsed['bulanArray'], true, $persenPpn);

        $dataRkas = $rawData->groupBy([
            fn ($item) => strtoupper($item->korek->jenis_belanja ?? 'BELUM DIATUR'),
            'kodeakun',
        ]);

        $grandTotalAnggaran = $rawData->sum('total_anggaran');
        $grandTotalRealisasi = $rawData->sum('total_realisasi');
        $grandTotalSisa = $grandTotalAnggaran - $grandTotalRealisasi;
        $grandPersen = $grandTotalAnggaran > 0 ? ($grandTotalRealisasi / $grandTotalAnggaran) * 100 : 0;

        $periodeText = $parsed['periodeText'];

        return view('realisasi.jenis_belanja', compact(
            'dataRkas', 'anggaran', 'sekolah', 'periode', 'periodeText',
            'grandTotalAnggaran', 'grandTotalRealisasi', 'grandTotalSisa', 'grandPersen'
        ));
    }

    public function exportKomponen(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        $sekolah = auth()->user()->sekolah;

        // Parsing periode lewat Trait
        $parsed = $this->parsePeriodeFilter($request->get('periode', ['tahun']));

        // Ambil data lewat Service
        $dataRkas = $this->realisasiService->getRekapRkas($anggaran->id, $parsed['bulanArray'])
            ->groupBy(['idbl', 'keterangan']);

        $namaFile = 'Realisasi_Komponen_'.str_replace([' ', ','], ['_', ''], $parsed['periodeText']).'_'.date('Ymd_His').'.xlsx';

        return Excel::download(new RealisasiKomponenExport($dataRkas, $anggaran, $sekolah, $parsed['periodeText']), $namaFile);
    }

    // ============================================================================
    // Metode Export dan SPJ lainnya tetap menggunakan kueri Belanja dan Rekanan
    // (Bisa dibiarkan seperti asli Anda karena sudah spesifik untuk rekanan/SPJ)
    // ============================================================================

    public function exportExcel(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $dataBelanja = Belanja::with(['rekanan', 'rincis.rkas.kegiatan', 'rincis.rkas.korek'])
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $sekolah->triwulan_aktif)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_bukti', 'asc')
            ->get();

        $fileName = 'Laporan_Rincian_Belanja_'.strtoupper($anggaran->singkatan).'_'.date('YmdHis').'.xlsx';

        return Excel::download(new BelanjaExport($dataBelanja), $fileName);
    }

    public function rekapPerRekanan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        $dataRekap = Belanja::with('rekanan')
            ->where('anggaran_id', $anggaran->id)
            ->whereNotNull('rekanan_id')
            ->select('rekanan_id')
            ->selectRaw('
                SUM(CASE WHEN tw = 1 THEN (subtotal + ppn) ELSE 0 END) as tw1,
                SUM(CASE WHEN tw = 2 THEN (subtotal + ppn) ELSE 0 END) as tw2,
                SUM(CASE WHEN tw = 3 THEN (subtotal + ppn) ELSE 0 END) as tw3,
                SUM(CASE WHEN tw = 4 THEN (subtotal + ppn) ELSE 0 END) as tw4,
                SUM(subtotal + ppn) as total_setahun
            ')
            ->groupBy('rekanan_id')
            ->get();

        $grandTotal = [
            'tw1' => $dataRekap->sum('tw1'),
            'tw2' => $dataRekap->sum('tw2'),
            'tw3' => $dataRekap->sum('tw3'),
            'tw4' => $dataRekap->sum('tw4'),
            'total' => $dataRekap->sum('total_setahun'),
        ];

        $user = Auth::user();
        $sekolah = $user->sekolah ?? Sekolah::find($user->sekolah_id);

        return view('realisasi.rekanan', compact('dataRekap', 'grandTotal', 'anggaran', 'sekolah'));
    }

    public function exportDetailRekanan(Request $request, $id)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        $tw = $request->query('tw', 'semua');
        $rekanan = Rekanan::findOrFail($id);

        $dataBelanja = Belanja::with(['rekanan', 'rincis.rkas.kegiatan', 'rincis.rkas.korek', 'pajaks.masterPajak'])
            ->where('anggaran_id', $anggaran->id)
            ->where('rekanan_id', $id)
            ->when($tw !== 'semua', fn ($q) => $q->where('tw', $tw))
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_bukti', 'asc')
            ->get();

        if ($dataBelanja->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi pada pilihan TW ini.');
        }

        $cleanName = preg_replace('/[^A-Za-z0-9]/', '_', $rekanan->nama_rekanan);
        $twText = $tw === 'semua' ? 'SEMUA_TW' : 'TW_'.$tw;
        $fileName = 'URK_Belanja_'.strtoupper($cleanName).'_'.$twText.'.xlsx';

        return Excel::download(new RekananMultipleSheetExport($dataBelanja, $rekanan), $fileName);
    }

    public function exportSemuaRekanan(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        $tw = $request->query('tw', 'semua');

        $daftarRekanan = Rekanan::whereHas('belanjas', function ($q) use ($anggaran, $tw) {
            $q->where('anggaran_id', $anggaran->id);
            if ($tw !== 'semua') {
                $q->where('tw', $tw);
            }
        })
            ->with(['belanjas' => function ($q) use ($anggaran, $tw) {
                $q->where('anggaran_id', $anggaran->id);
                if ($tw !== 'semua') {
                    $q->where('tw', $tw);
                }
                $q->with(['rekanan', 'korek', 'surats.rincis.rkas', 'rincis.rkas.kegiatan', 'rincis.rkas.korek', 'pajaks.masterPajak'])
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('no_bukti', 'asc');
            }])
            ->get();

        if ($daftarRekanan->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi pada pilihan TW ini.');
        }

        $twText = $tw === 'semua' ? 'SEMUA_TW' : 'TW_'.$tw;
        $fileName = 'SELURUH_URK_REKANAN_'.$twText.'.xlsx';

        return Excel::download(new SemuaRekananExport($daftarRekanan), $fileName);
    }

    private function siapkanDataSpj($anggaran, $sekolah)
    {
        $dataBelanja = Belanja::with(['rekanan', 'pajaks.masterPajak', 'rincis.rkas.korek'])
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $sekolah->triwulan_aktif)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_bukti', 'asc')
            ->get();

        $pajakUnik = [];
        foreach ($dataBelanja as $belanja) {
            foreach ($belanja->pajaks as $pajak) {
                $namaPajak = $pajak->masterPajak->nama_pajak ?? 'Pajak Lainnya';
                if (! in_array($namaPajak, $pajakUnik)) {
                    $pajakUnik[] = $namaPajak;
                }
            }
        }
        sort($pajakUnik);

        $mappedData = [];
        $totals = ['bruto' => 0, 'pajak' => array_fill_keys($pajakUnik, 0), 'netto' => 0];

        foreach ($dataBelanja as $belanja) {
            $bruto = $belanja->subtotal + $belanja->ppn;
            $rowPajak = [];
            $totalPotongan = 0;

            foreach ($pajakUnik as $namaPajak) {
                $nominal = 0;
                foreach ($belanja->pajaks as $pajak) {
                    if (($pajak->masterPajak->nama_pajak ?? 'Pajak Lainnya') === $namaPajak) {
                        $nominal += $pajak->nominal;
                    }
                }
                $rowPajak[$namaPajak] = $nominal;
                $totals['pajak'][$namaPajak] += $nominal;
                $totalPotongan += $nominal;
            }

            $netto = $bruto - $totalPotongan;
            $totals['bruto'] += $bruto;
            $totals['netto'] += $netto;

            $jenisKorek = '-';
            if ($belanja->rincis->isNotEmpty() && $belanja->rincis->first()->rkas && $belanja->rincis->first()->rkas->korek) {
                $jenisKorek = $belanja->rincis->first()->rkas->korek->singkat ?? '-';
            }

            $mappedData[] = [
                'tanggal' => $belanja->tanggal,
                'no_bukti' => $belanja->no_bukti,
                'korek' => $jenisKorek,
                'rekanan' => $belanja->rekanan->nama_rekanan ?? '-',
                'uraian' => $belanja->uraian ?? '-',
                'bruto' => $bruto,
                'pajak' => $rowPajak,
                'netto' => $netto,
            ];
        }
        $mappedData = collect($mappedData)->sortBy([['korek', 'asc'], ['tanggal', 'asc']])->values()->all();

        return compact('mappedData', 'pajakUnik', 'totals', 'anggaran', 'sekolah');
    }

    public function viewLaporanSpj(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $data = $this->siapkanDataSpj($anggaran, $sekolah);

        return view('realisasi.laporan_spj', $data);
    }

    public function pdfLaporanSpj(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $data = $this->siapkanDataSpj($anggaran, $sekolah);
        $data['isPdf'] = true;

        $kertasF4 = [0, 0, 595.28, 935.43];

        $pdf = Pdf::loadView('realisasi.laporan_spj_pdf', $data)->setPaper($kertasF4, 'landscape');

        $fileName = 'Laporan_SPJ_'.strtoupper($anggaran->singkatan).'_TW'.$sekolah->triwulan_aktif.'.pdf';

        return $pdf->download($fileName);
    }
}
