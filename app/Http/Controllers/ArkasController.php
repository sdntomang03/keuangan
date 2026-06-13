<?php

namespace App\Http\Controllers;

use App\Imports\ArkasImport;
use App\Models\Arkas;
use App\Models\ArkasChecklist;
use App\Models\Rkas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ArkasController extends Controller
{
    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->back()->with('error', 'Pilih Anggaran dulu.');
        }

        $sekolahId = auth()->user()->sekolah_id;

        // Data RKAS difilter lewat anggaran_id.
        // Sedangkan Checklist difilter lewat sekolah_id di dalam relasi (closure).
        $dataRkas = Rkas::with(['akbRincis', 'kegiatan', 'korek', 'arkasChecklist' => function ($q) use ($sekolahId) {
            $q->where('sekolah_id', $sekolahId);
        }])
            ->where('anggaran_id', $anggaran->id) // Ini sudah cukup untuk mengunci data ke 1 sekolah
            ->orderBy('idbl', 'asc')
            ->paginate(50);

        return view('arkas.index', compact('dataRkas', 'anggaran'));
    }

    public function updateIdKomponen(Request $request, $id)
    {
        $request->validate([
            'idkomponen' => 'nullable|string|max:255',
        ]);

        $rkas = Rkas::findOrFail($id);
        $rkas->idkomponen = $request->idkomponen;
        $rkas->save();

        return response()->json([
            'success' => true,
            'message' => 'ID Komponen berhasil diperbarui.',
            'value' => $rkas->idkomponen,
        ]);
    }

    public function toggleStatusArkas($id)
    {
        $sekolahId = auth()->user()->sekolah_id;

        // Cari checklist spesifik milik sekolah ini
        $checklist = ArkasChecklist::where('rkas_id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();

        if ($checklist) {
            $checklist->status = ! $checklist->status;
            $checklist->save();
            $statusAkhir = $checklist->status;
        } else {
            ArkasChecklist::create([
                'rkas_id' => $id,
                'sekolah_id' => $sekolahId, // Checklist mengikat ke sekolah
                'status' => true,
            ]);
            $statusAkhir = true;
        }

        return response()->json([
            'success' => true,
            'status' => $statusAkhir,
            'message' => $statusAkhir ? 'Ditandai: Sudah Input ARKAS' : 'Ditandai: Belum Input',
        ]);
    }

    // =========================================================================
    // AREA DATA MASTER ARKAS (GLOBAL UNTUK SEMUA SEKOLAH)
    // =========================================================================

    public function komponen()
    {
        // Query global tanpa where('sekolah_id')
        $listJenisBelanja = Arkas::select('jenis_belanja')
            ->distinct()
            ->orderBy('jenis_belanja')
            ->pluck('jenis_belanja');

        return view('arkas.komponen', compact('listJenisBelanja'));
    }

    public function getData(Request $request)
    {
        // Query global tanpa di-limit sekolah_id
        $query = Arkas::query();

        if ($request->filled('jenis_belanja')) {
            $query->where('jenis_belanja', $request->jenis_belanja);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_rekening', 'like', "%{$search}%")
                    ->orWhere('nama_rekening', 'like', "%{$search}%");
            });
        }

        if ($request->sort == 'termurah') {
            $query->orderBy('harga_maksimal', 'asc');
        } elseif ($request->sort == 'termahal') {
            $query->orderBy('harga_maksimal', 'desc');
        } else {
            $query->latest();
        }

        $data = $query->paginate(50);

        return response()->json($data);
    }

    public function importPage()
    {
        // Query global
        $listJenisBelanja = Arkas::select('jenis_belanja')->distinct()->pluck('jenis_belanja');

        return view('arkas.import', compact('listJenisBelanja'));
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        set_time_limit(300);

        try {
            // Import berjalan secara global tanpa melempar sekolahId
            Excel::import(new ArkasImport($request->jenis_belanja_input), $request->file('file'));

            return redirect()->route('arkas.index')->with('success', 'Data Master ARKAS berhasil diimport secara global!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Import: '.$e->getMessage());
        }
    }
}
