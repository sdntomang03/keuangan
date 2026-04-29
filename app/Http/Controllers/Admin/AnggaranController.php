<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggaran;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnggaranController extends Controller
{
    public function index()
    {
        // Menampilkan halaman generate
        return view('admin.anggaran.index');
    }

    public function generate(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'tahun' => 'required|digits:4|integer',
        ], [
            'tahun.required' => 'Tahun anggaran wajib diisi.',
            'tahun.digits' => 'Format tahun harus 4 digit (contoh: 2025).',
        ]);

        $tahun = $request->tahun;

        try {
            $sekolahList = Sekolah::all();

            if ($sekolahList->isEmpty()) {
                return redirect()->back()->with('error', 'Data sekolah tidak ditemukan di database.');
            }

            // Definisikan jenis anggaran yang ingin dibuat
            $jenisAnggaran = [
                ['singkatan' => 'bos', 'nama' => 'Bantuan Operasional Satuan Pendidikan '],
                ['singkatan' => 'bop', 'nama' => 'Bantuan Operasional Pendidikan '],
            ];

            $sekolahBaruDibuat = 0;
            $sekolahSudahAda = [];

            DB::beginTransaction();

            foreach ($sekolahList as $sekolah) {
                $isSkipped = false;
                $adaYangDibuat = false;

                foreach ($jenisAnggaran as $item) {
                    // Cek apakah anggaran sekolah tsb di tahun tsb sudah ada
                    $exists = Anggaran::where('sekolah_id', $sekolah->id)
                        ->where('tahun', $tahun)
                        ->where('singkatan', $item['singkatan'])
                        ->exists();

                    if (! $exists) {
                        Anggaran::create([
                            'tahun' => $tahun,
                            'singkatan' => $item['singkatan'],
                            'nama_anggaran' => $item['nama'].$tahun,
                            'is_aktif' => false,
                            'sekolah_id' => $sekolah->id,
                        ]);
                        $adaYangDibuat = true;
                    } else {
                        $isSkipped = true;
                    }
                }

                if ($adaYangDibuat) {
                    $sekolahBaruDibuat++;
                }

                if ($isSkipped) {
                    $sekolahSudahAda[] = $sekolah->nama_sekolah ?? 'Sekolah ID '.$sekolah->id;
                }
            }

            DB::commit();

            if ($sekolahBaruDibuat > 0) {
                return redirect()->back()
                    ->with('success', "Berhasil menambahkan anggaran tahun $tahun untuk $sekolahBaruDibuat sekolah.")
                    ->with('skipped_schools', $sekolahSudahAda);
            } else {
                return redirect()->back()
                    ->with('info', "Anggaran tahun $tahun sudah ada untuk semua sekolah.")
                    ->with('skipped_schools', $sekolahSudahAda);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }
}
