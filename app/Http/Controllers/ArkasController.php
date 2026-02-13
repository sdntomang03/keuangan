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

        $dataRkas = Rkas::with(['akbRincis', 'kegiatan', 'korek', 'arkasChecklist'])
            ->where('anggaran_id', $anggaran->id)
            ->orderBy('idbl', 'asc')
            ->paginate(50);

        return view('arkas.index', compact('dataRkas', 'anggaran'));
    }

    public function updateIdKomponen(Request $request, $id)
    {
        $request->validate([
            'idkomponen' => 'nullable|string|max:255', // Sesuaikan validasi
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
        // Cari data checklist berdasarkan rkas_id
        $checklist = ArkasChecklist::where('rkas_id', $id)->first();

        if ($checklist) {
            // Jika sudah ada, update statusnya (kebalikannya)
            $checklist->status = ! $checklist->status;
            $checklist->save();
            $statusAkhir = $checklist->status;
        } else {
            // Jika belum ada, buat baru dengan status true
            ArkasChecklist::create([
                'rkas_id' => $id,
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

    // 1. Tampilkan Halaman Index (Hanya Kerangka HTML)
    public function komponen()
    {
        // Ambil list untuk filter dropdown
        $listJenisBelanja = Arkas::select('jenis_belanja')
            ->distinct()
            ->orderBy('jenis_belanja')
            ->pluck('jenis_belanja');

        return view('arkas.komponen', compact('listJenisBelanja'));
    }

    // 2. Endpoint AJAX: Mengembalikan JSON Data
    public function getData(Request $request)
    {
        $query = Arkas::query();

        // Filter Jenis Belanja
        if ($request->filled('jenis_belanja')) {
            $query->where('jenis_belanja', $request->jenis_belanja);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_rekening', 'like', "%{$search}%")
                    ->orWhere('nama_rekening', 'like', "%{$search}%");
            });
        }

        // Sorting
        if ($request->sort == 'termurah') {
            $query->orderBy('harga_maksimal', 'asc');
        } elseif ($request->sort == 'termahal') {
            $query->orderBy('harga_maksimal', 'desc');
        } else {
            $query->latest(); // Default
        }

        // Pagination 10 per halaman
        $data = $query->paginate(50);

        return response()->json($data);
    }

    // 3. Tampilkan Halaman Import
    public function importPage()
    {
        $listJenisBelanja = Arkas::select('jenis_belanja')->distinct()->pluck('jenis_belanja');

        return view('arkas.import', compact('listJenisBelanja'));
    }

    // 4. Proses Import Excel
    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        set_time_limit(300); // Anti timeout

        try {
            Excel::import(new ArkasImport($request->jenis_belanja_input), $request->file('file'));

            return redirect()->route('arkas.index')->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Import: '.$e->getMessage());
        }
    }
}
