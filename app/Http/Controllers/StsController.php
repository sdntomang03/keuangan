<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Sekolah;
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
        $request->validate(['tanggal' => 'required|date', 'no_bukti' => 'required|string', 'uraian' => 'required|string', 'nominal' => 'required|numeric|min:1']);
        $anggaran = $request->anggaran_data;

        try {
            $sts = DB::transaction(function () use ($request, $anggaran) {
                $sekolah = Sekolah::where('id', auth()->user()->sekolah_id)->first();
                $sts = Sts::create(array_merge($request->all(), [
                    'anggaran_id' => $anggaran->id, 'tw' => $sekolah->triwulan_aktif,
                ]));

                Bku::catat($sts->tanggal, $sts->no_bukti, $sts->uraian, 0, $sts->nominal, null, null, $anggaran->id, null, $sekolah->triwulan_aktif, null, $sts->id);

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
        $sts = Sts::where('anggaran_id', $request->anggaran_data->id)->findOrFail($id);

        try {
            DB::transaction(function () use ($sts) {
                DB::table('bkus')->where('sts_id', $sts->id)->delete();
                $sts->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Data STS berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
