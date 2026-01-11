<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\DasarPajak;
use App\Models\Rekanan;
use App\Models\Rkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BelanjaController extends Controller
{
    // BelanjaController.php
    public function create()
    {
        $userId = auth()->id();
        $listPajak = DasarPajak::all();
        // 1. Ambil Pengaturan Aktif (Tahun & Sumber Dana BOS/BOP)
        $setting = DB::table('settings')->where('user_id', $userId)->first();

        if (! $setting) {
            return redirect()->route('settings.index')
                ->with('error', 'Silakan atur Tahun dan Anggaran Aktif di Pengaturan terlebih dahulu.');
        }

        $rekanans = Rekanan::where('user_id', $userId)->get();

        // 2. Ambil list kegiatan sesuai Tahun dan Anggaran (BOS/BOP) yang aktif
        $listKegiatan = DB::table('rkas')
            ->join('kegiatans', 'rkas.idbl', '=', 'kegiatans.idbl')
            ->where('rkas.user_id', $userId)
            ->where('rkas.tahun', $setting->tahun_aktif)       // Filter Tahun
            ->where('rkas.jenis_anggaran', $setting->anggaran_aktif) // Filter BOS atau BOP
            ->select(
                'kegiatans.idbl',
                'kegiatans.namagiat'
            )
            ->distinct()
            ->get();

        return view('belanja.create', compact('rekanans', 'listKegiatan', 'listPajak', 'setting'));
    }

    public function getRekening(Request $request)
    {
        return DB::table('rkas')
            ->join('koreks', 'rkas.kodeakun', '=', 'koreks.id')
            ->where('rkas.user_id', auth()->id())
            ->where('rkas.idbl', $request->idbl)
            ->select(
                'koreks.id as koderekening', // Kita samakan aliasnya untuk JS
                'koreks.uraian_singkat as namarekening' // Sesuaikan kolom nama di tabel koderekenings
            )
            ->distinct()
            ->get();
    }

    public function getKomponen(Request $request)
    {
        $userId = auth()->id();
        $setting = DB::table('settings')->where('user_id', $userId)->first();

        // Pastikan triwulan dikonversi dengan aman
        $tw = (int) filter_var($setting->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        $bulanRange = match ($tw) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] // Jika TW aneh, ambil semua bulan saja
        };

        $query = Rkas::where('rkas.user_id', $userId)
            ->join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')
            ->where('rkas.idbl', $request->idbl)
            // Gunakan where mencakup string/int untuk kodeakun
            ->where(function ($q) use ($request) {
                $q->where('rkas.kodeakun', $request->koderekening)
                    ->orWhere('rkas.kodeakun', (int) $request->koderekening);
            })
            ->where('rkas.jenis_anggaran', 'LIKE', '%'.$setting->anggaran_aktif.'%')
            ->whereIn('akb_rincis.bulan', $bulanRange)
            ->select(
                'rkas.id',
                'rkas.namakomponen',
                'rkas.hargasatuan',
                'rkas.spek',
                DB::raw('SUM(akb_rincis.volume) as volume_bulan')
            )
            ->groupBy('rkas.id', 'rkas.namakomponen', 'rkas.hargasatuan', 'rkas.spek');

        // DEBUG: Jalankan ini di browser untuk melihat SQL aslinya jika tetap kosong
        // dd($query->toSql(), $query->getBindings());

        return $query->get();
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string|unique:belanjas,no_bukti',
            'rekanan_id' => 'required',
            'items' => 'required|array|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {

                // 2. Simpan Header Belanja
                $belanja = Belanja::create([
                    'user_id' => Auth::id(),
                    'rekanan_id' => $request->rekanan_id,
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'uraian' => $request->uraian,
                    'subtotal' => $request->sub_total,
                    'ppn' => $request->ppn ?? 0,
                    'pph' => $request->pph ?? 0,
                    'transfer' => $request->transfer,
                ]);

                // 3. Simpan Rincian
                foreach ($request->items as $item) {
                    $belanja->rincis()->create([
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $item['spek'] ?? '-',
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'total_bruto' => $item['volume'] * $item['harga_satuan'],
                    ]);
                }

                // 4. Simpan Pajak (SESUAIKAN KEY DISINI)
                if ($request->has('pajaks')) {
                    foreach ($request->pajaks as $pajak) {
                        // Gunakan 'dasar_pajak_id' sesuai hasil dd() Anda
                        if (! empty($pajak['dasar_pajak_id']) && $pajak['nominal'] > 0) {
                            $belanja->pajaks()->create([
                                'dasar_pajak_id' => $pajak['dasar_pajak_id'],
                                'nominal' => $pajak['nominal'],
                                'is_terima' => false,
                                'is_setor' => false,
                            ]);
                        }
                    }
                }

                return redirect()->route('belanja.index')->with('success', 'Posting BKU Berhasil!');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: '.$e->getMessage())->withInput();
        }
    }

    public function index()
    {
        echo 'ok';
    }
}
