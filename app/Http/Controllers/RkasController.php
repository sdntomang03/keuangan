<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRkasRequest;
use App\Models\Rkas;
use App\Services\RkasImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class RkasController extends Controller
{
    protected $userId;

    protected $setting_id;

    public function __construct()
    {
        // Inisialisasi User ID via Middleware Closure
        $this->middleware(function ($request, $next) {
            $this->userId = Auth::id();
            $this->setting_id = auth()->user()->setting_id;

            return $next($request);
        });
    }

    public function index()
    {
        return view('rkas.index');
    }

    /**
     * FUNGSI IMPORT (Refactored menggunakan Form Request & Service)
     */
    public function import(ImportRkasRequest $request, RkasImportService $importService)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        // Delegasikan proses ke RkasImportService
        $hasil = $importService->importData($request->file('json_files'), $anggaran);

        // Jika terjadi exception (error)
        if ($hasil['status'] === 'error') {
            return back()->with('error', $hasil['message']);
        }

        // Cek jika ada log peringatan (kode rekening tidak ketemu)
        if (count($hasil['logs']) > 0) {
            $maxDisplay = 5;
            $logList = array_slice($hasil['logs'], 0, $maxDisplay);
            $sisaLog = count($hasil['logs']) - $maxDisplay;

            $pesanWarning = "Berhasil mengimpor <b>{$hasil['count']} baris</b>. Terdapat beberapa catatan relasi:<br><ul class='list-disc pl-5 mt-2 text-sm'>";
            foreach ($logList as $log) {
                $pesanWarning .= "<li>{$log}</li>";
            }
            if ($sisaLog > 0) {
                $pesanWarning .= "<li><i>...dan {$sisaLog} baris lainnya mengalami hal serupa.</i></li>";
            }
            $pesanWarning .= '</ul>';

            return back()->with('warning', $pesanWarning);
        }

        // Jika sukses mulus 100%
        return back()->with('success', "Sukses! {$hasil['count']} baris RKAS berhasil disinkronkan.");
    }

    public function rincian(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tampilan = $request->get('tampilan', 'bulanan');

        // Logika query ASLI milik Anda (Tetap Dipertahankan)
        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->withSum(['akbrincis as total_akb_setahun' => function ($q) use ($anggaran) {
                $q->where('anggaran_id', $anggaran->id);
            }], 'nominal')
            ->withSum(['akbrincis as bln_1' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 1)], 'nominal')
            ->withSum(['akbrincis as bln_2' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 2)], 'nominal')
            ->withSum(['akbrincis as bln_3' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 3)], 'nominal')
            ->withSum(['akbrincis as bln_4' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 4)], 'nominal')
            ->withSum(['akbrincis as bln_5' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 5)], 'nominal')
            ->withSum(['akbrincis as bln_6' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 6)], 'nominal')
            ->withSum(['akbrincis as bln_7' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 7)], 'nominal')
            ->withSum(['akbrincis as bln_8' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 8)], 'nominal')
            ->withSum(['akbrincis as bln_9' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 9)], 'nominal')
            ->withSum(['akbrincis as bln_10' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 10)], 'nominal')
            ->withSum(['akbrincis as bln_11' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 11)], 'nominal')
            ->withSum(['akbrincis as bln_12' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->where('bulan', 12)], 'nominal')
            ->get();

        return view('akb.rkas', compact('dataRkas', 'tampilan', 'anggaran'));
    }

    public function anggaran(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tampilan = $request->get('tampilan', 'bulanan');

        $dataRkas = Rkas::with([
            'akbrincis' => function ($query) use ($anggaran) {
                $query->where('anggaran_id', $anggaran->id);
            },
            'kegiatan',
            'korek',
        ])
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->groupBy(['idbl', 'kodeakun']);

        return view('rkas.anggaran', compact('dataRkas', 'tampilan', 'anggaran'));
    }

    public function updateIdKomponen(Request $request, $id)
    {
        $request->validate([
            'idkomponen' => 'required|string|max:100',
        ]);

        $rkas = Rkas::findOrFail($id);

        if ($rkas->idkomponen == $request->idkomponen) {
            return back()->with('warning', 'Tidak ada perubahan. ID Komponen yang dimasukkan sama dengan sebelumnya.');
        }

        $oldId = $rkas->idkomponen;

        try {
            $rkas->idkomponen = $request->idkomponen;
            $rkas->save();

            return back()->with('success', "Berhasil mengubah ID Komponen dari $oldId menjadi {$request->idkomponen}.");

        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->with('error', "Gagal update! ID Komponen {$request->idkomponen} sudah digunakan.");
            }

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function rekap(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tw = $request->get('tw', 'tahun');
        $periodeText = $tw !== 'tahun' ? "Triwulan {$tw}" : 'Tahunan';

        // Logika query ASLI milik Anda (Tetap Dipertahankan)
        $dataRkasUtuh = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->withSum(['akbrincis as tw1_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [1, 2, 3])], 'nominal')
            ->withSum(['akbrincis as tw2_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [4, 5, 6])], 'nominal')
            ->withSum(['akbrincis as tw3_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [7, 8, 9])], 'nominal')
            ->withSum(['akbrincis as tw4_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [10, 11, 12])], 'nominal')
            ->get();

        $rekapRekeningSemuaTw = $dataRkasUtuh->groupBy('kodeakun')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->kodeakun,
                'uraian' => $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                'tw1' => $items->sum('tw1_sum'),
                'tw2' => $items->sum('tw2_sum'),
                'tw3' => $items->sum('tw3_sum'),
                'tw4' => $items->sum('tw4_sum'),
                'total_anggaran' => $items->sum('tw1_sum') + $items->sum('tw2_sum') + $items->sum('tw3_sum') + $items->sum('tw4_sum'),
            ];
        })->sortBy('kode');

        $dataRkasFiltered = $dataRkasUtuh->map(function ($item) use ($tw) {
            $item->anggaran_aktif = match ((string) $tw) {
                '1' => $item->tw1_sum ?? 0,
                '2' => $item->tw2_sum ?? 0,
                '3' => $item->tw3_sum ?? 0,
                '4' => $item->tw4_sum ?? 0,
                default => ($item->tw1_sum + $item->tw2_sum + $item->tw3_sum + $item->tw4_sum),
            };

            return $item;
        })->filter(function ($item) {
            return $item->anggaran_aktif > 0;
        });

        $rekapKegiatan = $dataRkasFiltered->groupBy('idbl')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->idbl,
                'uraian' => $items->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi',
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortByDesc('total_anggaran');

        $rekapRekening = $dataRkasFiltered->groupBy('kodeakun')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->kodeakun,
                'uraian' => $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('kode');

        $rekapSnp = $dataRkasFiltered->groupBy(function ($item) {
            return $item->kegiatan->snp ?? 'BELUM DIATUR';
        })->map(function ($items, $snp) {
            return (object) [
                'uraian' => $snp,
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('uraian');

        $grandTotalAnggaran = $dataRkasFiltered->sum('anggaran_aktif');

        return view('rkas.rekap', compact(
            'anggaran', 'rekapKegiatan', 'rekapRekening', 'rekapSnp',
            'grandTotalAnggaran', 'rekapRekeningSemuaTw', 'tw', 'periodeText'
        ));
    }

    public function cetakLaporan(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        // Logika query ASLI milik Anda (Tetap Dipertahankan)
        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->orderBy('idbl')
            ->orderBy('keterangan')
            ->orderBy('kodeakun')
            ->get();

        $laporan = $dataRkas->groupBy('idbl')->map(function ($itemsByKegiatan) {
            return (object) [
                'kode_kegiatan' => $itemsByKegiatan->first()->idbl,
                'nama_kegiatan' => $itemsByKegiatan->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi',
                'total_kegiatan' => $itemsByKegiatan->sum('totalharga'),

                'keterangan_list' => $itemsByKegiatan->groupBy('keterangan')->map(function ($itemsByKeterangan, $namaKeterangan) {
                    return (object) [
                        'nama_keterangan' => $namaKeterangan ?: 'Tanpa Keterangan',
                        'total_keterangan' => $itemsByKeterangan->sum('totalharga'),

                        'rekening' => $itemsByKeterangan->groupBy('kodeakun')->map(function ($itemsByRekening) {
                            return (object) [
                                'kode_rekening' => $itemsByRekening->first()->kodeakun,
                                'nama_rekening' => $itemsByRekening->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                                'total_rekening' => $itemsByRekening->sum('totalharga'),
                                'komponen' => $itemsByRekening,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        $grandTotal = $dataRkas->sum('totalharga');

        return view('rkas.cetak_laporan', compact('anggaran', 'laporan', 'grandTotal'));
    }
}
