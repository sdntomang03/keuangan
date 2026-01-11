<?php

namespace App\Http\Controllers;

use App\Models\Rkas;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $setting = Setting::where('user_id', $userId)->first();

        // Statistik Anggaran
        $stats = [
            // Statistik BOS
            'total_bos' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bos')->count(),
            'harga_bos' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bos')->sum('totalharga'),
            'pajak_bos' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bos')->sum('totalpajak'),

            // Statistik BOP
            'total_bop' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bop')->count(),
            'harga_bop' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bop')->sum('totalharga'),
            'pajak_bop' => Rkas::where('user_id', $userId)->where('jenis_anggaran', 'bop')->sum('totalpajak'),
        ];

        return view('dashboard', compact('setting', 'stats'));
    }
}
