<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::latest()->paginate(10);

        return view('kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        return view('kegiatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // IDBL Wajib dan Unik
            'idbl' => 'required|string|unique:kegiatans,idbl',

            // Field lain nullable sesuai schema
            'snp' => 'nullable|string',
            'sumber_dana' => 'nullable|string',
            'kodedana' => 'nullable|string',
            'namadana' => 'nullable|string',
            'kodegiat' => 'nullable|string',
            'namagiat' => 'nullable|string',
            'kegiatan' => 'nullable|string', // Deskripsi
            'link' => 'nullable|url',    // Validasi URL jika diisi
        ]);

        Kegiatan::create($validated);

        return redirect()->route('setting.kegiatan.index')
            ->with('success', 'Data Kegiatan berhasil disimpan.');
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            // IDBL Unik, tapi abaikan ID data ini sendiri (ignore)
            'idbl' => [
                'required',
                'string',
                Rule::unique('kegiatans')->ignore($kegiatan->id),
            ],

            'snp' => 'nullable|string',
            'sumber_dana' => 'nullable|string',
            'kodedana' => 'nullable|string',
            'namadana' => 'nullable|string',
            'kodegiat' => 'nullable|string',
            'namagiat' => 'nullable|string',
            'kegiatan' => 'nullable|string',
            'link' => 'nullable|url',
        ]);

        $kegiatan->update($validated);

        return redirect()->route('setting.kegiatan.index')
            ->with('success', 'Data Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

        return redirect()->route('setting.kegiatan.index')
            ->with('success', 'Data Kegiatan berhasil dihapus.');
    }
}
