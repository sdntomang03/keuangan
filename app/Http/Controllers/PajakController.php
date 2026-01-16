<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Pajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PajakController extends Controller
{
    public function siapSetor()
    {
        $pajaks = Pajak::with(['masterPajak', 'belanja'])
            ->where('is_terima', true)
            ->where('is_setor', false)
            ->get();

        return view('pajak.siap_setor', compact('pajaks'));
    }

    public function prosesSetor(Request $request, $id)
    {
        $request->validate(['tanggal_setor' => 'required|date', 'ntpn' => 'required']);
        $pajak = Pajak::with('masterPajak')->findOrFail($id);

        DB::transaction(function () use ($pajak, $request) {
            $pajak->update([
                'is_setor' => true,
                'tanggal_setor' => $request->tanggal_setor,
                'ntpn' => $request->ntpn,
            ]);

            // Masuk ke KREDIT BKU (Setor ke Negara)
            Bku::catat(
                $request->tanggal_setor,
                $request->ntpn,
                'Setor '.$pajak->masterPajak->nama_pajak,
                0,
                $pajak->nominal,
                $pajak->belanja_id,
                $pajak->id
            );
        });

        return redirect()->route('bku.index')->with('success', 'Pajak berhasil disetor.');
    }
}
