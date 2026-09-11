<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatatanRequest;
use App\Models\Catatan;
use App\Models\Sekolah;
use App\Services\CatatanService;
use Illuminate\Http\Request;

class CatatanController extends Controller
{
    protected CatatanService $catatanService;

    public function __construct(CatatanService $catatanService)
    {
        $this->catatanService = $catatanService;
    }

    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Pilih Anggaran Aktif.');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);
        $filterTw = $request->get('tw', $twAktif);

        // Ambil data catatan berdasarkan anggaran dan triwulan terpilih
        $catatans = Catatan::where('anggaran_id', $anggaran->id)
            ->when($filterTw !== 'semua', fn ($q) => $q->where('tw', $filterTw))
            ->latest()
            ->get();

        return view('catatan.index', compact('catatans', 'anggaran', 'sekolah', 'filterTw'));
    }

    public function store(CatatanRequest $request)
    {
        $anggaran = $request->anggaran_data;
        $sekolah = auth()->user()->sekolah;

        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        try {
            $this->catatanService->simpanCatatan($request->validated(), $anggaran, $twAktif);

            return back()->with('success', 'Catatan berhasil ditambahkan dengan format WebP.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan catatan: '.$e->getMessage());
        }
    }

    public function destroy(Catatan $catatan)
    {
        try {
            $this->catatanService->hapusCatatan($catatan);

            return back()->with('success', 'Catatan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus catatan: '.$e->getMessage());
        }
    }

    public function toggleTl(Catatan $catatan)
    {
        $this->catatanService->toggleTindakLanjut($catatan);

        $status = $catatan->is_tl ? 'Sudah di-TL' : 'Belum di-TL';

        return back()->with('success', "Status catatan berhasil diubah menjadi: {$status}.");
    }
}
