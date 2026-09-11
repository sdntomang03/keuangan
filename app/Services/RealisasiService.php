<?php

namespace App\Services;

use App\Models\Rkas;
use Illuminate\Support\Facades\DB;

class RealisasiService
{
    /**
     * Mengeksekusi kueri rekapitulasi realisasi RKAS
     */
    public function getRekapRkas(int $anggaranId, ?array $bulanArray, bool $useMultiplier = false, float $persenPpn = 0)
    {
        $multiplier = 1 + ($persenPpn / 100);

        return Rkas::with(['kegiatan', 'korek', 'akb'])
            ->withSum(['akbrincis as total_volume_anggaran' => function ($query) use ($anggaranId, $bulanArray) {
                $query->where('anggaran_id', $anggaranId)
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'volume')
            ->withSum(['akbrincis as total_anggaran' => function ($query) use ($anggaranId, $bulanArray) {
                $query->where('anggaran_id', $anggaranId)
                    ->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray));
            }], 'nominal')
            ->withSum(['belanjaRincis as total_realisasi' => function ($query) use ($anggaranId, $bulanArray, $useMultiplier, $multiplier) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaranId));

                // Override jika memerlukan perhitungan pajak dinamis
                if ($useMultiplier) {
                    $query->select(DB::raw("SUM(
                        CASE
                            WHEN (SELECT ppn FROM belanjas WHERE belanjas.id = belanja_rincis.belanja_id) > 0
                            THEN (volume * harga_satuan * $multiplier)
                            ELSE (volume * harga_satuan)
                        END
                    )"));
                }
            }], 'total_bruto')
            ->withSum(['belanjaRincis as volume_realisasi' => function ($query) use ($anggaranId, $bulanArray) {
                $query->when($bulanArray, fn ($q) => $q->whereIn('bulan', $bulanArray))
                    ->whereHas('belanja', fn ($q) => $q->where('anggaran_id', $anggaranId));
            }], 'volume')
            ->where('anggaran_id', $anggaranId)
            ->get()
            ->filter(fn ($item) => $item->total_anggaran > 0 || $item->total_realisasi > 0);
    }
}
