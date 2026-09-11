<?php

namespace App\Services;

use App\Models\Belanja;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BelanjaService
{
    /**
     * Menyatukan logika validasi pagu yang sebelumnya ada di store, update, dan duplicate.
     */
    /**
     * Menyatukan logika validasi pagu yang sebelumnya ada di store, update, dan duplicate.
     */
    public function validasiPaguAnggaran(array $items, int $anggaranId, array $bulanDicheck, int $tw, bool $hasPpn = false, float $persenPpn = 0, ?int $ignoreBelanjaId = null): void
    {
        // Jika form mengirim nilai PPN > 0, gunakan multiplier (misal 1.11 untuk 11%). Jika tidak, kalikan 1 (tetap).
        $multiplier = $hasPpn ? 1 + ($persenPpn / 100) : 1;

        foreach ($items as $item) {
            $subtotal = $item['total_bruto'] ?? ($item['volume'] * $item['harga_satuan']);

            // Hitung total nilai input dengan pajaknya
            $totalBrutoInput = ! isset($item['total_bruto']) ? $subtotal * $multiplier : $subtotal;

            $totalPaguAnggaran = DB::table('akb_rincis')
                ->where('idblrinci', $item['idblrinci'])
                ->whereIn('bulan', $bulanDicheck)
                ->sum('nominal');

            if ($totalPaguAnggaran <= 0) {
                throw new Exception("Komponen [{$item['namakomponen']}] tidak memiliki anggaran di Triwulan {$tw}.");
            }

            $queryRealisasi = DB::table('belanja_rincis')
                ->join('belanjas', 'belanja_rincis.belanja_id', '=', 'belanjas.id')
                ->where('belanja_rincis.idblrinci', $item['idblrinci'])
                ->where('belanjas.anggaran_id', $anggaranId)
                ->whereIn('bulan', $bulanDicheck);

            // Abaikan ID belanja saat ini (untuk fitur Update)
            if ($ignoreBelanjaId) {
                $queryRealisasi->where('belanjas.id', '!=', $ignoreBelanjaId);
            }

            $sudahDibelanjakan = $queryRealisasi->sum('total_bruto');
            $sisaPagu = $totalPaguAnggaran - $sudahDibelanjakan;

            if ($totalBrutoInput > $sisaPagu) {
                throw new Exception("Pagu Tidak Cukup untuk {$item['namakomponen']}. Sisa Pagu TW {$tw}: Rp ".number_format($sisaPagu, 0, ',', '.'));
            }
        }
    }

    /**
     * Memproses penyimpanan detail rincian dan pajak. Digunakan oleh Store dan Update.
     */
    public function sinkronisasiRincianDanPajak(Belanja $belanja, array $data, float $persenPpn): void
    {
        $multiplier = 1 + ($persenPpn / 100);
        $bulanTransaksi = Carbon::parse($belanja->tanggal)->month;

        $itemIdsKeep = [];

        // Sinkronisasi Rincian Barang
        foreach ($data['items'] as $item) {
            $subtotal = $item['volume'] * $item['harga_satuan'];
            $brutoDasar = ($belanja->ppn > 0) ? $subtotal * $multiplier : $subtotal;

            $rinci = $belanja->rincis()->updateOrCreate(
                ['idblrinci' => $item['idblrinci']],
                [
                    'namakomponen' => $item['namakomponen'],
                    'spek' => $item['spek'] ?? '-',
                    'harga_satuan' => $item['harga_satuan'],
                    'harga_penawaran' => $item['harga_penawaran'] ?? 0,
                    'volume' => $item['volume'],
                    'total_bruto' => $brutoDasar,
                    'bulan' => $bulanTransaksi,
                ]
            );
            $itemIdsKeep[] = $rinci->id;
        }

        // Hapus rincian yang tidak ada di input (Untuk Update)
        $belanja->rincis()->whereNotIn('id', $itemIdsKeep)->delete();

        // Sinkronisasi Pajak (Hapus lama, insert baru)
        $belanja->pajaks()->delete();
        if (! empty($data['pajaks'])) {
            foreach ($data['pajaks'] as $pajak) {
                if (! empty($pajak['id_master']) && $pajak['nominal'] > 0) {
                    $belanja->pajaks()->create([
                        'dasar_pajak_id' => $pajak['id_master'],
                        'nominal' => $pajak['nominal'],
                        'is_terima' => false,
                        'is_setor' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Memproses posting transaksi Belanja dan Pajaknya ke BKU
     */
    public function postingKeBku(Belanja $belanja, $anggaran, $sekolah): void
    {
        DB::transaction(function () use ($belanja, $anggaran, $sekolah) {
            // 1. Catat Transaksi Belanja Utama ke BKU
            \App\Models\Bku::catat(
                now()->today()->format('Y-m-d'),
                $belanja->no_bukti,
                'Dibayar '.$belanja->uraian.' dari '.$sekolah->nama_sekolah.' kepada '.$belanja->rekanan->nama_rekanan,
                0,
                $belanja->subtotal + $belanja->ppn,
                $belanja->id,
                null,
                $anggaran->id,
                null,
                $belanja->tw
            );

            // 2. Catat Tiap Pajak yang ada di Belanja tersebut
            foreach ($belanja->pajaks as $pajak) {
                \App\Models\Bku::catat(
                    now()->today()->format('Y-m-d'),
                    $belanja->no_bukti,
                    'Diterima '.$pajak->masterPajak->nama_pajak.' '.$belanja->uraian.' dari '.$sekolah->nama_sekolah.' kepada '.$belanja->rekanan->nama_rekanan,
                    $pajak->nominal,
                    0,
                    $belanja->id,
                    $pajak->id,
                    $anggaran->id,
                    null,
                    $belanja->tw
                );

                $pajak->update(['is_terima' => true]);
            }

            // 3. Update status belanja
            $belanja->update(['status' => 'posted']);
        });
    }
}
