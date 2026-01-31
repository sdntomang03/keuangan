<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Penerimaan;
use App\Models\Sekolah;
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
                $sekolah = Sekolah::where('id', auth()->user()->sekolah_id)->first();
                // 2. Simpan data penerimaan dengan ID anggaran & User ID

                $p = Penerimaan::create([
                    'tanggal' => $request->tanggal,
                    'no_bukti' => $request->no_bukti,
                    'uraian' => $request->uraian,
                    'nominal' => $request->nominal,
                    'anggaran_id' => $anggaran->id,
                    'tw' => $sekolah->triwulan_aktif,
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
                    $anggaran->id, // anggaran_id
                    $p->id,      // penerimaan_id
                    $sekolah->triwulan_aktif,
                );
            });

            return redirect()->back()->with('success', "Penerimaan {$anggaran->singkatan} berhasil dicatat.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        // Ambil anggaran_id dari middleware/request
        $anggaran = $request->anggaran_data;

        // Cari data yang ID-nya cocok DAN berada dalam lingkup anggaran aktif
        $penerimaan = Penerimaan::where('anggaran_id', $anggaran->id)
            ->findOrFail($id);

        return response()->json($penerimaan);
    }

    // app/Http/Controllers/PenerimaanController.php

    public function update(Request $request, $id) // Urutan: Request dulu, baru ID
    {
        $anggaran = $request->anggaran_data;

        // Tambahkan pengaman: pastikan data yang diedit milik anggaran sekolah yang aktif
        $penerimaan = Penerimaan::where('anggaran_id', $anggaran->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string',
            'uraian' => 'required|string',
            'nominal' => 'required|numeric',
        ]);

        DB::transaction(function () use ($penerimaan, $validated) {
            $penerimaan->update($validated);

            // Sinkronkan ke BKU
            DB::table('bkus')->where('penerimaan_id', $penerimaan->id)->update([
                'tanggal' => $validated['tanggal'],
                'no_bukti' => $validated['no_bukti'],
                'uraian' => $validated['uraian'],
                'debit' => $validated['nominal'],
            ]);
        });

        return back()->with('success', 'Data berhasil diperbarui.');
    }
}
