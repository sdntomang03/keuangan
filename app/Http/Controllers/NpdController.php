<?php

namespace App\Http\Controllers;

use App\Models\Npd;
use App\Models\Rkas;
use App\Models\Sekolah;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NpdController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();
        $sekolah = $user->sekolah;
        $anggaran = $request->anggaran_data ?? $sekolah->anggaranAktif;

        if (! $anggaran) {
            return back()->with('error', 'Tidak ada anggaran aktif.');
        }

        $triwulanAktif = $sekolah->triwulan_aktif;
        $bulanArray = match ($triwulanAktif) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => []
        };

        $tahun = date('Y');
        $count = Npd::where('sekolah_id', $sekolah->id)->whereYear('tanggal', $tahun)->count() + 1;
        $nomorNpd = sprintf('%03d/NPD/%s', $count, $tahun);

        // 1. Ambil semua data RKAS
        $rawRkas = Rkas::with(['kegiatan', 'korek'])
            ->where('anggaran_id', $anggaran->id)
            ->withSum(['akbrincis as pagu_triwulan' => function ($q) use ($bulanArray) {
                $q->whereIn('bulan', $bulanArray);
            }], 'nominal')
            ->withSum(['belanjaRincis as realisasi_triwulan' => function ($q) use ($bulanArray) {
                $q->whereHas('belanja', function ($b) use ($bulanArray) {
                    $b->whereIn(DB::raw('MONTH(tanggal)'), $bulanArray);
                });
            }], 'total_bruto')
            ->get();

        // 2. Agregasi data per Kode Akun
        $listAnggaran = $rawRkas->groupBy(fn ($item) => $item->idbl.'-'.$item->kodeakun)
            ->map(function ($group) use ($sekolah, $bulanArray) { // Tambahkan $bulanArray ke closure 'use'
                $first = $group->first();
                $pagu = $group->sum('pagu_triwulan');
                $realisasi = $group->sum('realisasi_triwulan');

                // Filter NPD Pending berdasarkan Bulan di Triwulan Aktif
                $npdPending = Npd::where('sekolah_id', $sekolah->id)
                    ->where('idbl', $first->idbl)
                    ->where('kodeakun', $first->kodeakun)
                    ->whereIn(DB::raw('MONTH(tanggal)'), $bulanArray) // <-- Penyesuaian di sini
                    ->whereNotIn('status', ['ditolak'])
                    ->sum('nilai_npd');

                $sisa = $pagu - $realisasi - $npdPending;

                return (object) [
                    'idbl' => $first->idbl,
                    'kodeakun' => $first->kodeakun,
                    'kegiatan_nama' => $first->kegiatan->nama_kegiatan ?? $first->kegiatan->namagiat,
                    'korek_kode' => $first->korek->kode ?? $first->kodeakun,
                    'korek_uraian' => $first->korek->uraian ?? $first->korek->ket,
                    'pagu' => $pagu,
                    'realisasi' => $realisasi,
                    'pending' => $npdPending,
                    'sisa' => $sisa,
                ];
            })
            ->filter(fn ($item) => $item->pagu > 0)
            ->groupBy('idbl');

        return view('npd.create', compact('listAnggaran', 'nomorNpd', 'triwulanAktif', 'anggaran'));
    }

    public function storeMassal(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array',
        ]);

        $user = auth()->user();
        $sekolah = $user->sekolah; // Ambil data sekolah dari user yang login
        $sekolahId = $user->sekolah_id;
        $triwulanAktif = $sekolah->triwulan_aktif; // Ambil triwulan aktif
        $anggaranIdAktif = $sekolah->anggaran_id_aktif; // Pastikan kolom ini sesuai di model Sekolah

        $tahun = date('Y', strtotime($request->tanggal));
        $validItems = [];

        // 2. Kumpulkan baris item yang valid (Nominal > 0 dan Tidak Melebihi Pagu)
        foreach ($request->items as $item) {
            $nominal = (float) preg_replace('/[^0-9]/', '', $item['nominal']);
            $pagu = (float) ($item['pagu_hidden'] ?? 0);

            if ($nominal > 0 && $nominal <= $pagu) {
                $validItems[] = [
                    'item' => $item,
                    'nominal' => $nominal,
                    'pagu' => $pagu,
                ];
            }
        }

        if (empty($validItems)) {
            return back()->with('error', 'Tidak ada data yang valid untuk disimpan. Pastikan nominal diisi dan tidak melebihi pagu.');
        }

        // 3. Jalankan Database Transaction
        DB::beginTransaction();
        try {
            // --- MENGADOPSI LOGIKA PENOMORAN SURAT RESMI ---
            $baseNumber = 0;
            if ($sekolah->nomor_surat) {
                $parts = explode('/', $sekolah->nomor_surat);
                $baseNumber = (int) $parts[0];
            }

            // Hitung total seluruh surat untuk mendapatkan nomor urut selanjutnya
            $totalSurat = Surat::where('sekolah_id', $sekolahId)
                ->whereYear('tanggal_surat', $tahun)
                ->count();

            $nextUrut = $baseNumber + $totalSurat + 1;
            $strNoUrut = str_pad($nextUrut, 3, '0', STR_PAD_LEFT);
            $kodeSurat = $sekolah->kode_surat ?? 'NPD/'.$tahun;

            $nomorNpd = "{$strNoUrut}/{$kodeSurat}";

            // 4. BUAT INDUK SURAT NPD (Terintegrasi ke Manajemen Surat)
            $suratNpd = \App\Models\Surat::create([
                'sekolah_id' => $sekolahId,
                'belanja_id' => null, // Null karena mencakup banyak kode rekening
                'triwulan' => $triwulanAktif,
                'jenis_surat' => 'NPD',
                'nomor_surat' => $nomorNpd,
                'tanggal_surat' => $request->tanggal,
                'keterangan' => 'Permohonan NPD BOP Alokasi Dasar Triwulan '.$triwulanAktif.' tahun '.$tahun.' '.$sekolah->nama_sekolah,
            ]);

            // 5. Looping untuk menyimpan Rincian Rekening dengan 1 Nomor Surat
            foreach ($validItems as $data) {
                $item = $data['item'];
                $nominal = $data['nominal'];
                $pagu = $data['pagu'];

                $realisasiValue = $item['realisasi_hidden'] ?? 0;
                $realisasi = (float) preg_replace('/[^0-9]/', '', $realisasiValue);
                $uraian = 'Pengajuan Dana '.($item['nama_rekening_hidden'] ?? '');

                Npd::create([
                    'sekolah_id' => $sekolahId,
                    'surat_id' => $suratNpd->id,
                    'nomor_npd' => $suratNpd->nomor_surat, // <-- Integrasi dengan Nomor Surat Induk
                    'tanggal' => $request->tanggal,
                    'triwulan' => $triwulanAktif,
                    'idbl' => $item['idbl'],
                    'kodeakun' => $item['kodeakun'],
                    'uraian' => $uraian,
                    'nilai_npd' => $nominal,
                    'status' => 'diajukan',
                    'total_realisasi' => $realisasi,
                    'anggaran_id' => $anggaranIdAktif,
                    'pagu_anggaran' => $pagu,
                    'sisa_anggaran' => $pagu - $nominal,
                ]);
            }

            DB::commit();

            return redirect()->route('npd.index')->with('success', 'Berhasil menyimpan '.count($validItems)." pengajuan NPD dengan Nomor Surat $nomorNpd.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = $user->sekolah_id;
        $triwulanAktif = $user->sekolah->triwulan_aktif;

        // Tentukan filter bulan berdasarkan triwulan
        $bulanArray = match ($triwulanAktif) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => []
        };

        // 1. AMBIL DATA DROPDOWN (Sekarang mengambil surat_id dan nomor_npd)
        $listNomorNpd = Npd::where('sekolah_id', $sekolahId)
            ->where('triwulan', $triwulanAktif)
            ->whereNotNull('surat_id') // Hanya ambil data yang sudah memiliki relasi surat_id
            ->select('surat_id', 'nomor_npd')
            ->distinct()
            ->get(); // Menggunakan get() karena kita mengambil 2 kolom

        // 2. Inisialisasi Query Dasar
        $query = Npd::with(['kegiatan', 'korek'])
            ->where('sekolah_id', $sekolahId)
            ->withSum(['belanjas as realisasi_nota' => function ($q) use ($bulanArray) {
                $q->whereIn(DB::raw('MONTH(tanggal)'), $bulanArray);
            }], DB::raw('subtotal + ppn'));

        // 3. Terapkan Filter (BERDASARKAN SURAT_ID)
        if ($request->filled('surat_id')) {
            $query->where('surat_id', $request->surat_id);
        } else {
            // Default: Tampilkan semua di Triwulan Aktif
            $query->where('triwulan', $triwulanAktif);
        }

        // 4. Hitung Total Pengajuan
        $totalPengajuan = (clone $query)->sum('nilai_npd');

        // 5. Eksekusi Paginate
        $listNpd = $query->orderBy('tanggal', 'desc')
            ->orderBy('nomor_npd', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('npd.index', compact('listNpd', 'triwulanAktif', 'totalPengajuan', 'listNomorNpd'));
    }

    public function storeSurat(Request $request)
    {
        $user = Auth::user();
        $sekolah = Sekolah::find($user->sekolah_id);
        if (! $sekolah) {
            return back()->with('error', 'Data sekolah tidak ditemukan.');
        }

        $request->validate([
            'tanggal_npd' => 'required|date',
            'jenis_surat' => 'required|in:NPD,STS',
        ]);

        // 1. Simpan identitas ke model Surat
        $surat = Surat::create([
            'nomor_surat' => 'DRAFT',
            'tanggal_surat' => $request->tanggal_npd,
            'jenis_surat' => $request->jenis_surat, // Jika ada kolom pembeda tipe
            'sekolah_id' => auth()->user()->sekolah_id,
            'triwulan' => $sekolah->triwulan_aktif,
            'belanja_id' => null, // Karena ini talangan, tidak terkait langsung ke satu belanja
        ]);

        return back()->with('success', 'Surat berhasil disimpan.');
    }

    public function destroyTriwulan()
    {
        $user = Auth::user();
        $sekolah = $user->sekolah;
        $triwulanAktif = $sekolah->triwulan_aktif;
        $anggaranId = $sekolah->anggaran_id_aktif;

        // 1. CARI DATA TERLEBIH DAHULU (GET)
        $npdRecords = Npd::where('sekolah_id', $sekolah->id)
            ->where('anggaran_id', $anggaranId)
            ->where('triwulan', $triwulanAktif)
            ->get();

        // 2. CEK APAKAH KOSONG?
        if ($npdRecords->isEmpty()) {
            return back()->with('error', "Tidak ada data NPD yang ditemukan untuk dihapus pada Triwulan $triwulanAktif.");
        }

        // 3. JIKA ADA, HITUNG JUMLAHNYA LALU HAPUS
        DB::beginTransaction();
        try {
            $jumlahDihapus = $npdRecords->count();

            // Eksekusi hapus berdasarkan ID yang sudah pasti ditemukan
            $idsTobeDeleted = $npdRecords->pluck('id');
            Npd::whereIn('id', $idsTobeDeleted)->delete();

            DB::commit();

            return redirect()->route('npd.index')
                ->with('success', "Berhasil menghapus $jumlahDihapus data pengajuan NPD pada Triwulan $triwulanAktif.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan sistem saat menghapus data: '.$e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $sekolah = $user->sekolah;
        $triwulanAktif = $sekolah->triwulan_aktif;

        // Filter bulan berdasarkan triwulan
        $bulanArray = match ($triwulanAktif) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => []
        };

        // 1. Inisialisasi Query Dasar
        $query = \App\Models\Npd::with(['kegiatan', 'korek'])
            ->where('sekolah_id', $sekolah->id)
            ->where('triwulan', $triwulanAktif)
            ->withSum(['belanjas as realisasi_nota' => function ($q) use ($bulanArray) {
                $q->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(tanggal)'), $bulanArray);
            }], \Illuminate\Support\Facades\DB::raw('subtotal + ppn'));

        // 2. Terapkan Filter surat_id jika ada
        if ($request->filled('surat_id')) {
            $query->where('surat_id', $request->surat_id);
        }

        // 3. Eksekusi Query
        $listNpd = $query->orderBy('tanggal', 'desc')->get();

        // 4. Cari Nomor Surat untuk Judul Excel dan Nama File
        $nomorNpdDifilter = null;
        if ($request->filled('surat_id')) {
            $surat = \App\Models\Surat::find($request->surat_id);
            $nomorNpdDifilter = $surat ? $surat->nomor_surat : null;
        }

        // 5. Buat Nama File Dinamis
        $filterName = $nomorNpdDifilter ? str_replace('/', '_', $nomorNpdDifilter) : 'SEMUA';
        $fileName = "NPD_{$sekolah->nama_sekolah}_TW_{$triwulanAktif}_{$filterName}_".date('Ymd_His').'.xlsx';

        // 6. Return Download Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\NpdExport($listNpd, $triwulanAktif, $sekolah->nama_sekolah, $nomorNpdDifilter),
            $fileName
        );
    }
}
