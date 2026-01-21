<?php

namespace App\Http\Middleware;

use App\Models\Anggaran;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AnggaranMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah middleware terpanggil
        // dump("Middleware Berjalan");

        if (auth()->check()) {
            // dump("User Terdeteksi: " . auth()->user()->name);

            $anggaranAktif = Anggaran::where('sekolah_id', auth()->user()->sekolah_id)
                ->where('is_aktif', true)
                ->first();

            // dump($anggaranAktif); // Pastikan ini tidak null

            View::share('anggaranAktif', $anggaranAktif);
            $request->merge(['anggaran_data' => $anggaranAktif]);
        }

        return $next($request);
    }
}
