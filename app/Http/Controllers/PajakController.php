<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Pajak;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PajakController extends Controller
{
    public function siapSetor(Request $request)
    {
        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return redirect()->route('sekolah.index')
                ->with('error', 'Silakan pilih Anggaran Aktif terlebih dahulu.');
        }

        // 2. Ambil pajak yang sudah dipungut (is_terima) tapi belum disetor (is_setor)
        // Filter berdasarkan anggaran_id yang ada di tabel belanjas
        $pajaks = Pajak::with(['masterPajak', 'belanja'])
            ->whereHas('belanja', function ($query) use ($anggaran) {
                $query->where('anggaran_id', $anggaran->id);
            })
            ->where('is_terima', true)
            ->where('is_setor', false)
            ->get();

        return view('pajak.siap_setor', compact('pajaks', 'anggaran'));
    }

    public function prosesSetor(Request $request, $id)
    {
        $request->validate([
            'tanggal_setor' => 'required|date',
            'ntpn' => 'required|string',
        ]);

        // 1. Ambil data anggaran aktif dari middleware
        $anggaran = $request->anggaran_data;

        if (! $anggaran) {
            return back()->with('error', 'Anggaran aktif tidak terdeteksi.');
        }

        // 2. Cari data pajak
        $pajak = Pajak::with('masterPajak')->findOrFail($id);

        try {
            DB::transaction(function () use ($pajak, $request, $anggaran) {
                // 3. Update status setor pada tabel pajaks
                $pajak->update([
                    'is_setor' => true,
                    'tanggal_setor' => $request->tanggal_setor,
                    'ntpn' => $request->ntpn,
                ]);
                $sekolah = Sekolah::find(auth()->user()->sekolah_id);
                // 4. Catat ke KREDIT BKU (Uang keluar dari kas sekolah ke negara)
                // Urutan parameter: $tanggal, $no_bukti, $uraian, $debit, $kredit, $belanjaId, $pajakId, $anggaranId
                Bku::catat(
                    $request->tanggal_setor,
                    $request->ntpn,
                    'Setor '.$pajak->masterPajak->nama_pajak.' - Bukti: '.$request->ntpn,
                    0,               // Debit
                    $pajak->nominal, // Kredit
                    $pajak->belanja_id,
                    $pajak->id,
                    $anggaran->id,    // PENTING: anggaran_id harus dikirim
                    null,
                    $sekolah->triwulan_aktif
                );
            });

            return redirect()->route('bku.index')
                ->with('success', "Pajak {$anggaran->singkatan} berhasil disetorkan dan dicatat di BKU.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses setoran: '.$e->getMessage());
        }
    }
}
