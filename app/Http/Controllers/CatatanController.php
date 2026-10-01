<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatatanRequest;
use App\Models\Anggaran;
use App\Models\Catatan;
use App\Models\CatatanLampiran;
use App\Services\CatatanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $sekolahId = auth()->user()->sekolah_id;
        if (! $sekolahId) {
            return redirect()->route('sekolah.index')->with('error', 'Akun belum terhubung dengan sekolah.');
        }

        $sekolah = auth()->user()->sekolah;
        $filterTw = (string) $request->input('tw', $sekolah?->triwulan_aktif ?? 'semua');
        $filterAnggaran = (string) $request->input('anggaran', 'semua');
        $request->validate([
            'tw' => ['nullable', Rule::in(['semua', '0', '1', '2', '3', '4'])],
            'anggaran' => [
                'nullable',
                function ($attribute, $value, $fail) use ($sekolahId) {
                    if ($value !== null && $value !== 'semua'
                        && ! Anggaran::query()->where('sekolah_id', $sekolahId)->whereKey($value)->exists()) {
                        $fail('Tahun anggaran yang dipilih tidak tersedia.');
                    }
                },
            ],
        ]);
        $anggarans = Anggaran::query()
            ->where('sekolah_id', $sekolahId)
            ->orderByDesc('tahun')
            ->get();

        $catatans = Catatan::with(['anggaran', 'lampirans'])
            ->where('user_id', auth()->id())
            ->whereHas('anggaran', fn ($query) => $query->where('sekolah_id', $sekolahId))
            ->when($filterAnggaran !== 'semua', fn ($query) => $query->where('anggaran_id', $filterAnggaran))
            ->when($filterTw !== 'semua', fn ($q) => $q->where('tw', $filterTw))
            ->latest()
            ->get();

        return view('catatan.index', compact('catatans', 'anggaran', 'anggarans', 'sekolah', 'filterTw', 'filterAnggaran'));
    }

    public function store(CatatanRequest $request)
    {
        $sekolah = auth()->user()->sekolah;
        if (! $sekolah) {
            return back()->with('error', 'Akun belum terhubung dengan sekolah.');
        }

        $anggaran = Anggaran::query()->where('sekolah_id', $sekolah->id)->findOrFail($request->validated('anggaran_id'));

        $twAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        $this->catatanService->simpanCatatan($request->validated(), $anggaran, $twAktif);

        return back()->with('success', 'Catatan dan lampiran berhasil ditambahkan.');
    }

    public function update(CatatanRequest $request, Catatan $catatan)
    {
        $this->pastikanMilikPengguna($catatan);
        $anggaran = Anggaran::query()
            ->where('sekolah_id', auth()->user()->sekolah_id)
            ->findOrFail($request->validated('anggaran_id'));

        $this->catatanService->perbaruiCatatan($catatan, $request->validated(), $anggaran);

        return back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy(Catatan $catatan)
    {
        $this->pastikanMilikPengguna($catatan);
        $this->catatanService->hapusCatatan($catatan);

        return back()->with('success', 'Catatan berhasil dihapus.');
    }

    public function destroyLampiran(Catatan $catatan, CatatanLampiran $lampiran)
    {
        $this->pastikanMilikPengguna($catatan);
        abort_unless($lampiran->catatan_id === $catatan->id, 404);

        $this->catatanService->hapusLampiran($lampiran);

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function destroyLampiranLama(Catatan $catatan)
    {
        $this->pastikanMilikPengguna($catatan);
        abort_unless($catatan->file_path, 404);

        $this->catatanService->hapusLampiranLama($catatan);

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function toggleTl(Catatan $catatan)
    {
        $this->pastikanMilikPengguna($catatan);
        $this->catatanService->toggleTindakLanjut($catatan);

        $status = $catatan->is_tl ? 'Sudah di-TL' : 'Belum di-TL';

        return back()->with('success', "Status catatan berhasil diubah menjadi: {$status}.");
    }

    private function pastikanMilikPengguna(Catatan $catatan): void
    {
        abort_unless(
            $catatan->user_id === auth()->id()
                && $catatan->anggaran()->where('sekolah_id', auth()->user()->sekolah_id)->exists(),
            403
        );
    }
}
