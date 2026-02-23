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
            ->map(function ($group) use ($sekolah) {
                $first = $group->first();
                $pagu = $group->sum('pagu_triwulan');
                $realisasi = $group->sum('realisasi_triwulan');

                $npdPending = Npd::where('sekolah_id', $sekolah->id)
                    ->where('idbl', $first->idbl)
                    ->where('kodeakun', $first->kodeakun)
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

        // --- CEK APAKAH SUDAH ADA DATA UNTUK ANGGARAN & TRIWULAN INI ---
        $exists = Npd::where('sekolah_id', $sekolahId)
            ->where('anggaran_id', $anggaranIdAktif)
            ->where('triwulan', $triwulanAktif)
            ->exists();

        if ($exists) {
            return back()->with('error', "Gagal! Data NPD untuk Anggaran ini pada Triwulan $triwulanAktif sudah pernah disimpan sebelumnya.");
        }

        $tahun = date('Y', strtotime($request->tanggal));
        $berhasil = 0;

        // 2. Jalankan Database Transaction
        DB::beginTransaction();
        try {
            // Ambil nomor urut terakhir di tahun tersebut untuk penomoran otomatis
            $lastCount = Npd::where('sekolah_id', $sekolahId)
                ->whereYear('tanggal', $tahun)
                ->max(DB::raw("CAST(SUBSTRING_INDEX(nomor_npd, '/', 1) AS UNSIGNED)")) ?? 0;

            foreach ($request->items as $item) {
                // 3. Bersihkan Nominal & Realisasi
                $nominal = (float) preg_replace('/[^0-9]/', '', $item['nominal']);

                // Perbaikan: ambil realisasi dari hidden input di blade
                $realisasiValue = $item['realisasi_hidden'] ?? 0;
                $realisasi = (float) preg_replace('/[^0-9]/', '', $realisasiValue);

                // Hanya simpan jika nominal lebih dari 0
                if ($nominal > 0) {

                    // Validasi sisi server: Jangan melebihi pagu
                    $pagu = (float) ($item['pagu_hidden'] ?? 0);
                    if ($nominal > $pagu) {
                        continue; // Skip baris yang melanggar aturan
                    }

                    $lastCount++;
                    $nomorNpd = sprintf('%03d/NPD/%s', $lastCount, $tahun);

                    // Uraian otomatis berdasarkan nama rekening
                    $uraian = 'Pengajuan Dana '.($item['nama_rekening_hidden'] ?? '');

                    // 4. Create Data NPD
                    Npd::create([
                        'sekolah_id' => $sekolahId,
                        'nomor_npd' => $nomorNpd,
                        'tanggal' => $request->tanggal,
                        'triwulan' => $triwulanAktif, // <--- PENAMBAHAN KOLOM TRIWULAN
                        'idbl' => $item['idbl'],
                        'kodeakun' => $item['kodeakun'],
                        'uraian' => $uraian,
                        'nilai_npd' => $nominal,
                        'status' => 'diajukan',
                        'total_realisasi' => $realisasi,
                        'anggaran_id' => $sekolah->anggaran_id_aktif,
                        // Snapshot untuk history
                        'pagu_anggaran' => $pagu,
                        'sisa_anggaran' => $pagu - $nominal,
                    ]);

                    $berhasil++;
                }
            }

            // 5. Finalisasi
            if ($berhasil > 0) {
                DB::commit();

                return redirect()->route('npd.index')->with('success', "Berhasil menyimpan $berhasil pengajuan NPD untuk Triwulan $triwulanAktif.");
            } else {
                DB::rollBack();

                return back()->with('error', 'Tidak ada data yang valid untuk disimpan. Pastikan nominal diisi dan tidak melebihi pagu.');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    public function index()
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

        $listNpd = Npd::with(['kegiatan', 'korek'])
            ->where('sekolah_id', $sekolahId)
            ->where('triwulan', $triwulanAktif)
    // Gunakan DB::raw untuk menjumlahkan subtotal dan ppn
            ->withSum(['belanjas as realisasi_nota' => function ($q) use ($bulanArray) {
                $q->whereIn(DB::raw('MONTH(tanggal)'), $bulanArray);
            }], DB::raw('subtotal + ppn')) // <--- Perubahan di sini
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_npd', 'desc')
            ->paginate(20);

        $totalPengajuan = Npd::where('sekolah_id', $sekolahId)
            ->where('triwulan', $triwulanAktif)
            ->sum('nilai_npd');

        return view('npd.index', compact('listNpd', 'triwulanAktif', 'totalPengajuan'));
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
}
