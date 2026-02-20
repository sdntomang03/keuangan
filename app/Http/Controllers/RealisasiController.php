<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Exports\RekananMultipleSheetExport;
use App\Models\Belanja;
use App\Models\DasarPajak;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
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

        // 1. Logic Periode (Tahunan / Triwulan / Bulanan)
        $periode = $request->get('periode', 'tahun');
        $bulanArray = null;
        $periodeText = 'Tahunan';

        // Cek jika filter Triwulan
        if (str_starts_with($periode, 'tw')) {
            $tw = str_replace('tw', '', $periode);
            $bulanArray = match ($tw) {
                '1' => [1, 2, 3],
                '2' => [4, 5, 6],
                '3' => [7, 8, 9],
                '4' => [10, 11, 12],
                default => null,
            };
            $periodeText = 'Triwulan '.$tw;
        }
        // Cek jika filter Bulan
        elseif (str_starts_with($periode, 'b')) {
            $bulan = (int) str_replace('b', '', $periode);
            if ($bulan >= 1 && $bulan <= 12) {
                $bulanArray = [$bulan]; // Hanya 1 bulan di dalam array
                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $periodeText = 'Bulan '.$namaBulan[$bulan - 1];
            } else {
                $periode = 'tahun'; // Fallback jika error
            }
        }

        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

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
                // Sembunyikan jika pagu dan realisasinya 0 pada periode yang dipilih
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);

        // Kirim variabel $periode dan $periodeText ke View
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
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
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
    // --- TAMBAHKAN FILTER DI SINI ---
            ->filter(function ($item) {
                // Hanya ambil yang ada anggaran atau ada realisasi
                return $item->total_anggaran > 0 || $item->total_realisasi > 0;
            })
            ->groupBy(['idbl', 'kodeakun']);
        $sekolah = Sekolah::where('id', $user->sekolah_id)->first();

        return view('realisasi.korek', compact('dataRkas', 'anggaran', 'tw', 'persenPpn', 'sekolah'));
    }

    public function rekapPerRekanan(Request $request)
    {
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->back()->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // QUERY AGREGASI
        // Mengelompokkan total belanja per rekanan dan memecahnya ke kolom triwulan
        $dataRekap = Belanja::with('rekanan')
            ->where('anggaran_id', $anggaran->id)
            ->whereNotNull('rekanan_id') // Hanya ambil yang ada rekanannya
            ->select('rekanan_id')
            ->selectRaw('
    SUM(CASE WHEN MONTH(tanggal) BETWEEN 1 AND 3 THEN (subtotal + ppn) ELSE 0 END) as tw1,
    SUM(CASE WHEN MONTH(tanggal) BETWEEN 4 AND 6 THEN (subtotal + ppn) ELSE 0 END) as tw2,
    SUM(CASE WHEN MONTH(tanggal) BETWEEN 7 AND 9 THEN (subtotal + ppn) ELSE 0 END) as tw3,
    SUM(CASE WHEN MONTH(tanggal) BETWEEN 10 AND 12 THEN (subtotal + ppn) ELSE 0 END) as tw4,
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
}
