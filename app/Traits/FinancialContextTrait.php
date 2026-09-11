<?php

namespace App\Traits;

trait FinancialContextTrait
{
    /**
     * Konversi angka Triwulan menjadi array bulan.
     */
    protected function getBulanDariTw($tw): array
    {
        return match ((int) filter_var($tw, FILTER_SANITIZE_NUMBER_INT)) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => range(1, 12)
        };
    }

    /**
     * Menerjemahkan input multi-filter (contoh: tw1, b5) menjadi array bulan dan teks
     */
    protected function parsePeriodeFilter($periodeInput): array
    {
        if (! is_array($periodeInput)) {
            $periodeInput = explode(',', $periodeInput);
        }

        if (in_array('tahun', $periodeInput)) {
            return ['bulanArray' => null, 'periodeText' => 'Tahunan'];
        }

        $bulanArray = [];
        $periodeTextList = [];
        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        foreach ($periodeInput as $p) {
            $p = trim(strtolower($p));
            if (str_starts_with($p, 'tw')) {
                $tw = str_replace('tw', '', $p);
                $bulanArray = array_merge($bulanArray, $this->getBulanDariTw($tw));
                $periodeTextList[] = 'TW '.$tw;
            } elseif (str_starts_with($p, 'b')) {
                $b = (int) str_replace('b', '', $p);
                if ($b >= 1 && $b <= 12) {
                    $bulanArray[] = $b;
                    $periodeTextList[] = $namaBulan[$b - 1];
                }
            }
        }

        if (! empty($bulanArray)) {
            $bulanArray = array_unique($bulanArray);
            sort($bulanArray);

            return [
                'bulanArray' => $bulanArray,
                'periodeText' => implode(', ', array_unique($periodeTextList)),
            ];
        }

        return ['bulanArray' => null, 'periodeText' => 'Tahunan'];
    }
}
