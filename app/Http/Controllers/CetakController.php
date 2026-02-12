<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Jika ingin mengambil kode rekening dari transaksi

class CetakController extends Controller
{
    public function cetakCover(Request $request)
    {
        // 1. Ambil Data Sekolah dari User Login
        $user = Auth::user();
        $sekolah = $user->sekolah; // Pastikan relasi user->sekolah ada
        $anggaran = $sekolah->anggaranAktif; // Mengambil anggaran yang sedang aktif

        if (! $sekolah || ! $anggaran) {
            return back()->with('error', 'Data Sekolah atau Anggaran Aktif tidak ditemukan.');
        }

        // 2. Tentukan Triwulan (Dari Input Request atau Default)
        $tw = $request->input('tw', '1'); // Default TW 1 jika tidak ada
        $labelTriwulan = match ($tw) {
            '1' => 'TRIWULAN I',
            '2' => 'TRIWULAN II',
            '3' => 'TRIWULAN III',
            '4' => 'TRIWULAN IV',
            default => 'TAHUNAN',
        };

        // 3. Susun Data untuk View
        $data = [
            'nama_sekolah' => strtoupper($sekolah->nama_sekolah),
            // Gabungkan alamat jika terpisah kolom, atau ambil langsung jika satu kolom
            'alamat' => $sekolah->alamat.', '.($sekolah->kota ?? ''),
            'sumber_dana' => strtoupper($anggaran->nama_anggaran), // Misal: BOSP REGULER
            'tahun' => $anggaran->tahun,
            'triwulan' => $labelTriwulan,
            // Cek jika ada file logo, jika tidak pakai placeholder
            'logo_url' => $sekolah->logo
                                ? asset('storage/'.$sekolah->logo)
                                : 'https://upload.wikimedia.org/wikipedia/commons/b/b2/Garuda_Pancasila.png',
            'nomor_spj' => '-', // Bisa diambil dari database jika ada kolom no_spj
        ];

        // 4. Daftar Halaman Pembatas (Statis + Dinamis Kode Rekening)
        $halaman_pembatas = [
            '', // Cover Depan (Kosong)
            'REKENING KORAN',
            'BUKU KAS UMUM',
            'BUKU KAS BANK',
            'REKAP & RINCIAN PAJAK',
            'FORM MONITORING TRIWULAN',
            'FORM MONITORING TRANSFER',
            'SPTJM',
            'BUKTI PENGEMBALIAN',
            'BAP KAS',
            'FORM 1A - 1E (BOP)',
        ];

        // [OPSIONAL] Jika ingin mengambil Kode Rekening yang dipakai saja secara otomatis dari database:

        $kodeRekeningDipakai = Belanja::with('rincis.korek')
            ->where('sekolah_id', $sekolah->id)
            ->where('anggaran_id', $anggaran->id)
            // Tambahkan filter bulan berdasarkan Triwulan disini jika perlu
            ->get()
            ->flatMap(fn ($b) => $b->rincis)
            ->map(fn ($r) => $r->korek->kode_rekening.'<br><span style="font-size:14pt">'.$r->korek->ket.'</span>')
            ->unique();

        foreach ($kodeRekeningDipakai as $kode) {
            $halaman_pembatas[] = $kode;
        }

        return view('cetak_cover', compact('data', 'halaman_pembatas'));
    }
}
