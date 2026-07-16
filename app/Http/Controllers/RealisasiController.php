<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Exports\RealisasiKomponenExport;
use App\Exports\RekananMultipleSheetExport;
use App\Exports\SemuaRekananExport;
use App\Models\Belanja;
use App\Models\DasarPajak;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RealisasiController extends Controller
{
    public function komponen(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // 1. Logic Periode Multi-Filter
        $periodeInput = $request->get('periode', ['tahun']);

        if (! is_array($periodeInput)) {
            $periodeInput = explode(',', $periodeInput);
        }

        $bulanArray = [];
        $periodeTextList = [];

        if (in_array('tahun', $periodeInput)) {
            $bulanArray = null;
            $periodeText = 'Tahunan';
        } else {
            foreach ($periodeInput as $p) {
                $p = trim(strtolower($p));

                if (str_starts_with($p, 'tw')) {
                    $tw = str_replace('tw', '', $p);
                    $bulan = match ($tw) {
                        '1' => [1, 2, 3],
                        '2' => [4, 5, 6],
                        '3' => [7, 8, 9],
                        '4' => [10, 11, 12],
                        default => [],
                    };
                    $bulanArray = array_merge($bulanArray, $bulan);
                    if (! empty($bulan)) {
                        $periodeTextList[] = 'TW '.$tw;
                    }
                } elseif (str_starts_with($p, 'b')) {
                    $b = (int) str_replace('b', '', $p);
                    if ($b >= 1 && $b <= 12) {
                        $bulanArray[] = $b;
                        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                        $periodeTextList[] = $namaBulan[$b - 1];
                    }
                }
            }

            if (! empty($bulanArray)) {
                $bulanArray = array_unique($bulanArray);
                sort($bulanArray);
                $periodeText = implode(', ', array_unique($periodeTextList));
            } else {
                $bulanArray = null;
                $periodeText = 'Tahunan';
                $periodeInput = ['tahun'];
            }
        }

        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

        // =====================================================================
        // 2. QUERY RKAS (Diperbaiki agar tidak bocor ke sekolah lain)
        // =====================================================================
        $dataRkas = Rkas::with(['kegiatan', 'korek', 'akb'])

            // PERBAIKAN 1: Tambahkan ->where('anggaran_id', $anggaran->id) untuk akbrincis
            ->withSum(['akbrincis as total_volume_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id) // <--- KUNCI PENCEGAH KEBOCORAN
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'volume')

            // PERBAIKAN 2: Tambahkan ->where('anggaran_id', $anggaran->id) untuk akbrincis
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id) // <--- KUNCI PENCEGAH KEBOCORAN
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')

            // Bagian Belanja ini sudah benar dari awal
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'total_bruto')
            ->withSum(['belanjaRincis as volume_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'volume')

            // Filter utama RKAS
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->filter(function ($item) {
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);

        $periode = $periodeInput;

        return view('realisasi.komponen', compact('dataRkas', 'anggaran', 'sekolah', 'periode', 'periodeText'));
    }

    public function exportExcel(Request $request)
    {
        // 1. Ambil Data Anggaran
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;

        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        // 2. AMBIL DATA BELANJA DENGAN RELASI NESTED
        $dataBelanja = Belanja::with([
            'rekanan',
            // Load RKAS, lalu dari RKAS load Kegiatan dan Korek
            'rincis.rkas.kegiatan',
            'rincis.rkas.korek',
        ])
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $sekolah->triwulan_aktif)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_bukti', 'asc')
            ->get();

        // 3. Generate Nama File
        $fileName = 'Laporan_Rincian_Belanja_'.strtoupper($anggaran->singkatan).'_'.date('YmdHis').'.xlsx';

        // 4. Download Excel
        return Excel::download(new BelanjaExport($dataBelanja), $fileName);
    }

    public function korek(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;
        $tw = $request->get('tw', 'tahun');

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // Definisikan range bulan untuk filter
        $bulanArray = match ($tw) {
            '1' => [1, 2, 3],
            '2' => [4, 5, 6],
            '3' => [7, 8, 9],
            '4' => [10, 11, 12],
            default => null, // Tahunan (ambil semua)
        };

        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
        $multiplier = 1 + ($persenPpn / 100);

        $dataRkas = Rkas::with(['kegiatan', 'korek'])

            // --- PERBAIKAN DI SINI: Tambah filter anggaran_id ---
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id) // Kunci anti-bocor
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            // ----------------------------------------------------

            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $multiplier, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id))
                    ->select(DB::raw("SUM(
                    CASE
                        WHEN (SELECT ppn FROM belanjas WHERE belanjas.id = belanja_rincis.belanja_id) > 0
                        THEN (volume * harga_satuan * $multiplier)
                        ELSE (volume * harga_satuan)
                    END
              )"));
            }], 'total_bruto')
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->filter(function ($item) {
                // Hanya ambil yang ada anggaran atau ada realisasi
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);

        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

        return view('realisasi.korek', compact('dataRkas', 'anggaran', 'tw', 'persenPpn', 'sekolah'));
    }

    public function jenisBelanja(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // 1. Logic Penentuan Periode
        $periode = $request->get('periode', 'tahun');
        $bulanArray = null;
        $periodeText = 'Tahunan';

        if (str_starts_with($periode, 'tw')) {
            $tw = str_replace('tw', '', $periode);
            $bulanArray = match ($tw) {
                '1' => [1, 2, 3], '2' => [4, 5, 6], '3' => [7, 8, 9], '4' => [10, 11, 12], default => null,
            };
            $periodeText = 'Triwulan '.$tw;
        } elseif (str_starts_with($periode, 'b')) {
            $bulan = (int) str_replace('b', '', $periode);
            if ($bulan >= 1 && $bulan <= 12) {
                $bulanArray = [$bulan];
                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $periodeText = 'Bulan '.$namaBulan[$bulan - 1];
            } else {
                $periode = 'tahun';
            }
        }

        $sekolah = \App\Models\Sekolah::where('id', $user->sekolah_id)->first();
        $persenPpn = \App\Models\DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
        $multiplier = 1 + ($persenPpn / 100);

        // 2. Query Utama RKAS
        $rawData = \App\Models\Rkas::with(['kegiatan', 'korek'])

            // --- PERBAIKAN DI SINI: Tambah filter anggaran_id ---
            ->withSum(['akbrincis as total_volume_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id) // Kunci anti-bocor
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'volume')

            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaran, $bulanArray) {
                $query->where('anggaran_id', $anggaran->id) // Kunci anti-bocor
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            // ----------------------------------------------------

            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $multiplier, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id))
                    ->select(\Illuminate\Support\Facades\DB::raw("SUM(
                        CASE
                            WHEN (SELECT ppn FROM belanjas WHERE belanjas.id = belanja_rincis.belanja_id) > 0
                            THEN (volume * harga_satuan * $multiplier)
                            ELSE (volume * harga_satuan)
                        END
                    )"));
            }], 'total_bruto')
            ->withSum(['belanjaRincis as volume_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'volume')
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->filter(function ($item) {
                // Hanya tampilkan yang ada anggarannya atau ada realisasinya
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            });

        // 3. Pengelompokan Data (Grouping 2 Level)
        $dataRkas = $rawData->groupBy([
            function ($item) {
                // Level 1: Kelompokkan dari relasi koreks->jenis_belanja
                $jenis = $item->korek->jenis_belanja ?? 'BELUM DIATUR';

                return strtoupper($jenis);
            },
            'kodeakun', // Level 2: Kode Rekening
        ]);

        // 4. Hitung Grand Total untuk dilempar ke View
        $grandTotalAnggaran = $rawData->sum('total_anggaran');
        $grandTotalRealisasi = $rawData->sum('total_realisasi');
        $grandTotalSisa = $grandTotalAnggaran - $grandTotalRealisasi;
        $grandPersen = $grandTotalAnggaran > 0 ? ($grandTotalRealisasi / $grandTotalAnggaran) * 100 : 0;

        return view('realisasi.jenis_belanja', compact(
            'dataRkas', 'anggaran', 'sekolah', 'periode', 'periodeText',
            'grandTotalAnggaran', 'grandTotalRealisasi', 'grandTotalSisa', 'grandPersen'
        ));
    }

    public function rekapPerRekanan(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // QUERY AGREGASI
        // Mengelompokkan total belanja per rekanan dan memecahnya ke kolom triwulan berdasarkan kolom 'tw'
        $dataRekap = Belanja::with('rekanan')
            ->where('anggaran_id', $anggaran->id)
            ->whereNotNull('rekanan_id') // Hanya ambil yang ada rekanannya
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

        // Hitung Grand Total untuk Footer Tabel
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
        // 1. Ambil Data Anggaran & Sekolah
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $rekanan = Rekanan::findOrFail($id);

        // 2. Ambil Data Rekanan
        $rekanan = Rekanan::findOrFail($id);

        // 3. AMBIL SEMUA DATA BELANJA (TRANSAKSI) MILIK REKANAN INI
        $dataBelanja = Belanja::with([
            'rekanan',
            'rincis.rkas.kegiatan',
            'rincis.rkas.korek',
            'pajaks.masterPajak', // Load pajak juga
        ])
            ->where('anggaran_id', $anggaran->id)
            ->where('tw', $sekolah->triwulan_aktif)
            ->where('rekanan_id', $id)
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_bukti', 'asc')
            ->get();

        if ($dataBelanja->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi.');
        }

        // 4. Download Excel (Panggil Class Multiple Sheet)
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '_', $rekanan->nama_rekanan);
        $fileName = 'URK_Belanja_'.strtoupper($cleanName).'.xlsx';

        return Excel::download(new RekananMultipleSheetExport($dataBelanja, $rekanan), $fileName);
    }

    public function exportSemuaRekanan(Request $request)
    {
        // 1. Ambil Data Anggaran & Sekolah
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        $sekolah = Sekolah::find(auth()->user()->sekolah_id);

        // 2. AMBIL SEMUA REKANAN BESERTA TRANSAKSINYA
        $daftarRekanan = Rekanan::whereHas('belanjas', function ($q) use ($anggaran, $sekolah) {
            $q->where('anggaran_id', $anggaran->id)
                ->where('tw', $sekolah->triwulan_aktif);
        })
            ->with(['belanjas' => function ($q) use ($anggaran, $sekolah) {
                $q->where('anggaran_id', $anggaran->id)
                    ->where('tw', $sekolah->triwulan_aktif)
                    ->with([
                        'rekanan',
                        'korek', // Dibutuhkan untuk penamaan judul SingleBelanjaSheet
                        'surats.rincis.rkas', // Dibutuhkan untuk daftar surat BAPB
                        'rincis.rkas.kegiatan',
                        'rincis.rkas.korek',
                        'pajaks.masterPajak',
                    ])
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('no_bukti', 'asc');
            }])
            ->get();

        if ($daftarRekanan->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi di triwulan ini.');
        }

        // 3. Download Excel Menggunakan Class Baru
        $fileName = 'SELURUH_URK_REKANAN_TW_'.$sekolah->triwulan_aktif.'.xlsx';

        return Excel::download(new SemuaRekananExport($daftarRekanan), $fileName);
    }

    public function exportKomponen(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        // 1. Logic Periode Multi-Filter (Disesuaikan dari komponen)
        $periodeInput = $request->get('periode', ['tahun']);
        if (! is_array($periodeInput)) {
            $periodeInput = explode(',', $periodeInput);
        }

        $bulanArray = [];
        $periodeTextList = [];

        if (in_array('tahun', $periodeInput)) {
            $bulanArray = null;
            $periodeText = 'Tahunan';
        } else {
            foreach ($periodeInput as $p) {
                $p = trim(strtolower($p));
                if (str_starts_with($p, 'tw')) {
                    $tw = str_replace('tw', '', $p);
                    $bulan = match ($tw) {
                        '1' => [1, 2, 3], '2' => [4, 5, 6], '3' => [7, 8, 9], '4' => [10, 11, 12], default => []
                    };
                    $bulanArray = array_merge($bulanArray, $bulan);
                    if (! empty($bulan)) {
                        $periodeTextList[] = 'TW '.$tw;
                    }
                } elseif (str_starts_with($p, 'b')) {
                    $b = (int) str_replace('b', '', $p);
                    if ($b >= 1 && $b <= 12) {
                        $bulanArray[] = $b;
                        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $periodeTextList[] = $namaBulan[$b - 1];
                    }
                }
            }

            if (! empty($bulanArray)) {
                $bulanArray = array_unique($bulanArray);
                sort($bulanArray);
                $periodeText = implode(', ', array_unique($periodeTextList));
            } else {
                $bulanArray = null;
                $periodeText = 'Tahunan';
            }
        }

        $sekolah = Sekolah::find($user->sekolah_id);

        // 2. Query RKAS
        $dataRkas = Rkas::with(['kegiatan', 'korek', 'akb'])
            ->withSum(['akbrincis as total_volume_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'volume')
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'total_bruto')
            ->withSum(['belanjaRincis as volume_realisasi' => function ($query) use ($anggaran, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaran->id));
            }], 'volume')
            ->where('anggaran_id', $anggaran->id)
            ->get()
            ->filter(function ($item) {
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'keterangan']);

        // 3. Download Excel
        $namaFile = 'Realisasi_Komponen_'.str_replace([' ', ','], ['_', ''], $periodeText).'_'.date('Ymd_His').'.xlsx';

        return Excel::download(new RealisasiKomponenExport($dataRkas, $anggaran, $sekolah, $periodeText), $namaFile);
    }

    private function siapkanDataSpj($anggaran, $sekolah)
    {
        // 1. Tambahkan 'rincis.rkas.korek' ke dalam with()
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
        $totals = [
            'bruto' => 0,
            'pajak' => array_fill_keys($pajakUnik, 0),
            'netto' => 0,
        ];

        foreach ($dataBelanja as $belanja) {
            $bruto = $belanja->subtotal + $belanja->ppn;
            $rowPajak = [];
            $totalPotongan = 0;

            foreach ($pajakUnik as $namaPajak) {
                $nominal = 0;
                foreach ($belanja->pajaks as $pajak) {
                    $pajakCurrent = $pajak->masterPajak->nama_pajak ?? 'Pajak Lainnya';
                    if ($pajakCurrent === $namaPajak) {
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

            // 2. Ambil nama korek/jenis belanja dari rincian pertama
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
        $mappedData = collect($mappedData)->sortBy([
            ['korek', 'asc'],      // Urutan 1: Berdasarkan Nama/Singkatan Korek
            ['tanggal', 'asc'],    // Urutan 2: Berdasarkan Tanggal
        ])->values()->all();       // .values()->all() mengembalikan index array kembali ke angka berurutan (0, 1, 2, dst)

        return compact('mappedData', 'pajakUnik', 'totals', 'anggaran', 'sekolah');
    }

    /**
     * Menampilkan ke Halaman View (HTML)
     */
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

    /**
     * Generate dan Download PDF
     */
    public function pdfLaporanSpj(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;
        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $data = $this->siapkanDataSpj($anggaran, $sekolah);

        $data['isPdf'] = true;

        // Mendefinisikan Ukuran F4 / Folio (210mm x 330mm) dalam satuan Point
        $kertasF4 = [0, 0, 595.28, 935.43];

        // Render PDF menggunakan kertas F4 kustom dengan orientasi lanskap
        $pdf = Pdf::loadView('realisasi.laporan_spj_pdf', $data)
            ->setPaper($kertasF4, 'landscape');

        $fileName = 'Laporan_SPJ_'.strtoupper($anggaran->singkatan).'_TW'.$sekolah->triwulan_aktif.'.pdf';

        return $pdf->download($fileName);
    }
}
