<?php

namespace App\Http\Controllers;

use App\Imports\ArkasImport;
use App\Models\Arkas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ArkasController extends Controller
{
    // 1. Tampilkan Halaman Index (Hanya Kerangka HTML)
    public function index()
    {
        // Ambil list untuk filter dropdown
        $listJenisBelanja = Arkas::select('jenis_belanja')
            ->distinct()
            ->orderBy('jenis_belanja')
            ->pluck('jenis_belanja');

        return view('arkas.index', compact('listJenisBelanja'));
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
