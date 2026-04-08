<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\KorekUpdateImport;
use App\Models\Korek;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class KorekController extends Controller
{
    /**
     * Menampilkan daftar Kode Rekening (Master Data)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $koreks = Korek::when($search, function ($query, $search) {
            return $query->where('kode', 'like', "%{$search}%")
                ->orWhere('uraian_singkat', 'like', "%{$search}%")
                ->orWhere('ket', 'like', "%{$search}%");
        })
            ->orderBy('kode', 'asc')
            ->paginate(20)
            ->withQueryString(); // Menjaga parameter pencarian saat pindah halaman

        return view('admin.korek.index', compact('koreks'));
    }

    /**
     * Menampilkan form tambah data
     */
    public function create()
    {
        // Mendefinisikan pilihan jenis belanja standar
        $jenisBelanjaList = ['operasional', 'mesin', 'aset lainnya'];

        return view('admin.korek.create', compact('jenisBelanjaList'));
    }

    /**
     * Menyimpan data kode rekening baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:koreks,kode',
            'ket' => 'nullable|string',
            'uraian_singkat' => 'nullable|string',
            'singkat' => 'nullable|string',
            'jenis_belanja' => 'nullable|string',
        ]);

        Korek::create($validated);

        return redirect()->route('admin.korek.index')
            ->with('success', 'Data Kode Rekening berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit data
     */
    public function edit(Korek $korek)
    {
        $jenisBelanjaList = ['operasional', 'mesin', 'aset lainnya'];

        return view('admin.korek.edit', compact('korek', 'jenisBelanjaList'));
    }

    /**
     * Memperbarui data kode rekening
     */
    public function update(Request $request, Korek $korek)
    {
        $validated = $request->validate([
            // Validasi unik, tapi abaikan ID yang sedang diedit
            'kode' => [
                'required',
                'string',
                Rule::unique('koreks')->ignore($korek->id),
            ],
            'ket' => 'nullable|string',
            'uraian_singkat' => 'nullable|string',
            'singkat' => 'nullable|string',
            'jenis_belanja' => 'nullable|string',
        ]);

        $korek->update($validated);

        return redirect()->route('admin.korek.index')
            ->with('success', 'Data Kode Rekening berhasil diperbarui.');
    }

    /**
     * Menghapus data kode rekening
     */
    public function destroy(Korek $korek)
    {
        try {
            $korek->delete();

            return redirect()->route('admin.korek.index')
                ->with('success', 'Data Kode Rekening berhasil dihapus.');

        } catch (QueryException $e) {
            // Cek Kode Error SQL 23000 (Integrity Constraint Violation)
            // Mencegah error jika kode rekening sudah terikat di tabel RKAS atau Transaksi
            if ($e->getCode() == '23000') {
                return redirect()->route('admin.korek.index')
                    ->with('error', 'GAGAL MENGHAPUS! Kode Rekening ini masih digunakan dalam Rencana Anggaran (RKAS) atau Transaksi.');
            }

            return redirect()->route('admin.korek.index')
                ->with('error', 'Terjadi kesalahan sistem: '.$e->getMessage());
        }
    }

    /**
     * Import update jenis belanja dari Excel
     */
    public function importKorekUpdate(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120', // Maksimal 5MB
        ]);

        try {
            Excel::import(new KorekUpdateImport, $request->file('file_excel'));

            return redirect()->back()->with('success', 'Data Jenis Belanja berhasil diperbarui berdasarkan Kode Rekening!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat import: '.$e->getMessage());
        }
    }
}
