<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DasarPajak;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DasarPajakController extends Controller
{
    /**
     * Menampilkan daftar master pajak.
     */
    public function index()
    {
        $pajaks = DasarPajak::orderBy('nama_pajak', 'asc')->get();

        return view('admin.dasar_pajak.index', compact('pajaks'));
    }

    /**
     * Menyimpan data master pajak baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pajak' => 'required|string|max:255|unique:dasar_pajaks,nama_pajak',
            'persen' => 'required|numeric|min:0|max:100',
            'jenis' => 'required|in:penambah,pengurang',
        ]);

        DasarPajak::create($validated);

        return back()->with('success', 'Jenis pajak berhasil ditambahkan.');
    }

    /**
     * Memperbarui data master pajak.
     */
    /**
     * Memperbarui data master pajak.
     */
    public function update(Request $request, DasarPajak $dasarPajak)
    {
        // 1. Cek apakah pajak ini sudah pernah dipakai di transaksi belanja
        $isUsed = \App\Models\Pajak::where('dasar_pajak_id', $dasarPajak->id)->exists();

        if ($isUsed) {
            // 2. Jika SUDAH DIPAKAI: Hanya izinkan update nama pajak
            $validated = $request->validate([
                'nama_pajak' => 'required|string|max:255|unique:dasar_pajaks,nama_pajak,'.$dasarPajak->id,
            ]);

            // Eksekusi update hanya untuk kolom nama
            $dasarPajak->update([
                'nama_pajak' => $validated['nama_pajak'],
            ]);

            return back()->with('success', 'Nama pajak diperbarui. Persentase dan jenis tidak diubah karena pajak sudah digunakan dalam transaksi sekolah.');

        } else {
            // 3. Jika BELUM DIPAKAI: Izinkan update semua kolom (Nama, Persen, Jenis)
            $validated = $request->validate([
                'nama_pajak' => 'required|string|max:255|unique:dasar_pajaks,nama_pajak,'.$dasarPajak->id,
                'persen' => 'required|numeric|min:0|max:100',
                'jenis' => 'required|in:penambah,pengurang',
            ]);

            $dasarPajak->update($validated);

            return back()->with('success', 'Data pajak berhasil diperbarui secara menyeluruh.');
        }
    }

    /**
     * Menghapus master pajak.
     */
    public function destroy(DasarPajak $dasarPajak)
    {
        try {
            $dasarPajak->delete();

            return back()->with('success', 'Jenis pajak berhasil dihapus.');

        } catch (QueryException $e) {
            // Error 1451 biasanya karena Constrain Foreign Key (Pajak ini sudah dipakai di transaksi belanja)
            if ($e->errorInfo[1] == 1451) {
                return back()->with('error', 'Pajak tidak dapat dihapus karena sudah digunakan pada riwayat transaksi sekolah.');
            }

            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }
}
