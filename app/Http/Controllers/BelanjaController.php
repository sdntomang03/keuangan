<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Bku;
use App\Models\DasarPajak;
use App\Models\Kegiatan;
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

        $rekanans = Rekanan::where('user_id', $userId)
            ->orderBy('nama_rekanan', 'asc') // 'asc' untuk A-Z, 'desc' untuk Z-A
            ->get();

        // 2. Ambil list kegiatan sesuai Tahun dan Anggaran (BOS/BOP) yang aktif
        $listKegiatan = DB::table('rkas')
            ->leftJoin('kegiatans', 'rkas.idbl', '=', 'kegiatans.idbl') // Gunakan Left Join
            ->where('rkas.setting_id', auth()->user()->setting_id)
            ->where('rkas.tahun', $setting->tahun_aktif)
            ->where('rkas.jenis_anggaran', 'LIKE', '%'.$setting->anggaran_aktif.'%')
            ->select(
                'rkas.idbl', // Ambil idbl dari tabel rkas saja dulu
                DB::raw('COALESCE(kegiatans.namagiat, rkas.giatsubteks) as namagiat')
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
        $user = auth()->user();
        // Gunakan relasi agar lebih clean, atau ambil dari tabel settings
        $setting = DB::table('settings')->where('id', $user->setting_id)->first();

        if (! $setting) {
            return response()->json([]);
        }

        // Konversi triwulan ke range bulan
        $tw = (int) filter_var($setting->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $bulanRange = match ($tw) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => range(1, 12)
        };

        return Rkas::where('rkas.setting_id', $user->setting_id) // Ganti user_id menjadi setting_id
            ->join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')
            ->where('rkas.idbl', $request->idbl)
            ->where('rkas.kodeakun', $request->koderekening)
            ->where('rkas.jenis_anggaran', 'LIKE', '%'.$setting->anggaran_aktif.'%')
            ->whereIn('akb_rincis.bulan', $bulanRange)
            ->select(
                'rkas.id',
                'rkas.idblrinci',
                'rkas.namakomponen',
                'rkas.hargasatuan',
                'rkas.spek',
                DB::raw('SUM(akb_rincis.volume) as volume_bulan')
            )
            // Tambahkan idblrinci ke groupBy agar tidak error di beberapa database engine
            ->groupBy('rkas.id', 'rkas.idblrinci', 'rkas.namakomponen', 'rkas.hargasatuan', 'rkas.spek')
            ->get();
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // 1. Validasi Input Dasar
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string|unique:belanjas,no_bukti',
            'rekanan_id' => 'required',
            'items' => 'required|array|min:1',
        ]);

        // 2. Ambil Setting Triwulan Aktif dan Mapping Bulan (Angka)
        $setting = DB::table('settings')->first();
        $twAktif = $setting->triwulan_aktif ?? 1;

        $mappingBulan = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
        ];
        $bulanDicheck = $mappingBulan[$twAktif];

        try {
            return DB::transaction(function () use ($request, $bulanDicheck, $twAktif) {

                // --- VALIDASI PAGU KOMPONEN ---
                foreach ($request->items as $item) {
                    $totalBrutoInput = $item['volume'] * $item['harga_satuan'];

                    // Ambil total nominal di table akb_rincis untuk triwulan terkait
                    $totalPaguAnggaran = DB::table('akb_rincis')
                        ->where('idblrinci', $item['idblrinci'])
                        ->whereIn('bulan', $bulanDicheck)
                        ->sum('nominal');

                    // Opsional: Jika ingin menghitung sisa (dikurangi belanja yang sudah diposting sebelumnya)
                    $sudahDibelanjakan = DB::table('belanja_rincis')
                        ->where('idblrinci', $item['idblrinci'])
                        ->sum('total_bruto');

                    $sisaPaguTersedia = $totalPaguAnggaran - $sudahDibelanjakan;

                    if ($totalPaguAnggaran <= 0) {
                        throw new \Exception('Komponen ['.$item['namakomponen']."] tidak memiliki anggaran di Triwulan $twAktif.");
                    }

                    if ($totalBrutoInput > $sisaPaguTersedia) {
                        throw new \Exception(
                            'Pagu Tidak Cukup! '.$item['namakomponen'].
                            ". Sisa Pagu TW $twAktif: Rp ".number_format($sisaPaguTersedia, 0, ',', '.').
                            '. Input: Rp '.number_format($totalBrutoInput, 0, ',', '.')
                        );
                    }
                }

                // 3. Simpan Header Belanja
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
                    'idbl' => $request->idbl,
                    'kodeakun' => $request->kodeakun,
                    'status' => 'draft',
                ]);
                $hasPpn = $request->ppn > 0;
                // 4. Simpan Rincian
                $brutoDasar = $item['volume'] * $item['harga_satuan'];
                $totalBrutoFinal = $hasPpn ? ($brutoDasar * 1.11) : $brutoDasar;
                foreach ($request->items as $item) {
                    $belanja->rincis()->create([
                        'idblrinci' => $item['idblrinci'],
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $item['spek'] ?? '-',
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'total_bruto' => $totalBrutoFinal,
                    ]);
                }
                // dd($request->pajaks);
                // 5. Simpan Pajak
                if ($request->has('pajaks')) {
                    foreach ($request->pajaks as $pajak) {
                        if (! empty($pajak['id_master']) && $pajak['nominal'] > 0) {
                            $belanja->pajaks()->create([
                                'dasar_pajak_id' => $pajak['id_master'],
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
            // Pesan error akan muncul di halaman jika pagu tidak cukup
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function index()
    {
        $userId = auth()->id();
        $setting = DB::table('settings')->where('user_id', $userId)->first();
        $kode = $setting->anggaran_aktif === 'bos' ? '3.01' : '3.04';

        $belanjas = Belanja::with(['rekanan', 'kegiatan', 'korek'])
            ->whereRelation('kegiatan', 'kodedana', $kode)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('belanja.index', compact('belanjas'));
    }

    public function destroy($id)
    {
        // 1. Cari data belanja
        $belanja = Belanja::findOrFail($id);

        // 2. Cek apakah status sudah 'posted'
        // Asumsi: Nama kolom di database Anda adalah 'status'
        if ($belanja->status === 'posted') {
            return redirect()->route('belanja.index')
                ->with('error', 'Transaksi tidak dapat dihapus karena sudah diposting.');
        }

        // 3. Hapus data belanja
        // Catatan: Jika Anda menggunakan detail belanja, pastikan relasi diset cascade
        // atau hapus detailnya secara manual sebelum baris ini.
        $belanja->delete();

        // 4. Kembalikan ke halaman index dengan pesan sukses
        return redirect()->route('belanja.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function show($id)
    {
        // Mengambil data belanja beserta relasinya
        $belanja = Belanja::with(['rekanan', 'rincis', 'pajaks.masterPajak'])->findOrFail($id);

        // Mengambil data kegiatan untuk menampilkan nama kegiatan (opsional jika idbl adalah foreign key)
        $kegiatan = Kegiatan::where('idbl', $belanja->idbl)->first();

        return view('belanja.show', compact('belanja', 'kegiatan'));
    }

    public function post($id)
    {
        $belanja = Belanja::with('pajaks.masterPajak')->findOrFail($id);

        DB::transaction(function () use ($belanja) {
            // 1. Catat Transaksi Belanja Utama
            Bku::catat(
                $belanja->tanggal,
                $belanja->no_bukti,
                'Dibayar '.$belanja->uraian,
                0,                  // debit
                $belanja->subtotal + $belanja->ppn, // kredit
                $belanja->id,       // belanja_id
                null                // pajak_id (kosong)
            );

            // 2. Catat Tiap Pajak yang ada di Belanja tersebut
            foreach ($belanja->pajaks as $pajak) {
                Bku::catat(
                    $belanja->tanggal,
                    $belanja->no_bukti,
                    'Diterima '.$pajak->masterPajak->nama_pajak.' '.$belanja->uraian,
                    $pajak->nominal, // debit
                    0,               // kredit
                    $belanja->id,    // tetap masukkan belanja_id sebagai referensi induk
                    $pajak->id       // masukkan pajak_id
                );

                // Tandai pajak sudah diterima di tabel pajaks
                $pajak->update(['is_terima' => true]);
            }

            // 3. Update status belanja agar tidak diposting ulang
            $belanja->update(['status' => 'posted']);
        });

        return back()->with('success', 'Transaksi dan Pajak berhasil dicatat di BKU');
    }
}
