<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string',
            'uraian' => 'required|string',
            'nominal' => 'required|numeric',
        ]);

        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->back()->with('error', 'Anggaran aktif tidak ditemukan.');
        }

        try {
            DB::transaction(function () use ($request, $anggaran) {
                // 2. Simpan data penerimaan dengan ID anggaran & User ID
                $p = Penerimaan::create([
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'uraian' => $request->uraian,
                    'nominal' => $request->nominal,
                    'anggaran_id' => $anggaran->id,
                    'user_id' => auth()->id(),
                ]);

                // 3. Masuk ke DEBIT BKU dengan anggaran_id (Parameter ke-8)
                // Bku::catat($tanggal, $no_bukti, $uraian, $debit, $kredit, $belanjaId, $pajakId, $anggaranId)
                Bku::catat(
                    $p->tanggal,
                    $p->no_bukti,
                    $p->uraian,
                    $p->nominal, // Debit
                    0,           // Kredit
                    null,        // belanja_id
                    null,        // pajak_id
                    $anggaran->id // anggaran_id
                );
            });

            return redirect()->back()->with('success', "Penerimaan {$anggaran->singkatan} berhasil dicatat.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
