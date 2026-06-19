<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Sts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StsController extends Controller
{
    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->back()->with('error', 'Anggaran aktif tidak ditemukan.');
        }

        $stss = Sts::where('anggaran_id', $anggaran->id)->orderBy('tanggal', 'desc')->get();

        return view('sts.index', compact('stss', 'anggaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string',
            'uraian' => 'required|string',
            'nominal' => 'required|numeric|min:1',
        ]);
        $anggaran = $request->anggaran_data;

        try {
            $sts = DB::transaction(function () use ($request, $anggaran) {
                $sekolah = \App\Models\Sekolah::where('id', auth()->user()->sekolah_id)->first();

                // 1. CATAT KE BKU TERLEBIH DAHULU
                $bku = \App\Models\Bku::catat(
                    $request->tanggal,
                    $request->no_bukti,
                    $request->uraian,
                    0,
                    $request->nominal,
                    null, null, $anggaran->id, null,
                    $sekolah->triwulan_aktif
                );

                // 2. SIMPAN STS DENGAN MENYISIPKAN ID BKU YANG BARU SAJA DIBUAT
                $sts = Sts::create(array_merge($request->all(), [
                    'anggaran_id' => $anggaran->id,
                    'tw' => $sekolah->triwulan_aktif,
                    'bku_id' => $bku->id,
                ]));

                return $sts;
            });

            return response()->json(['status' => 'success', 'message' => 'STS berhasil dicatat.', 'data' => $sts]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $sts = Sts::where('anggaran_id', $request->anggaran_data->id)->findOrFail($id);

        return response()->json($sts);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['tanggal' => 'required|date', 'no_bukti' => 'required|string', 'uraian' => 'required|string', 'nominal' => 'required|numeric|min:1']);
        $sts = Sts::where('anggaran_id', $request->anggaran_data->id)->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $sts) {
                $sts->update($request->only('tanggal', 'no_bukti', 'uraian', 'nominal'));
                DB::table('bkus')->where('sts_id', $sts->id)->update([
                    'tanggal' => $request->tanggal, 'no_bukti' => $request->no_bukti, 'uraian' => $request->uraian, 'kredit' => $request->nominal,
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'STS berhasil diperbarui.', 'data' => $sts]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        // Tetap pertahankan validasi anggaran_id ini karena sangat bagus untuk keamanan
        $sts = Sts::where('anggaran_id', $request->anggaran_data->id)->findOrFail($id);

        try {
            DB::transaction(function () use ($sts) {
                // 1. Cek apakah STS ini memiliki bku_id, lalu hapus data BKU-nya
                if ($sts->bku_id) {
                    DB::table('bkus')->where('id', $sts->bku_id)->delete();
                }

                // 2. Hapus data STS
                $sts->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Data STS dan catatan BKU berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
