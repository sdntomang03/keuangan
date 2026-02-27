<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\BelanjaRinci;
use App\Models\Sekolah;
use Illuminate\Http\Request;

class PersediaanController extends Controller
{
    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index');
        }

        $sekolah = Sekolah::find(auth()->user()->sekolah_id);
        $triwulanAktif = (int) filter_var($sekolah->triwulan_aktif, FILTER_SANITIZE_NUMBER_INT);

        // Tangkap input (filterKorek sekarang akan berbentuk array)
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $filterKorek = $request->get('korek'); // Ini akan menangkap array
        $search = $request->get('search');

        $query = BelanjaRinci::with(['belanja.korek'])
            ->whereHas('belanja', function ($q) use ($anggaran, $startDate, $endDate, $filterKorek) {
                $q->where('anggaran_id', $anggaran->id)
                    ->where('status', 'posted');

                if ($startDate && $endDate) {
                    $q->whereBetween('tanggal', [$startDate, $endDate]);
                }

                // MENGGUNAKAN whereIn UNTUK MULTI SELECT
                if (! empty($filterKorek)) {
                    $q->whereIn('kodeakun', $filterKorek);
                }
            });

        if ($search) {
            $query->where('namakomponen', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(25);
        $totalNilai = $query->sum('total_bruto');

        // Ambil daftar unik kode rekening untuk dropdown
        $listKorek = Belanja::where('anggaran_id', $anggaran->id)
            ->where('status', 'posted')
            ->whereNotNull('kodeakun')
            ->select('kodeakun')
            ->distinct()
            ->with('korek')
            ->get();

        return view('persediaan.index', compact('items', 'totalNilai', 'startDate', 'endDate', 'search', 'triwulanAktif', 'listKorek', 'filterKorek'));
    }
}
