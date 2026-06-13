<?php

namespace App\Services;

use App\Models\Akb;
use App\Models\AkbRinci;
use App\Models\Rkas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AkbService
{
    /**
     * Logika untuk memproses file JSON AKB
     */
    public function importData($files, $anggaran, $settingId)
    {

        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30; // Default 30 jika tidak terdaftar

        $count = 0;

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file->getRealPath()), true);
            $items = $content['data'] ?? [];

            foreach ($items as $item) {
                Akb::updateOrCreate(
                    [
                        // KUNCI PENCARIAN
                        'idblrinci' => $jenisAnggaran.$item['idblrinci'].$anggaran->id,
                    ],
                    [
                        'idakun' => $item['idakun'] ?? null,
                        'volume' => $item['volume'] ?? null,
                        'pajak' => (int) ($item['pajak'] ?? 0),
                        'totalrincian' => (float) ($item['totalrincian'] ?? 0),
                        'bulan1' => (float) ($item['bulan1'] ?? 0),
                        'bulan2' => (float) ($item['bulan2'] ?? 0),
                        'bulan3' => (float) ($item['bulan3'] ?? 0),
                        'bulan4' => (float) ($item['bulan4'] ?? 0),
                        'bulan5' => (float) ($item['bulan5'] ?? 0),
                        'bulan6' => (float) ($item['bulan6'] ?? 0),
                        'bulan7' => (float) ($item['bulan7'] ?? 0),
                        'bulan8' => (float) ($item['bulan8'] ?? 0),
                        'bulan9' => (float) ($item['bulan9'] ?? 0),
                        'bulan10' => (float) ($item['bulan10'] ?? 0),
                        'bulan11' => (float) ($item['bulan11'] ?? 0),
                        'bulan12' => (float) ($item['bulan12'] ?? 0),
                        'totalakb' => (float) ($item['totalakb'] ?? 0),
                        'selisih' => (float) ($item['selisih'] ?? 0),
                        'realtw1' => (float) ($item['realtw1'] ?? 0),
                        'realtw2' => (float) ($item['realtw2'] ?? 0),
                        'realtw3' => (float) ($item['realtw3'] ?? 0),
                        'realtw4' => (float) ($item['realtw4'] ?? 0),
                        'anggaran_id' => $anggaran->id,
                        'setting_id' => $settingId,
                        'created_at' => now(),
                        'updated' => now(),
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Logika untuk men-generate Rincian AKB
     */
    public function generateRincian($anggaran)
    {
        $records = Akb::with('rkas')->where('anggaran_id', $anggaran->id)->get();

        if ($records->isEmpty()) {
            return ['status' => 'warning', 'message' => 'Data AKB Master untuk anggaran ini kosong. Silakan import file AKB terlebih dahulu.'];
        }

        try {
            DB::transaction(function () use ($records, $anggaran) {
                // Hapus data lama
                AkbRinci::where('anggaran_id', $anggaran->id)->delete();

                $dataToInsert = [];

                foreach ($records as $record) {
                    if (! $record->rkas) {
                        continue;
                    }

                    $hargaSatuan = (float) $record->rkas->hargasatuan;
                    $pajak = (float) ($record->pajak ?? 0);

                    if ($hargaSatuan <= 0) {
                        continue;
                    }

                    for ($i = 1; $i <= 12; $i++) {
                        $fieldBulan = "bulan$i";
                        $nominalBulan = (float) $record->$fieldBulan;

                        if ($nominalBulan > 0) {
                            $faktorPajak = ($pajak > 0) ? (1 + ($pajak / 100)) : 1;
                            $volume = $nominalBulan / ($hargaSatuan * $faktorPajak);

                            $dataToInsert[] = [
                                'akb_id' => $record->id,
                                'idblrinci' => $record->idblrinci,
                                'bulan' => $i,
                                'nominal' => $nominalBulan,
                                'volume' => $volume,
                                'anggaran_id' => $anggaran->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // Optimasi Batch Insert 500 baris
                    if (count($dataToInsert) >= 500) {
                        AkbRinci::insert($dataToInsert);
                        $dataToInsert = [];
                    }
                }

                if (! empty($dataToInsert)) {
                    AkbRinci::insert($dataToInsert);
                }
            });

            return ['status' => 'success', 'message' => "Sukses! Rincian bulan untuk anggaran <b>{$anggaran->nama_anggaran}</b> berhasil di-generate ulang."];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal memproses generate AKB: '.$e->getMessage()];
        }
    }

    /**
     * Logika untuk Membandingkan RKAS Lama vs Baru
     */
    public function compareData($files, $anggaran, $jenisJson)
    {
        $mapAnggaran = ['bos' => 10, 'bop' => 20];
        $singkatan = strtolower($anggaran->singkatan);
        $jenisAnggaran = $mapAnggaran[$singkatan] ?? 30;

        $dataDb = Akb::with('rkas')->where('anggaran_id', $anggaran->id)->get()->keyBy('idblrinci');
        $dataJsonMerged = [];

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file->getRealPath()), true);
            foreach ($content['data'] ?? [] as $item) {
                $idRaw = trim((string) ($item['idblrinci'] ?? ''));
                if (! $idRaw) {
                    continue;
                }

                $idblrinciUnik = $jenisAnggaran.$idRaw.$anggaran->id;
                $dataJsonMerged[$idblrinciUnik] = $item;
            }
        }

        $semuaIdUnik = collect($dataDb->keys())->merge(array_keys($dataJsonMerged))->unique();
        $hasilPerbandingan = [];

        foreach ($semuaIdUnik as $id) {
            $itemDb = $dataDb->get($id);
            $itemJson = $dataJsonMerged[$id] ?? null;

            $valJsonTotal = $itemJson ? (float) ($itemJson['totalakb'] ?? $itemJson['totalharga'] ?? 0) : 0;
            $valJsonRincian = $itemJson ? (float) ($itemJson['totalrincian'] ?? $itemJson['totalharga'] ?? 0) : 0;
            $valDbTotal = $itemDb ? (float) $itemDb->totalakb : 0;
            $valDbRincian = $itemDb ? (float) $itemDb->totalrincian : 0;

            $namaDb = $itemDb?->rkas?->namakomponen;
            $koefDb = $itemDb?->rkas?->koefisien;
            $spekDb = $itemDb?->rkas?->spek;
            $hargaSatuanDb = $itemDb?->rkas?->hargasatuan;

            $namaJson = $itemJson['namakomponen'] ?? null;
            $koefJson = $itemJson['koefisien'] ?? null;
            $spekJson = $itemJson['spek'] ?? null;
            $hargaSatuanJson = $itemJson['hargasatuan'] ?? null;

            if ($jenisJson == 'baru') {
                $totalLama = $valDbTotal;
                $totalBaru = $valJsonTotal;
                $rincianLama = $valDbRincian;
                $rincianBaru = $valJsonRincian;
                $namaKomponen = $namaJson ?? $namaDb ?? "ID: $id";
                $koefisien = $koefJson ?? $koefDb ?? '-';
                $spek = $spekJson ?? $spekDb ?? '-';
                $hargaSatuan = $hargaSatuanJson ?? $hargaSatuanDb ?? 0;
            } else {
                $totalLama = $valJsonTotal;
                $totalBaru = $valDbTotal;
                $rincianLama = $valJsonRincian;
                $rincianBaru = $valDbRincian;
                $namaKomponen = $namaDb ?? $namaJson ?? "ID: $id";
                $koefisien = $koefDb ?? $koefJson ?? '-';
                $spek = $spekDb ?? $spekJson ?? '-';
                $hargaSatuan = $hargaSatuanDb ?? $hargaSatuanJson ?? 0;
            }

            $bulanLama = [];
            $bulanBaru = [];
            $selisihBulan = [];
            $adaPergeseranBulan = false;

            for ($i = 1; $i <= 12; $i++) {
                if ($jenisJson == 'baru') {
                    $bLama = $itemDb ? (float) $itemDb->{"bulan$i"} : 0;
                    $bBaru = $itemJson ? (float) ($itemJson["bulan$i"] ?? 0) : 0;
                } else {
                    $bLama = $itemJson ? (float) ($itemJson["bulan$i"] ?? 0) : 0;
                    $bBaru = $itemDb ? (float) $itemDb->{"bulan$i"} : 0;
                }

                $bulanLama[$i] = $bLama;
                $bulanBaru[$i] = $bBaru;
                $sBulan = $bBaru - $bLama;
                $selisihBulan[$i] = $sBulan;
                if ($sBulan != 0) {
                    $adaPergeseranBulan = true;
                }
            }

            $selisihTotal = $totalBaru - $totalLama;
            $selisihRincian = $rincianBaru - $rincianLama;

            if ($totalLama > 0 && $totalBaru == 0) {
                $status = 'Dihapus';
            } elseif ($totalLama == 0 && $totalBaru > 0) {
                $status = 'Baru';
            } elseif ($selisihTotal != 0) {
                $status = 'Berubah Pagu';
            } elseif ($selisihRincian != 0) {
                $status = 'Berubah Rincian';
            } elseif ($adaPergeseranBulan) {
                $status = 'Geser Jadwal';
            } elseif ($totalLama == 0 && $totalBaru == 0) {
                $status = 'Dihapus';
            } else {
                $status = 'Tetap';
            }

            $hasilPerbandingan[] = [
                'idblrinci' => $id,
                'namakomponen' => $namaKomponen,
                'koefisien' => $koefisien,
                'spek' => $spek,
                'hargasatuan' => (float) $hargaSatuan,
                'status' => $status,
                'harga_lama' => $totalLama,
                'harga_baru' => $totalBaru,
                'selisih' => $selisihTotal,
                'bulan_lama' => $bulanLama,
                'bulan_baru' => $bulanBaru,
                'selisih_bulan' => $selisihBulan,
            ];
        }

        return [
            'koleksi' => collect($hasilPerbandingan),
            'labelLama' => $jenisJson == 'baru' ? 'Database' : 'JSON Lama',
            'labelBaru' => $jenisJson == 'baru' ? 'JSON Baru' : 'Database',
        ];
    }

    /**
     * Memformat Data untuk AJAX Datatable (Memindahkan logika dari Controller)
     */
    public function getAjaxFormattedData($request, $anggaranId)
    {
        $query = Rkas::with([
            'akbRincis' => fn ($q) => $q->orderBy('bulan', 'asc'),
            'kegiatan',
            'korek',
        ])->where('anggaran_id', $anggaranId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('namakomponen', 'like', "%{$search}%");
        }

        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['created_at', 'namakomponen', 'hargasatuan', 'idkomponen'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'namakomponen' => $item->namakomponen,
                'spek' => $item->spek,
                'satuan' => $item->satuan,
                'hargasatuan' => $item->hargasatuan,
                'idkomponen' => $item->idkomponen,
                'total_volume' => $item->akbRincis->sum('volume'),
                'total_pagu' => $item->akbRincis->sum('nominal'),
                'snp' => $item->kegiatan->snp ?? '-',
                'kegiatan' => Str::after($item->namasub ?? '-', ' '),
                'kode_rekening' => $item->korek->singkat ?? '-',
                'nama_rekening' => $item->korek->uraian_singkat ?? '-',
                'rincian' => $item->akbRincis->mapWithKeys(fn ($r) => [
                    $r->bulan => ['volume' => $r->volume, 'nominal' => $r->nominal],
                ]),
                'alokasi_aktif' => $item->akbRincis->filter(fn ($r) => $r->nominal > 0 || $r->volume > 0)
                    ->values()->map(fn ($r) => [
                        'nama_bulan' => Carbon::create()->month($r->bulan)->translatedFormat('F'),
                        'volume' => $r->volume,
                    ]),
            ];
        });
    }
}
