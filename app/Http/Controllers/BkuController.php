<?php

namespace App\Http\Controllers;

use App\Models\Bku;

class BkuController extends Controller
{
    public function index()
    {
        // Memanggil BKU -> Belanja -> Rekanan
        $bkus = Bku::with(['belanja.kegiatan', 'belanja.rekanan', 'belanja.korek'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_urut', 'asc')
            ->get();

        return view('bku.index', compact('bkus'));
    }
}
