<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RkasService
{
    public function getPivotComparison($tw = null)
    {
        $anggaranAktif = DB::table('settings')->where('key', 'anggaran_aktif')->value('value') ?? 'bos';
        $months = $this->getBulanByTw($tw);

        // Format bulan untuk SQLite strftime (01, 02, dst)
        $monthList = array_map(fn ($m) => str_pad($m, 2, '0', STR_PAD_LEFT), $months);
        $monthString = "'".implode("','", $monthList)."'";

        // Subquery 1: Total AKB per idblrinci
        $akbSub = DB::table('akb_rincis')
            ->select('idblrinci', DB::raw('SUM(nominal) as total_akb'))
            ->whereIn('bulan', $months)
            ->groupBy('idblrinci');

        // Subquery 2: Total Realisasi dari belanja_rincis join belanjas
        $belanjaSub = DB::table('belanja_rincis')
            ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
            ->select('belanja_rincis.idblrinci', DB::raw('SUM(belanja_rincis.total_bruto) as total_realisasi'))
            ->whereRaw("strftime('%m', belanjas.tanggal) IN ($monthString)")
            ->groupBy('belanja_rincis.idblrinci');

        // Main Query: Menggabungkan RKAS dengan hasil kalkulasi subquery
        return DB::table('rkas')
            ->leftJoinSub($akbSub, 'akb', 'rkas.idblrinci', '=', 'akb.idblrinci')
            ->leftJoinSub($belanjaSub, 'blj', 'rkas.idblrinci', '=', 'blj.idblrinci')
            ->select(
                'rkas.idbl',
                'rkas.kodeakun',
                'rkas.namaakun',
                DB::raw('SUM(COALESCE(akb.total_akb, 0)) as total_akb'),
                DB::raw('SUM(COALESCE(blj.total_realisasi, 0)) as total_realisasi'),
                DB::raw('MAX(rkas.totalharga) as pagu_tahunan') // Jika ingin melihat pagu induk
            )
            ->where('rkas.jenis_anggaran', $anggaranAktif)
            ->groupBy('rkas.idbl', 'rkas.kodeakun', 'rkas.namaakun')
            ->get();
    }

    private function getBulanByTw($tw)
    {
        return match ((int) $tw) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => [1, 2, 3]
        };
    }

    public function getFullYearComparison()
    {
        $anggaranAktif = DB::table('settings')->where('key', 'anggaran_aktif')->value('value') ?? 'bos';

        return DB::table('rkas')
            ->leftJoin('belanja_rincis', 'rkas.idblrinci', '=', 'belanja_rincis.idblrinci')
            ->leftJoin('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
            ->select(
                'rkas.idbl',
                'rkas.kodeakun',
                'rkas.namaakun',
                'rkas.totalharga as pagu_tahunan',
                // Gunakan COALESCE agar jika tidak ada belanja, hasilnya 0 (bukan null)
                DB::raw("SUM(CASE WHEN strftime('%m', belanjas.tanggal) BETWEEN '01' AND '03' THEN COALESCE(belanja_rincis.total_bruto, 0) ELSE 0 END) as realisasi_tw1"),
                DB::raw("SUM(CASE WHEN strftime('%m', belanjas.tanggal) BETWEEN '04' AND '06' THEN COALESCE(belanja_rincis.total_bruto, 0) ELSE 0 END) as realisasi_tw2"),
                DB::raw("SUM(CASE WHEN strftime('%m', belanjas.tanggal) BETWEEN '07' AND '09' THEN COALESCE(belanja_rincis.total_bruto, 0) ELSE 0 END) as realisasi_tw3"),
                DB::raw("SUM(CASE WHEN strftime('%m', belanjas.tanggal) BETWEEN '10' AND '12' THEN COALESCE(belanja_rincis.total_bruto, 0) ELSE 0 END) as realisasi_tw4")
            )
            ->where('rkas.jenis_anggaran', $anggaranAktif)
            ->groupBy('rkas.idbl', 'rkas.kodeakun', 'rkas.namaakun', 'rkas.totalharga')
            ->get();
    }
}
