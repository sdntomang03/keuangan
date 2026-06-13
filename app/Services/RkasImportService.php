<?php

namespace App\Services;

use App\Models\Rkas;
use Illuminate\Support\Facades\DB;

class RkasImportService
{
    protected $rekeningService;

    // Inject RekeningService langsung ke Service ini
    public function __construct(RekeningService $rekeningService)
    {
        $this->rekeningService = $rekeningService;
    }

    public function importData($files, $anggaran)
    {
        $mapAnggaran = ['bos' => 10, 'bop' => 20];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30;

        $countSukses = 0;
        $processedIds = [];
        $errorLogs = []; // Untuk mencatat peringatan (bukan memblokir)

        DB::beginTransaction();
        try {
            // PROSES BACA FILE JSON
            foreach ($files as $index => $file) {
                $content = json_decode(file_get_contents($file->getRealPath()), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('File ke-'.($index + 1).' gagal dibaca. Pastikan format file adalah JSON.');
                }

                // Ekstrak fallback IDBL dari queries
                $fallbackIdbl = '000000';
                if (isset($content['queries']) && is_array($content['queries'])) {
                    foreach ($content['queries'] as $queryObj) {
                        if (isset($queryObj['bindings']) && count($queryObj['bindings']) >= 4) {
                            $fallbackIdbl = (string) $queryObj['bindings'][3];
                            break;
                        }
                    }
                }

                $dataRinci = isset($content['data']) ? $content['data'] : [];

                if (empty($dataRinci)) {
                    throw new \Exception('File ke-'.($index + 1).' tidak memiliki data rincian komponen.');
                }

                // LOOPING DATA RINCIAN
                foreach ($dataRinci as $rowNum => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $barisFisik = $rowNum + 1;
                    $namaKompLog = $item['namakomponen'] ?? 'Komponen Tanpa Nama';

                    // Cek ketersediaan idblrinci (wajib)
                    $hasIdblrinci = array_key_exists('idblrinci', $item) && ! empty($item['idblrinci']);
                    if (! $hasIdblrinci) {
                        continue;
                    }

                    $uniqueId = $jenisAnggaran.$item['idblrinci'];
                    $processedIds[] = $uniqueId;

                    $finalIdbl = isset($item['idbl']) && ! empty($item['idbl'])
                                 ? (string) $item['idbl']
                                 : $fallbackIdbl;

                    // Pengecekan Rekening
                    $kodeAkunJson = trim((string) ($item['kodeakun'] ?? ''));
                    $korekId = $this->rekeningService->getIdByKode($kodeAkunJson);

                    if (! $korekId && $kodeAkunJson !== '') {
                        $errorLogs[] = "[Baris {$barisFisik}] Peringatan: Kode '{$kodeAkunJson}' belum ada di Master Rekening. Data tetap disimpan, namun relasi rekening dikosongkan.";
                    }

                    // --- ATURAN EMAS 1: Proteksi Pagu vs Realisasi ---
                    $rkasLama = Rkas::where('idblrinci', $uniqueId)->where('anggaran_id', $anggaran->id)->first();

                    if ($rkasLama) {
                        $totalRealisasi = $rkasLama->belanjaRincis()
                            ->join('belanjas', 'belanjas.id', '=', 'belanja_rincis.belanja_id')
                            ->sum(DB::raw('
                                CASE WHEN belanjas.ppn > 0 THEN (belanja_rincis.volume * belanja_rincis.harga_satuan * 1.11)
                                ELSE (belanja_rincis.volume * belanja_rincis.harga_satuan) END
                            '));

                        $paguBaru = (float) ($item['totalharga'] ?? 0);
                        if ($paguBaru < $totalRealisasi) {
                            throw new \Exception("GAGAL: Pagu komponen '{$namaKompLog}' diturunkan (Rp ".number_format($paguBaru, 0, ',', '.').') padahal realisasi sudah (Rp '.number_format($totalRealisasi, 0, ',', '.').').');
                        }
                    }

                    // --- EKSEKUSI SIMPAN ---
                    Rkas::updateOrCreate(
                        ['idblrinci' => $uniqueId],
                        [
                            'idbl' => $finalIdbl,
                            'idsubtitle' => (string) ($item['idsubtitle'] ?? ''),
                            'keterangan' => (string) ($item['keterangan'] ?? ''),
                            'namasub' => isset($item['namasub']) ? strip_tags((string) $item['namasub']) : '',
                            'kodeakun' => $korekId,
                            'namaakun' => (string) ($item['namaakun'] ?? ''),
                            'idkomponen' => (string) ($item['idkomponen'] ?? ''),
                            'namakomponen' => (string) ($item['namakomponen'] ?? ''),
                            'spek' => (string) ($item['spek'] ?? ''),
                            'satuan' => (string) ($item['satuan'] ?? ''),
                            'koefisien' => (string) ($item['koefisien'] ?? ''),
                            'hargasatuan' => (float) ($item['hargasatuan'] ?? 0),
                            'totalharga' => (float) ($item['totalharga'] ?? 0),
                            'totalpajak' => (float) ($item['totalpajak'] ?? 0),
                            'giatsubteks' => (string) ($item['giatsubteks'] ?? ''),
                            'anggaran_id' => $anggaran->id,
                        ]
                    );
                    $countSukses++;
                }
            }

            // --- ATURAN EMAS 2: PROTEKSI HAPUS SINKRONISASI ---
            if (count($processedIds) > 0) {
                $rkasDihapus = Rkas::where('anggaran_id', $anggaran->id)
                    ->whereNotIn('idblrinci', $processedIds)
                    ->get();

                foreach ($rkasDihapus as $rkas) {
                    if ($rkas->belanjaRincis()->exists()) {
                        throw new \Exception("SINKRONISASI DITOLAK: Komponen '{$rkas->namakomponen}' terhapus dari file JSON, tapi sudah ada riwayat belanja di sistem.");
                    }
                    $rkas->delete();
                }
            }

            DB::commit();

            return [
                'status' => 'success',
                'count' => $countSukses,
                'logs' => $errorLogs,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
