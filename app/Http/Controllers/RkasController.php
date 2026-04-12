<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Services\RekeningService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RkasController extends Controller
{
    protected $rekeningService;

    protected $userId;

    protected $setting_id;

    public function __construct(RekeningService $rekeningService)
    {
        // 1. Inisialisasi Service (Dependency Injection)
        $this->rekeningService = $rekeningService;

        // 2. Inisialisasi User ID via Middleware Closure
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

    public function import(Request $request)
    {
        // 1. VALIDASI AWAL
        $request->validate([
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',
        ]);

        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        $mapAnggaran = ['bos' => 10, 'bop' => 20];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30;

        $countSukses = 0;
        $processedIds = [];
        $errorLogs = []; // Untuk mencatat peringatan (bukan memblokir)

        DB::beginTransaction();
        try {
            // 2. PROSES BACA FILE JSON
            foreach ($request->file('json_files') as $index => $file) {
                // Menggunakan getRealPath() agar lebih aman membaca file temporary di server
                $content = json_decode(file_get_contents($file->getRealPath()), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('File ke-'.($index + 1).' gagal dibaca. Pastikan format file adalah JSON.');
                }

                // Ekstrak fallback IDBL dari queries
                $fallbackIdbl = '000000';
                if (isset($content['queries']) && is_array($content['queries'])) {
                    foreach ($content['queries'] as $queryObj) {
                        if (isset($queryObj['bindings']) && count($queryObj['bindings']) >= 4) {
                            $fallbackIdbl = (string) $queryObj['bindings'][3];
                            break;
                        }
                    }
                }

                $dataRinci = isset($content['data']) ? $content['data'] : [];

                if (empty($dataRinci)) {
                    throw new \Exception('File ke-'.($index + 1).' tidak memiliki data rincian komponen.');
                }

                // 3. LOOPING DATA RINCIAN
                foreach ($dataRinci as $rowNum => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $barisFisik = $rowNum + 1;
                    $namaKompLog = $item['namakomponen'] ?? 'Komponen Tanpa Nama';

                    // Cek ketersediaan idblrinci (wajib)
                    $hasIdblrinci = array_key_exists('idblrinci', $item) && ! empty($item['idblrinci']);
                    if (! $hasIdblrinci) {
                        continue; // Lewati hanya jika benar-benar tidak ada ID utama
                    }

                    $uniqueId = $jenisAnggaran.$item['idblrinci'];
                    $processedIds[] = $uniqueId;

                    $finalIdbl = isset($item['idbl']) && ! empty($item['idbl'])
                                 ? (string) $item['idbl']
                                 : $fallbackIdbl;

                    // =========================================================
                    // PERBAIKAN: JANGAN BLOKIR JIKA REKENING TIDAK KETEMU
                    // =========================================================
                    $kodeAkunJson = trim((string) ($item['kodeakun'] ?? ''));
                    $korekId = $this->rekeningService->getIdByKode($kodeAkunJson);

                    // Jika tidak ketemu, kita beri NULL dan catat peringatannya, TAPI PROSES TETAP LANJUT (Tidak di-continue)
                    if (! $korekId && $kodeAkunJson !== '') {
                        $errorLogs[] = "[Baris {$barisFisik}] Peringatan: Kode '{$kodeAkunJson}' belum ada di Master Rekening. Data tetap disimpan, namun relasi rekening dikosongkan.";
                    }

                    // --- ATURAN EMAS 1: Proteksi Pagu vs Realisasi ---
                    $rkasLama = Rkas::where('idblrinci', $uniqueId)->where('anggaran_id', $anggaran->id)->first();

                    if ($rkasLama) {
                        $totalRealisasi = $rkasLama->belanjaRincis()
                            ->join('belanjas', 'belanjas.id', '=', 'belanja_rincis.belanja_id')
                            ->sum(DB::raw('
                                CASE WHEN belanjas.ppn > 0 THEN (belanja_rincis.volume * belanja_rincis.harga_satuan * 1.11)
                                ELSE (belanja_rincis.volume * belanja_rincis.harga_satuan) END
                            '));

                        $paguBaru = (float) ($item['totalharga'] ?? 0);
                        if ($paguBaru < $totalRealisasi) {
                            throw new \Exception("GAGAL: Pagu komponen '{$namaKompLog}' diturunkan (Rp ".number_format($paguBaru, 0, ',', '.').') padahal realisasi sudah (Rp '.number_format($totalRealisasi, 0, ',', '.').').');
                        }
                    }

                    // --- EKSEKUSI SIMPAN ---
                    Rkas::updateOrCreate(
                        ['idblrinci' => $uniqueId],
                        [
                            'idbl' => $finalIdbl,
                            'idsubtitle' => (string) ($item['idsubtitle'] ?? ''),
                            'keterangan' => (string) ($item['keterangan'] ?? ''),
                            'namasub' => isset($item['namasub']) ? strip_tags((string) $item['namasub']) : '',

                            // Menyimpan Relasi ID Korek (Bisa bernilai angka ID, atau NULL jika tidak ketemu)
                            'kodeakun' => $korekId,

                            'namaakun' => (string) ($item['namaakun'] ?? ''),
                            'idkomponen' => (string) ($item['idkomponen'] ?? ''),
                            'namakomponen' => (string) ($item['namakomponen'] ?? ''),
                            'spek' => (string) ($item['spek'] ?? ''),
                            'satuan' => (string) ($item['satuan'] ?? ''),
                            'koefisien' => (string) ($item['koefisien'] ?? ''),
                            'hargasatuan' => (float) ($item['hargasatuan'] ?? 0),
                            'totalharga' => (float) ($item['totalharga'] ?? 0),
                            'totalpajak' => (float) ($item['totalpajak'] ?? 0),
                            'giatsubteks' => (string) ($item['giatsubteks'] ?? ''),
                            'anggaran_id' => $anggaran->id,
                        ]
                    );
                    $countSukses++;
                }
            }

            // 4. ATURAN EMAS 2: PROTEKSI HAPUS SINKRONISASI
            if (count($processedIds) > 0) {
                $rkasDihapus = Rkas::where('anggaran_id', $anggaran->id)
                    ->whereNotIn('idblrinci', $processedIds)
                    ->get();

                foreach ($rkasDihapus as $rkas) {
                    if ($rkas->belanjaRincis()->exists()) {
                        throw new \Exception("SINKRONISASI DITOLAK: Komponen '{$rkas->namakomponen}' terhapus dari file JSON, tapi sudah ada riwayat belanja di sistem.");
                    }
                    $rkas->delete();
                }
            }

            DB::commit();

            // 5. RESPON HASIL IMPORT
            if (count($errorLogs) > 0) {
                $maxDisplay = 5;
                $logList = array_slice($errorLogs, 0, $maxDisplay);
                $sisaLog = count($errorLogs) - $maxDisplay;

                $pesanWarning = "Berhasil mengimpor <b>{$countSukses} baris</b>. Terdapat beberapa catatan relasi:<br><ul class='list-disc pl-5 mt-2 text-sm'>";
                foreach ($logList as $log) {
                    $pesanWarning .= "<li>{$log}</li>";
                }
                if ($sisaLog > 0) {
                    $pesanWarning .= "<li><i>...dan {$sisaLog} baris lainnya mengalami hal serupa.</i></li>";
                }
                $pesanWarning .= '</ul>';

                return back()->with('warning', $pesanWarning);
            }

            return back()->with('success', "Sukses! {$countSukses} baris RKAS berhasil disinkronkan.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
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

        // 2. Filter data RKAS berdasarkan anggaran_id yang sedang aktif
        $data = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->orderBy('idbl', 'asc') // Opsional: mengurutkan agar lebih rapi
            ->paginate(25);

        return view('rkas.rincian', compact('data', 'anggaran'));
    }

    public function anggaran(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        // Proteksi jika anggaran aktif tidak ditemukan
        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $tampilan = $request->get('tampilan', 'bulanan');

        // 2. Query berdasarkan anggaran_id yang aktif
        $dataRkas = Rkas::with(['akbrincis', 'kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id) // Kunci pada anggaran yang sedang aktif
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

        // --- PERBAIKAN: Cek apakah ada perubahan? ---
        if ($rkas->idkomponen == $request->idkomponen) {
            return back()->with('warning', 'Tidak ada perubahan. ID Komponen yang dimasukkan sama dengan sebelumnya.');
        }
        // --------------------------------------------

        // 1. Ambil ID Lama (Pastikan baris ini SEBELUM pengubahan data)
        $oldId = $rkas->idkomponen;

        try {
            // 2. Ubah dengan ID Baru
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

    /**
     * Menampilkan Rekapitulasi Total berdasarkan Kegiatan dan Kode Rekening
     */
    public function rekap(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        // 1. Logika Filter Triwulan
        $tw = $request->get('tw', 'tahun');
        $periodeText = $tw !== 'tahun' ? "Triwulan {$tw}" : 'Tahunan';

        // =================================================================
        // 2. TARIK SEMUA DATA RKAS (Sekaligus hitung sum untuk tiap TW)
        // =================================================================
        $dataRkasUtuh = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            // --- PERBAIKAN: Kunci setiap sum dengan anggaran_id ---
            ->withSum(['akbrincis as tw1_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [1, 2, 3])], 'nominal')
            ->withSum(['akbrincis as tw2_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [4, 5, 6])], 'nominal')
            ->withSum(['akbrincis as tw3_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [7, 8, 9])], 'nominal')
            ->withSum(['akbrincis as tw4_sum' => fn ($q) => $q->where('anggaran_id', $anggaran->id)->whereIn('bulan', [10, 11, 12])], 'nominal')
            // ------------------------------------------------------
            ->get();

        // =================================================================
        // 3. REKAP BARU: KODE REKENING UNTUK SEMUA TW (1 TAHUN PENUH)
        // (Dihitung sebelum data difilter)
        // =================================================================
        $rekapRekeningSemuaTw = $dataRkasUtuh->groupBy('kodeakun')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->kodeakun,
                'uraian' => $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                'tw1' => $items->sum('tw1_sum'),
                'tw2' => $items->sum('tw2_sum'),
                'tw3' => $items->sum('tw3_sum'),
                'tw4' => $items->sum('tw4_sum'),
                // Jika ingin total_anggaran ini juga dinamis dari AKB setahun penuh (bukan pagu rkas):
                'total_anggaran' => $items->sum('tw1_sum') + $items->sum('tw2_sum') + $items->sum('tw3_sum') + $items->sum('tw4_sum'),
            ];
        })->sortBy('kode');

        // =================================================================
        // 4. FILTER DATA UNTUK TABEL SPESIFIK (SNP, Kegiatan, Rekening TW)
        // =================================================================
        $dataRkasFiltered = $dataRkasUtuh->map(function ($item) use ($tw) {
            // Tentukan pagu sesuai pilihan dropdown
            $item->anggaran_aktif = match ((string) $tw) {
                '1' => $item->tw1_sum ?? 0,
                '2' => $item->tw2_sum ?? 0,
                '3' => $item->tw3_sum ?? 0,
                '4' => $item->tw4_sum ?? 0,
                default => ($item->tw1_sum + $item->tw2_sum + $item->tw3_sum + $item->tw4_sum), // Total dari AKB, bukan dari tabel RKAS
            };

            return $item;
        })->filter(function ($item) {
            // Sembunyikan item yang pagunya 0 pada TW terpilih
            return $item->anggaran_aktif > 0;
        });

        // --- REKAP BERDASARKAN KEGIATAN ---
        $rekapKegiatan = $dataRkasFiltered->groupBy('idbl')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->idbl,
                'uraian' => $items->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi',
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortByDesc('total_anggaran');

        // --- REKAP BERDASARKAN KODE REKENING (Khusus TW yang dipilih) ---
        $rekapRekening = $dataRkasFiltered->groupBy('kodeakun')->map(function ($items) {
            return (object) [
                'kode' => $items->first()->kodeakun,
                'uraian' => $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('kode');

        // --- REKAP BERDASARKAN SNP ---
        $rekapSnp = $dataRkasFiltered->groupBy(function ($item) {
            return $item->kegiatan->snp ?? 'BELUM DIATUR';
        })->map(function ($items, $snp) {
            return (object) [
                'uraian' => $snp,
                'total_anggaran' => $items->sum('anggaran_aktif'),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('uraian');

        // --- GRAND TOTAL (Sesuai TW yang dipilih) ---
        $grandTotalAnggaran = $dataRkasFiltered->sum('anggaran_aktif');

        // Kirim semua variabel ke view
        return view('rkas.rekap', compact(
            'anggaran',
            'rekapKegiatan',
            'rekapRekening',
            'rekapSnp',
            'grandTotalAnggaran',
            'rekapRekeningSemuaTw',
            'tw',
            'periodeText'
        ));
    }
}
