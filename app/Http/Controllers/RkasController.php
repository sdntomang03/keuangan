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
        // 1. Validasi Input
        $request->validate([
            'json_files' => 'required|array',
            'json_files.*' => 'required|mimes:json,txt',
        ]);

        // 2. Cek apakah anggaran aktif tersedia dari middleware
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        // 3. Mapping awalan ID berdasarkan singkatan (case-insensitive)
        $mapAnggaran = [
            'bos' => 10,
            'bop' => 20,
        ];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30; // Default 30 jika tidak terdaftar

        $count = 0;

        // 4. Gunakan Transaction untuk keamanan data
        DB::beginTransaction();
        try {
            foreach ($request->file('json_files') as $file) {
                $content = json_decode(file_get_contents($file), true);
                $dataRinci = $content['data'] ?? [];

                foreach ($dataRinci as $item) {
                    Rkas::updateOrCreate(
                        // Unique Key: Gabungan kode jenis dan ID Rinci asli
                        ['idblrinci' => $jenisAnggaran.$item['idblrinci']],
                        [

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
                            'anggaran_id' => $anggaran->id, // ID dari middleware
                        ]
                    );
                    $count++;
                }
            }
            DB::commit();

            return back()->with('success', "Berhasil mengimpor $count baris data.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat import: '.$e->getMessage());
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
        $bulanArray = match ($tw) {
            '1' => [1, 2, 3],
            '2' => [4, 5, 6],
            '3' => [7, 8, 9],
            '4' => [10, 11, 12],
            default => null, // Tahunan
        };

        $periodeText = $bulanArray ? "Triwulan {$tw}" : 'Tahunan';

        // 2. Tarik Data RKAS (Join dengan rincian bulan jika TW dipilih)
        $dataRkas = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->when($bulanArray, function ($query) use ($bulanArray) {
                // Jika difilter TW, hitung jumlah nominal dari tabel akbrincis
                $query->withSum(['akbrincis as anggaran_tw' => function ($q) use ($bulanArray) {
                    $q->whereIn('bulan', $bulanArray);
                }], 'nominal');
            })
            ->get()
            ->filter(function ($item) use ($bulanArray) {
                // Sembunyikan item yang pagunya 0 pada TW terpilih
                $pagu = $bulanArray ? ($item->anggaran_tw ?? 0) : $item->totalharga;

                return $pagu > 0;
            });

        // --- REKAP BERDASARKAN KEGIATAN ---
        $rekapKegiatan = $dataRkas->groupBy('idbl')->map(function ($items) use ($bulanArray) {
            return (object) [
                'kode' => $items->first()->idbl,
                'uraian' => $items->first()->kegiatan->namagiat ?? 'Kegiatan Tidak Terdefinisi',
                'total_anggaran' => $items->sum(fn ($item) => $bulanArray ? ($item->anggaran_tw ?? 0) : $item->totalharga),
                'jumlah_item' => $items->count(),
            ];
        })->sortByDesc('total_anggaran');

        // --- REKAP BERDASARKAN KODE REKENING ---
        $rekapRekening = $dataRkas->groupBy('kodeakun')->map(function ($items) use ($bulanArray) {
            return (object) [
                'kode' => $items->first()->kodeakun,
                'uraian' => $items->first()->korek->ket ?? 'Rekening Tidak Terdefinisi',
                'total_anggaran' => $items->sum(fn ($item) => $bulanArray ? ($item->anggaran_tw ?? 0) : $item->totalharga),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('kode');

        // --- REKAP BERDASARKAN SNP ---
        $rekapSnp = $dataRkas->groupBy(function ($item) {
            return $item->kegiatan->snp ?? 'BELUM DIATUR';
        })->map(function ($items, $snp) use ($bulanArray) {
            return (object) [
                'uraian' => $snp,
                'total_anggaran' => $items->sum(fn ($item) => $bulanArray ? ($item->anggaran_tw ?? 0) : $item->totalharga),
                'jumlah_item' => $items->count(),
            ];
        })->sortBy('uraian');

        // --- GRAND TOTAL ---
        $grandTotalAnggaran = $dataRkas->sum(fn ($item) => $bulanArray ? ($item->anggaran_tw ?? 0) : $item->totalharga);

        return view('rkas.rekap', compact(
            'anggaran',
            'rekapKegiatan',
            'rekapRekening',
            'rekapSnp',
            'grandTotalAnggaran',
            'tw',
            'periodeText'
        ));
    }
}
