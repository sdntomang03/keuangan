<?php

namespace App\Http\Controllers;

use App\Http\Requests\BelanjaRequest;
use App\Models\Anggaran;
use App\Models\Belanja;
use App\Models\BelanjaRinci;
use App\Models\Bku;
use App\Models\DasarPajak;
use App\Models\Kegiatan;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use App\Services\BelanjaService;
use App\Traits\FinancialContextTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BelanjaController extends Controller
{
    use FinancialContextTrait;

    protected BelanjaService $belanjaService;

    public function __construct(BelanjaService $belanjaService)
    {
        $this->belanjaService = $belanjaService;
    }

    public function create(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $user = auth()->user(); // Ambil data user login
        $listPajak = DasarPajak::all();

        // 1. Ambil data Anggaran Aktif dari Middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan pilih Anggaran Aktif (BOS/BOP) terlebih dahulu.');
        }

        // 2. Ambil Data Sekolah Aktif
        // Mengasumsikan User model memiliki relasi 'sekolah' atau menggunakan sekolah_id
        $sekolah = Sekolah::find($user->sekolah_id);

        $rekanans = Rekanan::where('sekolah_id', $sekolah->id)
            ->orderBy('nama_rekanan', 'asc')
            ->get();

        // 3. Ambil list kegiatan berdasarkan ID Anggaran Aktif
        $listKegiatan = DB::table('rkas')
            ->leftJoin('kegiatans', 'rkas.idbl', '=', 'kegiatans.idbl')
            ->where('rkas.anggaran_id', $anggaran->id)
            ->select(
                'rkas.idbl',
                DB::raw('COALESCE(kegiatans.namagiat, rkas.giatsubteks) as namagiat')
            )
            ->distinct()
            ->get();

        // Kirim $sekolah dan $anggaran ke view
        return view('belanja.create', compact('rekanans', 'listKegiatan', 'listPajak', 'anggaran', 'sekolah'));
    }

    public function getRekening(Request $request)
    {
        $anggaran = $request->anggaran_data; // Dari Middleware
        $user = auth()->user();
        $sekolah = \App\Models\Sekolah::find($user->sekolah_id);

        if (! $anggaran || ! $sekolah) {
            return response()->json([]);
        }

        // 1. Tentukan range bulan berdasarkan TW aktif sekolah menggunakan Trait
        $tw = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        $bulanRange = $this->getBulanDariTw($tw); // <-- Cukup panggil ini!

        // 2. Query dengan Join ke akb_rincis untuk filter saldo
        return DB::table('rkas')
            ->join('koreks', 'rkas.kodeakun', '=', 'koreks.id')

            // --- TAMBAHAN JOIN UNTUK CEK SALDO ---
            ->join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')

            ->where('rkas.anggaran_id', $anggaran->id)
            ->where('akb_rincis.anggaran_id', $anggaran->id) // Kunci anti-bocor
            ->where('rkas.idbl', $request->idbl)

            // --- FILTER TRIWULAN DAN NOMINAL > 0 ---
            ->whereIn('akb_rincis.bulan', $bulanRange)
            ->where('akb_rincis.nominal', '>', 0)

            ->select(
                'koreks.id as koderekening',
                'koreks.uraian_singkat as namarekening'
            )
            ->distinct() // Pastikan nama rekening tidak dobel meski ada banyak barang
            ->get();
    }

    public function getKomponen(Request $request)
    {
        $user = auth()->user();
        $anggaran = $request->anggaran_data;
        $sekolah = Sekolah::find($user->sekolah_id);

        if (! $anggaran || ! $sekolah) {
            return response()->json([]);
        }

        // 1. Tentukan range bulan berdasarkan TW aktif sekolah menggunakan Trait
        $tw = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        $bulanRange = $this->getBulanDariTw($tw); // <-- Cukup panggil ini!

        // Mulai Query Dasar
        $query = Rkas::join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')
            ->where('rkas.anggaran_id', $anggaran->id)

            // --- TAMBAHKAN BARIS INI: Filter juga tabel akb_rincis-nya ---
            ->where('akb_rincis.anggaran_id', $anggaran->id)

            ->where('rkas.idbl', $request->idbl)
            ->where('rkas.kodeakun', $request->koderekening)
            ->whereIn('akb_rincis.bulan', $bulanRange);

        // Jika user mengirim 'keterangan' DAN isinya BUKAN 'ALL', maka filter spesifik.
        if ($request->filled('keterangan') && $request->keterangan !== 'ALL') {
            $query->where('rkas.keterangan', $request->keterangan);
        }

        return $query->select(
            'rkas.id',
            'rkas.idblrinci',
            'rkas.namakomponen',
            'rkas.hargasatuan',
            'rkas.satuan',
            'rkas.spek',
            'rkas.keterangan',
            DB::raw('SUM(akb_rincis.volume) as volume_bulan')
        )
            ->groupBy('rkas.id', 'rkas.idblrinci', 'rkas.namakomponen', 'rkas.hargasatuan', 'rkas.satuan', 'rkas.spek', 'rkas.keterangan')
            ->get();
    }

    public function getKeterangan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        $user = auth()->user();
        $sekolah = Sekolah::find($user->sekolah_id);

        if (! $anggaran || ! $sekolah) {
            return response()->json([]);
        }

        $data = DB::table('rkas')
            ->where('anggaran_id', $anggaran->id)
            ->where('idbl', $request->idbl)
            ->where('kodeakun', $request->koderekening) // Ini mencocokkan nilai ID
            ->whereNotNull('keterangan')
            ->where('keterangan', '!=', '')
            ->where('keterangan', '!=', '-')
            ->select('keterangan')
            ->distinct()
            ->orderBy('keterangan', 'asc')
            ->get();

        return response()->json($data);
    }

    public function store(BelanjaRequest $request)
    {
        $anggaran = $request->anggaran_data;
        $sekolah = auth()->user()->sekolah;

        if (! $anggaran || ! $sekolah) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $bulanDicheck = $this->getBulanDariTw($twAktif);
        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 0;

        try {
            DB::transaction(function () use ($request, $bulanDicheck, $twAktif, $anggaran, $persenPpn) {
                // 1. Validasi Pagu (Via Service)
                // Cek apakah user mengisi nilai PPN di form > 0
                $hasPpn = ($request->ppn > 0);

                // 1. Validasi Pagu (Via Service)
                $this->belanjaService->validasiPaguAnggaran($request->items, $anggaran->id, $bulanDicheck, $twAktif, $hasPpn, $persenPpn);
                // 2. Simpan Header
                // 2. Simpan Header
                $belanja = Belanja::create([
                    'user_id' => auth()->id(),
                    'anggaran_id' => $anggaran->id,
                    'rekanan_id' => $request->rekanan_id,
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'rincian' => $request->rincian,
                    'uraian' => $request->uraian,
                    'subtotal' => $request->sub_total ?? 0, // Mapping manual di sini
                    'ppn' => $request->ppn ?? 0,
                    'pph' => $request->pph ?? 0,
                    'transfer' => $request->transfer,
                    'idbl' => $request->idbl,
                    'kodeakun' => $request->kodeakun,
                    'tw' => $twAktif,
                    'status' => 'draft',
                ]);

                // 3. Simpan Rincian dan Pajak (Via Service)
                $this->belanjaService->sinkronisasiRincianDanPajak($belanja, $request->validated(), $persenPpn);
            });

            return redirect()->route('belanja.index')->with('success', 'Posting BKU Berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();

        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        // 2. Ambil data sekolah aktif
        $sekolah = Sekolah::find($user->sekolah_id);

        // --- TAMBAHAN LOGIKA TW AKTIF ---
        // Ambil angka TW yang sedang aktif di pengaturan sekolah
        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        // Cek inputan dari user.
        // Jika di URL ada ?tw=... maka gunakan itu.
        // Jika tidak ada (baru pertama buka halaman), gunakan $twAktif sebagai default.
        $selectedTw = $request->input('tw', $twAktif);

        // 3. Query Dasar
        $query = Belanja::with(['rekanan', 'kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id);

        // 4. FILTER TW (LOGIKA BARU DIPERBARUI)
        // Jika user ingin melihat "Semua TW", misal dengan mengirim ?tw=semua,
        // kita bisa melewatinya. Jika berupa angka, kita filter.
        if ($selectedTw && $selectedTw !== 'semua') {
            $query->where('tw', $selectedTw);
        }

        // 5. Eksekusi Query dengan Pagination
        $belanjas = $query->orderBy('tanggal', 'asc')
            ->paginate(10)
            ->withQueryString(); // PENTING: Agar filter tidak hilang saat klik halaman 2

        // Kirim $selectedTw dan $twAktif ke View agar bisa digunakan di Dropdown/Tab menu
        return view('belanja.index', compact('belanjas', 'anggaran', 'sekolah', 'twAktif', 'selectedTw'));
    }

    public function destroy($id)
    {
        // 1. Cari data belanja dengan proteksi user_id (Security Check)
        $belanja = Belanja::where('user_id', auth()->id())->findOrFail($id);

        // 2. Cek apakah status sudah 'posted'
        if ($belanja->status === 'posted') {
            return redirect()->route('belanja.index')
                ->with('error', 'Transaksi tidak dapat dihapus karena sudah diposting ke BKU.');
        }

        try {
            DB::transaction(function () use ($belanja) {
                // 3. Hapus data terkait secara manual (jika tidak menggunakan ON DELETE CASCADE)
                // Hapus detail rincian belanja
                $belanja->rincis()->delete();

                // Hapus data pajak terkait belanja ini
                $belanja->pajaks()->delete();

                // 4. Hapus Header Belanja
                $belanja->delete();
            });

            return redirect()->route('belanja.index')
                ->with('success', 'Transaksi dan rinciannya berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('belanja.index')
                ->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        // Mengambil data belanja beserta relasinya
        $belanja = Belanja::with(['rekanan', 'rincis.rkas', 'pajaks.masterPajak'])->findOrFail($id);

        // Mengambil data kegiatan untuk menampilkan nama kegiatan (opsional jika idbl adalah foreign key)
        $kegiatan = Kegiatan::where('idbl', $belanja->idbl)->first();

        return view('belanja.show', compact('belanja', 'kegiatan'));
    }

    public function post($id, Request $request)
    {
        $belanja = Belanja::with('rekanan', 'pajaks.masterPajak')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $anggaran = $request->anggaran_data;
        $sekolah = auth()->user()->sekolah;

        if ($belanja->status === 'posted') {
            return back()->with('error', 'Transaksi ini sudah diposting sebelumnya.');
        }

        try {
            // Panggil Service untuk mengeksekusi logika BKU
            $this->belanjaService->postingKeBku($belanja, $anggaran, $sekolah);

            return back()->with('success', "Transaksi {$anggaran->singkatan} berhasil dicatat di BKU.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memposting transaksi: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        // 1. Ambil data utama belanja
        $belanja = Belanja::findOrFail($id);

        // 2. Ambil data sekolah (Pastikan cara ambilnya sesuai struktur DB Anda)
        // Jika sekolah berelasi dengan user yang login:
        $sekolah = auth()->user()->sekolah;

        // 3. Ambil data anggaran terkait
        $anggaran = Anggaran::find($belanja->anggaran_id);

        // 4. Siapkan data items dan pajaks untuk Alpine.js
        $items = $belanja->rincis->map(function ($item) {
            return [
                'idblrinci' => $item->idblrinci,
                'namakomponen' => $item->namakomponen,
                'spek' => $item->spek,
                'volume' => $item->volume,
                'harga_satuan' => $item->harga_satuan,
                'satuan' => $item->rkas->satuan ?? '-',
            ];
        });

        $pajaks = $belanja->pajaks->map(function ($p) {
            return [
                // Ubah $p->id_master_pajak menjadi $p->dasar_pajak_id
                'id_master' => $p->dasar_pajak_id,
                'nominal' => $p->nominal,
            ];
        });

        // 5. Ambil list untuk dropdown
        $listPajak = DasarPajak::all();
        $rekanans = Rekanan::where('sekolah_id', $sekolah->id)
            ->orderBy('nama_rekanan', 'asc')
            ->get();
        $kegiatan = Kegiatan::where('idbl', $belanja->idbl)->first();

        // KIRIM SEMUA VARIABEL KE VIEW
        return view('belanja.edit', compact(
            'belanja',
            'sekolah',
            'anggaran',
            'items',
            'pajaks',
            'listPajak',
            'rekanans',
            'kegiatan'
        ));
    }

    public function update(BelanjaRequest $request, $id)
    {
        $belanja = Belanja::findOrFail($id);
        $anggaran = $request->anggaran_data;
        $sekolah = auth()->user()->sekolah;

        if (! $anggaran || ! $sekolah) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $bulanDicheck = $this->getBulanDariTw($twAktif);
        $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 0;

        try {
            DB::transaction(function () use ($request, $bulanDicheck, $twAktif, $anggaran, $belanja, $persenPpn) {
                // 1. Validasi Pagu dengan mengabaikan transaksi ini (Via Service)
                // Cek apakah user mengisi nilai PPN di form > 0
                $hasPpn = ($request->ppn > 0);

                // 1. Validasi Pagu dengan mengabaikan transaksi ini (Via Service)
                $this->belanjaService->validasiPaguAnggaran($request->items, $anggaran->id, $bulanDicheck, $twAktif, $hasPpn, $persenPpn, $belanja->id);

                // 2. Update Header
                $belanja->update([
                    'rekanan_id' => $request->rekanan_id,
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'rincian' => $request->rincian,
                    'uraian' => $request->uraian,
                    'subtotal' => $request->sub_total ?? 0,
                    'ppn' => $request->ppn ?? 0,
                    'pph' => $request->pph ?? 0,
                    'transfer' => $request->transfer,
                    'idbl' => $request->idbl,
                    'kodeakun' => $request->kodeakun,
                ]);

                // 3. Sinkronisasi Rincian dan Pajak (Via Service)
                $this->belanjaService->sinkronisasiRincianDanPajak($belanja, $request->validated(), $persenPpn);
            });

            return redirect()->route('belanja.index')->with('success', 'Perubahan BKU Berhasil Disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function editPenawaran($id)
    {
        $belanja = Belanja::with('rincis')->findOrFail($id);

        return view('belanja.edit_penawaran', compact('belanja'));
    }

    /**
     * Proses Update Harga Penawaran Massal
     */
    public function updatePenawaran(Request $request, $id)
    {
        $request->validate([
            'no_bast' => 'nullable|string',
            'tanggal_bast' => 'nullable|date',
            'items' => 'required|array',
        ]);

        $belanja = Belanja::findOrFail($id);

        DB::transaction(function () use ($request, $belanja) {

            // 1. UPDATE DATA BAST DI TABEL BELANJA (PARENT)
            $belanja->update([
                'no_bast' => $request->no_bast,
                'tanggal_bast' => $request->tanggal_bast,
            ]);

            // 2. UPDATE HARGA BARANG (CHILD)
            foreach ($request->items as $rinciId => $hargaPenawaran) {
                BelanjaRinci::where('id', $rinciId)->update([
                    'harga_penawaran' => $hargaPenawaran,
                ]);
            }
        });

        return redirect(url()->previous().'#barang')->with('success', 'Rincian penawaran berhasil diperbarui!');
    }

    public function duplicate(Request $request, $id)
    {
        $original = Belanja::with(['rincis', 'pajaks'])->findOrFail($id);
        $anggaran = $request->anggaran_data ?? $original->anggaran;
        $sekolah = Sekolah::find($anggaran->sekolah_id);

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $bulanDicheck = $this->getBulanDariTw($twAktif);

        try {
            DB::transaction(function () use ($original, $anggaran, $bulanDicheck, $twAktif) {
                // 1. Validasi Pagu menggunakan rincian yang sudah ada (Via Service)
                // Cukup gunakan toArray() dari data asli, tidak perlu $hasPpn dkk.
                $this->belanjaService->validasiPaguAnggaran($original->rincis->toArray(), $anggaran->id, $bulanDicheck, $twAktif);

                // 2. Replikasi Data Header
                $newBelanja = $original->replicate();
                $newBelanja->no_bukti = $original->id.'-COPY-'.time();
                $newBelanja->tanggal = now();
                $newBelanja->push(); // Simpan parent

                // 3. Replikasi Relasi (Rincian & Pajak)
                foreach ($original->rincis as $rinci) {
                    $newBelanja->rincis()->create($rinci->toArray());
                }
                foreach ($original->pajaks as $pajak) {
                    $newBelanja->pajaks()->create($pajak->toArray());
                }
            });

            return back()->with('success', 'Transaksi berhasil diduplikat! Silakan sesuaikan Tanggal dan Nomor Bukti.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // JSON Endpoints juga menjadi ramping berkat Trait getBulanDariTw
    public function getJson($id)
    {
        $belanja = Belanja::with(['rincis', 'rekanan', 'pajaks.masterPajak', 'korek', 'kegiatan', 'rkas'])->findOrFail($id);

        $bulanTw = $this->getBulanDariTw($belanja->tw); // Menggunakan Trait

        $belanja->rincis->map(function ($rinci) use ($belanja, $bulanTw) {
            $akbRincis = \App\Models\AkbRinci::where('anggaran_id', $belanja->anggaran_id)
                ->where('idblrinci', $rinci->idblrinci)
                ->get();

            $rinci->total_volume_setahun = $akbRincis->sum('volume');
            $rinci->pagu_setahun = $akbRincis->sum('nominal');

            $akbTw = $akbRincis->whereIn('bulan', $bulanTw);
            $rinci->total_volume_akb = $akbTw->sum('volume');
            $rinci->pagu_dana = $akbTw->sum('nominal');

            return $rinci;
        });

        return response()->json(['belanja' => $belanja, 'sekolah' => auth()->user()->sekolah]);
    }

    public function getRiwayatKomponen(Request $request)
    {
        $idblrincis = $request->input('idblrincis', []);

        if (empty($idblrincis)) {
            return response()->json([]);
        }

        // Cari riwayat di belanja_rincis yang tergabung dengan belanjas
        // Diurutkan dari tanggal belanja paling baru
        $riwayat = DB::table('belanja_rincis')
            ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
            ->whereIn('belanja_rincis.idblrinci', $idblrincis)
            ->where('belanjas.user_id', auth()->id()) // Pastikan riwayat milik sekolah/user ini sendiri
            ->orderBy('belanjas.tanggal', 'desc')
            ->orderBy('belanja_rincis.id', 'desc')
            ->select('belanja_rincis.idblrinci', 'belanja_rincis.namakomponen', 'belanja_rincis.spek', 'belanja_rincis.harga_satuan')
            ->get()
            ->groupBy('idblrinci');

        $result = [];
        // Karena sudah diurutkan desc, kita ambil array pertama (terbaru) dari setiap idblrinci
        foreach ($riwayat as $idblrinci => $items) {
            $latest = $items->first();
            $result[$idblrinci] = [
                'namakomponen' => $latest->namakomponen,
                'spek' => $latest->spek,
                'harga_satuan' => $latest->harga_satuan ?? 0,
            ];
        }

        return response()->json($result);
    }
}
