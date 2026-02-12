<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Belanja;
use App\Models\BelanjaRinci;
use App\Models\Bku;
use App\Models\DasarPajak;
use App\Models\Kegiatan;
use App\Models\Rekanan;
use App\Models\Rkas;
use App\Models\Sekolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BelanjaController extends Controller
{
    // BelanjaController.php
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

        return DB::table('rkas')
            ->join('koreks', 'rkas.kodeakun', '=', 'koreks.id')
            ->where('rkas.anggaran_id', $anggaran->id) // Filter berdasarkan ID Anggaran Aktif
            ->where('rkas.idbl', $request->idbl)
            ->select(
                'koreks.id as koderekening',
                'koreks.uraian_singkat as namarekening'
            )
            ->distinct()
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

        $tw = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $bulanRange = match ($tw) {
            1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12],
            default => range(1, 12)
        };

        // Mulai Query Dasar
        $query = Rkas::join('akb_rincis', 'rkas.idblrinci', '=', 'akb_rincis.idblrinci')
            ->where('rkas.anggaran_id', $anggaran->id)
            ->where('rkas.idbl', $request->idbl)
            ->where('rkas.kodeakun', $request->koderekening)
            ->whereIn('akb_rincis.bulan', $bulanRange);

        // --- LOGIKA FILTER KETERANGAN ---
        // Jika user mengirim 'keterangan' DAN isinya BUKAN 'ALL', maka filter spesifik.
        // Jika isinya 'ALL' atau kosong, maka filter ini dilewati (tampilkan semua).
        if ($request->filled('keterangan') && $request->keterangan !== 'ALL') {
            $query->where('rkas.keterangan', $request->keterangan);
        }
        // -------------------------------

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

        // Ambil data sekolah dari user login
        $user = auth()->user();
        $sekolah = Sekolah::find($user->sekolah_id);

        if (! $anggaran || ! $sekolah) {
            return response()->json([]);
        }

        // Query mencari keterangan yang unik (DISTINCT)
        $data = DB::table('rkas')
            ->where('anggaran_id', $anggaran->id)
            ->where('idbl', $request->idbl) // Filter Kegiatan
            ->where('kodeakun', $request->koderekening) // Filter Rekening

            // Filter agar yang kosong tidak ikut muncul
            ->whereNotNull('keterangan')
            ->where('keterangan', '!=', '')
            ->where('keterangan', '!=', '-')

            ->select('keterangan')
            ->distinct() // PENTING: Supaya tidak ada keterangan kembar
            ->orderBy('keterangan', 'asc')
            ->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'tanggal' => 'required|date',
            // PERBAIKAN: Hapus ignore($id) karena ini Create Data Baru
            'no_bukti' => 'required|string|unique:belanjas,no_bukti',
            'rekanan_id' => 'required',
            'items' => 'required|array|min:1',
            'rincian' => 'nullable|string',
            // Validasi struktur item agar aman
            'items.*.volume' => 'required|numeric|min:0',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ], [
            'no_bukti.unique' => 'Nomor Bukti ini sudah digunakan pada transaksi lain.',
            'items.required' => 'Harap masukkan minimal satu rincian belanja.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
        ]);

        // 2. Ambil Data dari Middleware & Relasi Sekolah
        $anggaran = $request->anggaran_data;
        $sekolah = Sekolah::where('id', auth()->user()->sekolah_id)->first();

        if (! $anggaran || ! $sekolah) {
            return back()->with('error', 'Data Anggaran atau Sekolah tidak ditemukan.');
        }

        // Ambil Triwulan Aktif dari Model Sekolah
        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $mappingBulan = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
        ];
        $bulanDicheck = $mappingBulan[$twAktif] ?? range(1, 12);
        try {
            return DB::transaction(function () use ($request, $bulanDicheck, $twAktif, $anggaran) {

                // --- VALIDASI PAGU KOMPONEN ---
                foreach ($request->items as $item) {
                    $subtotal = $item['volume'] * $item['harga_satuan'];

                    $totalBrutoInput = ($request->ppn > 0)
                    ? $subtotal * 1.11
                    : $subtotal;

                    // Ambil pagu berdasarkan anggaran_id aktif
                    $totalPaguAnggaran = DB::table('akb_rincis')
                        ->where('idblrinci', $item['idblrinci'])
                        ->whereIn('bulan', $bulanDicheck)
                        ->sum('nominal');

                    // Hitung belanja yang sudah dilakukan (pada anggaran_id yang sama)
                    $sudahDibelanjakan = DB::table('belanja_rincis')
                        ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
                        ->where('belanja_rincis.idblrinci', $item['idblrinci'])
                        ->where('belanjas.anggaran_id', $anggaran->id)
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

                // 3. Simpan Header Belanja (Tambahkan anggaran_id)
                $belanja = Belanja::create([
                    'user_id' => auth()->id(),
                    'anggaran_id' => $anggaran->id, // PENTING: Agar data terkunci di BOS/BOP & Tahun terkait
                    'rekanan_id' => $request->rekanan_id,
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'rincian' => $request->rincian,
                    'uraian' => $request->uraian,
                    'subtotal' => $request->sub_total,
                    'ppn' => $request->ppn ?? 0,
                    'pph' => $request->pph ?? 0,
                    'transfer' => $request->transfer,
                    'idbl' => $request->idbl,
                    'kodeakun' => $request->kodeakun,
                    'tw' => $twAktif,
                    'status' => 'draft',
                ]);
                $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 0;
                // Konversi ke desimal (contoh: 11 menjadi 0.11)
                $multiplier = $persenPpn / 100;
                // 4. Simpan Rincian
                foreach ($request->items as $item) {
                    $volume = $item['volume'];
                    $hargaSatuan = $item['harga_satuan'];
                    $subtotal = $volume * $hargaSatuan;

                    // Logika Bruto Dasar
                    // Jika PPN tidak 0, maka subtotal dikali persen PPN. Jika 0, tetap subtotal.
                    $brutoDasar = ($request->ppn != 0)
                                  ? $subtotal * (1 + $multiplier)
                                  : $subtotal; // Atau $subtotal jika bruto dasar maksudnya adalah nilai sebelum pajak

                    $belanja->rincis()->create([
                        'idblrinci' => $item['idblrinci'],
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $item['spek'] ?? '-',
                        'harga_satuan' => $item['harga_satuan'],
                        'harga_penawaran' => 0,
                        'volume' => $item['volume'],
                        'total_bruto' => $brutoDasar,
                        'bulan' => Carbon::parse($request->tanggal)->month,
                    ]);
                }

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

        // 3. Query Dasar
        $query = Belanja::with(['rekanan', 'kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id);

        // 4. FILTER TW (LOGIKA BARU)
        // Jika di URL ada ?tw=1, maka filter ditambahkan. Jika tidak, tampilkan semua.
        if ($request->filled('tw')) {
            $query->where('tw', $request->tw);
        }

        // 5. Eksekusi Query dengan Pagination
        $belanjas = $query->orderBy('tanggal', 'desc')
            ->paginate(10)
            ->withQueryString(); // PENTING: Agar filter tidak hilang saat klik halaman 2

        return view('belanja.index', compact('belanjas', 'anggaran', 'sekolah'));
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

    public function post($id, Request $request) // Tambahkan Request untuk akses middleware
    {
        // 1. Cari data belanja beserta pajaknya dengan proteksi user_id
        $belanja = Belanja::with('rekanan', 'pajaks.masterPajak')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Ambil anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if ($belanja->status === 'posted') {
            return back()->with('error', 'Transaksi ini sudah diposting sebelumnya.');
        }
        DB::transaction(function () use ($belanja, $anggaran) {
            $sekolah = auth()->user()->sekolah;
            // 2. Catat Transaksi Belanja Utama ke BKU
            // Pastikan model Bku::catat menerima parameter anggaran_id
            Bku::catat(
                now()->today()->format('Y-m-d'),
                $belanja->no_bukti,
                'Dibayar '.$belanja->uraian.' dari '.$sekolah->nama_sekolah.' kepada '.$belanja->rekanan->nama_rekanan,
                0,                                  // debit
                $belanja->subtotal + $belanja->ppn, // kredit
                $belanja->id,                       // belanja_id
                null,                               // pajak_id
                $anggaran->id,                       // PENTING: anggaran_id dari middleware
                null,
                $belanja->tw
            );

            // 3. Catat Tiap Pajak yang ada di Belanja tersebut
            foreach ($belanja->pajaks as $pajak) {
                Bku::catat(
                    now()->today()->format('Y-m-d'),
                    $belanja->no_bukti,
                    'Diterima '.$pajak->masterPajak->nama_pajak.' '.$belanja->uraian.' dari '.$sekolah->nama_sekolah.' kepada '.$belanja->rekanan->nama_rekanan,
                    $pajak->nominal, // debit (penerimaan pajak)
                    0,               // kredit
                    $belanja->id,    // referensi induk belanja
                    $pajak->id,      // pajak_id rincian
                    $anggaran->id,   // PENTING: anggaran_id
                    null,
                    $belanja->tw

                );

                // Tandai pajak sudah diterima
                $pajak->update(['is_terima' => true]);
            }

            // 4. Update status belanja
            $belanja->update(['status' => 'posted']);
        });

        return back()->with('success', "Transaksi {$anggaran->singkatan} berhasil dicatat di BKU.");
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
                'id_master' => $p->id_master_pajak, // Sesuaikan dengan nama kolom di DB
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

    public function update(Request $request, $id)
    {

        $belanja = Belanja::findOrFail($id);

        // 1. Validasi Input (No bukti unik kecuali untuk ID ini sendiri)
        $request->validate([
            'tanggal' => 'required|date',
            // Validasi Unique yang mengabaikan ID ini sendiri
            'no_bukti' => [
                'required',
                'string',
                Rule::unique('belanjas', 'no_bukti')->ignore($id),
            ],
            'rekanan_id' => 'required',
            'items' => 'required|array|min:1',
            'rincian' => 'nullable|string',
        ], [
            // --- BAGIAN INI YANG PERLU ANDA TAMBAHKAN ---
            // Format: 'nama_field.nama_rule' => 'Pesan Error'
            'no_bukti.unique' => 'Nomor Bukti ini sudah digunakan pada transaksi lain. Mohon ganti dengan nomor lain.',
            'items.required' => 'Harap masukkan minimal satu rincian belanja.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
        ]);

        $anggaran = $request->anggaran_data;
        $sekolah = Sekolah::where('id', auth()->user()->sekolah_id)->first();

        if (! $anggaran || ! $sekolah) {
            return back()->with('error', 'Data Anggaran atau Sekolah tidak ditemukan.');
        }

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $mappingBulan = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];
        $bulanDicheck = $mappingBulan[$twAktif] ?? range(1, 12);

        try {
            return DB::transaction(function () use ($request, $bulanDicheck, $anggaran, $belanja) {

                // --- VALIDASI PAGU (DENGAN EXCLUDE TRANSAKSI INI) ---
                foreach ($request->items as $item) {
                    $subtotal = $item['volume'] * $item['harga_satuan'];
                    $totalBrutoInput = ($request->ppn > 0)
                    ? $subtotal * 1.11
                    : $subtotal;

                    $totalPaguAnggaran = DB::table('akb_rincis')
                        ->where('idblrinci', $item['idblrinci'])
                        ->whereIn('bulan', $bulanDicheck)
                        ->sum('nominal');

                    // Hitung belanja lain (Exclude ID belanja yang sedang diedit)
                    $sudahDibelanjakan = DB::table('belanja_rincis')
                        ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
                        ->where('belanja_rincis.idblrinci', $item['idblrinci'])
                        ->where('belanjas.anggaran_id', $anggaran->id)
                        ->where('belanjas.id', '!=', $belanja->id) // PENTING: Abaikan diri sendiri
                        ->sum('total_bruto');

                    $sisaPaguTersedia = $totalPaguAnggaran - $sudahDibelanjakan;

                    if ($totalBrutoInput > $sisaPaguTersedia) {
                        throw new \Exception('Pagu Tidak Cukup untuk: '.$item['namakomponen']);
                    }
                }

                // 2. Update Header Belanja
                $belanja->update([
                    'rekanan_id' => $request->rekanan_id,
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'uraian' => $request->uraian,
                    'rincian' => $request->rincian,
                    'subtotal' => $request->sub_total,
                    'ppn' => $request->ppn ?? 0,
                    'pph' => $request->pph ?? 0,
                    'transfer' => $request->transfer,
                ]);

                // 3. Hapus Rincian & Pajak Lama (Fresh Update)
                $belanja->rincis()->delete();
                $belanja->pajaks()->delete();

                // 4. Simpan Rincian Baru
                $persenPpn = DasarPajak::where('nama_pajak', 'PPN')->value('persen') ?? 11;
                $multiplier = $persenPpn / 100;

                foreach ($request->items as $item) {
                    $subtotalItem = $item['volume'] * $item['harga_satuan'];
                    $brutoDasar = ($request->ppn != 0) ? $subtotalItem * (1 + $multiplier) : $subtotalItem;

                    $belanja->rincis()->create([
                        'idblrinci' => $item['idblrinci'],
                        'namakomponen' => $item['namakomponen'],
                        'spek' => $item['spek'] ?? '-',
                        'harga_satuan' => $item['harga_satuan'],
                        'volume' => $item['volume'],
                        'total_bruto' => $brutoDasar,
                        'bulan' => \Carbon\Carbon::parse($request->tanggal)->month,
                    ]);
                }

                // 5. Simpan Pajak Baru
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

                return redirect()->route('belanja.index')->with('success', 'Perubahan BKU Berhasil Disimpan!');
            });
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

        return redirect()->route('surat.index', $id)->with('success', 'Data BAST dan Harga Penawaran berhasil disimpan.');
    }

    public function duplicate(Request $request, $id)
    {
        // 1. Ambil Data Asli beserta relasinya (Rincian & Pajak)
        $original = Belanja::with(['rincis', 'pajaks'])->findOrFail($id);

        // 2. Siapkan Data Pendukung untuk Cek Pagu (Sekolah & Anggaran)
        // Asumsi: Anda punya middleware yang inject anggaran_data, atau ambil manual
        $anggaran = $request->anggaran_data ?? Anggaran::find($original->anggaran_id);
        $sekolah = Sekolah::find($anggaran->sekolah_id);
        // Tentukan Triwulan/Bulan aktif untuk pengecekan Pagu
        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $mappingBulan = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];
        $bulanDicheck = $mappingBulan[$twAktif] ?? range(1, 12);

        DB::beginTransaction();
        try {
            // --- A. VALIDASI PAGU DULU ---
            foreach ($original->rincis as $item) {
                // Hitung total pagu di anggaran
                $totalPagu = DB::table('akb_rincis')
                    ->where('idblrinci', $item->idblrinci)
                    ->whereIn('bulan', $bulanDicheck)
                    ->sum('nominal');

                // Hitung yang sudah terpakai (termasuk belanja asli yang mau diduplikat)
                $terpakai = DB::table('belanja_rincis')
                    ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
                    ->where('belanja_rincis.idblrinci', $item->idblrinci)
                    ->where('belanjas.anggaran_id', $anggaran->id)
                    ->sum('total_bruto');

                // Cek apakah muat jika ditambah duplikat ini?
                // Note: $item->total_bruto adalah nilai belanja yang mau diduplikat
                if (($terpakai + $item->total_bruto) > $totalPagu) {
                    throw new \Exception("Gagal Duplikat! Pagu untuk '{$item->namakomponen}' tidak mencukupi.");
                }
            }

            // --- B. PROSES DUPLIKASI HEADER ---
            // replicate() menyalin model tanpa ID dan timestamp
            $newBelanja = $original->replicate();

            // Generate No Bukti Unik Sementara
            // Contoh: BKU-001 menjadi BKU-001-COPY-17092344 (Timestamp)
            $newBelanja->no_bukti = $original->id.'-COPY-'.time();

            // Opsional: Set tanggal ke hari ini atau biarkan sama dengan asli
            $newBelanja->tanggal = now();

            $newBelanja->created_at = now();
            $newBelanja->updated_at = now();
            $newBelanja->save();

            // --- C. PROSES DUPLIKASI RINCIAN (ITEMS) ---
            foreach ($original->rincis as $rinci) {
                $newRinci = $rinci->replicate();
                $newRinci->belanja_id = $newBelanja->id; // Link ke ID baru
                $newRinci->created_at = now();
                $newRinci->updated_at = now();
                $newRinci->save();
            }

            // --- D. PROSES DUPLIKASI PAJAK ---
            foreach ($original->pajaks as $pajak) {
                $newPajak = $pajak->replicate();
                $newPajak->belanja_id = $newBelanja->id; // Link ke ID baru
                $newPajak->created_at = now();
                $newPajak->updated_at = now();
                $newPajak->save();
            }

            DB::commit();

            // Redirect ke halaman Edit dari data baru tersebut agar user bisa langsung ubah No Bukti
            return redirect()->route('belanja.edit', $newBelanja->id)
                ->with('success', 'Transaksi berhasil diduplikat! Silakan sesuaikan Tanggal dan Nomor Bukti.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}
