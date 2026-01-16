<?php

namespace App\Http\Controllers;

use App\Models\Bku;
use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'no_bukti' => 'required|string',
            'uraian' => 'required|string',
            'nominal' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request) {
            $p = Penerimaan::create($request->all());

            // Masuk ke DEBIT BKU
            Bku::catat($p->tanggal, $p->no_bukti, $p->uraian, $p->nominal, 0);
        });

        return redirect()->back()->with('success', 'Penerimaan berhasil dicatat.');
    }
}
