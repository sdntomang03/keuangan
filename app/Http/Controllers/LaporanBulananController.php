<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Bku;
use App\Models\LaporanBulanan;
use App\Models\Penerimaan;
use App\Models\Rkas;
use App\Models\Sts;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanBulananController extends Controller
{
    public function index(Request $request)
    {
        $anggaran = $this->activeBudget($request);
        $laporans = LaporanBulanan::where('sekolah_id', auth()->user()->sekolah_id)
            ->where('anggaran_id', $anggaran->id)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->paginate(12);
        $laporans->getCollection()->each(function (LaporanBulanan $laporan) use ($anggaran) {
            $monthlyTotals = $this->monthlyTotals($anggaran, $laporan->bulan);
            $laporan->forceFill($monthlyTotals['totals']);
            $laporan->setAttribute('belanja_belum_diatur', $monthlyTotals['belanja_belum_diatur']);
        });

        return view('laporan_bulanan.index', compact('anggaran', 'laporans'));
    }

    public function create(Request $request)
    {
        $anggaran = $this->activeBudget($request);
        $bulan = (int) $request->query('bulan', now()->month);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }

        $laporanExisting = LaporanBulanan::where('anggaran_id', $anggaran->id)
            ->where('sekolah_id', auth()->user()->sekolah_id)
            ->where('tahun', $anggaran->tahun)
            ->where('bulan', $bulan)
            ->first();
        if ($laporanExisting) {
            return redirect()->route('laporan-bulanan.edit', $laporanExisting->id);
        }

        $laporan = new LaporanBulanan([
            'bulan' => $bulan,
            'tahun' => $anggaran->tahun,
            'sumber_dana' => $anggaran->nama_anggaran,
            'kota_surat' => auth()->user()->sekolah?->kota ?: 'Jakarta',
            'tanggal_surat' => now()->toDateString(),
        ]);
        $monthlyTotals = $this->monthlyTotals($anggaran, $bulan);

        return view('laporan_bulanan.form', compact('anggaran', 'laporan', 'monthlyTotals'));
    }

    public function store(Request $request)
    {
        $anggaran = $this->activeBudget($request);
        $validated = $this->validateReport($request, $anggaran->tahun);
        $monthlyTotals = $this->monthlyTotals($anggaran, (int) $validated['bulan']);
        $this->ensureBelanjaClassified($monthlyTotals);

        LaporanBulanan::create([
            ...$validated,
            ...$monthlyTotals['totals'],
            'sekolah_id' => auth()->user()->sekolah_id,
            'anggaran_id' => $anggaran->id,
            'user_id' => auth()->id(),
            'tahun' => $anggaran->tahun,
        ]);

        return redirect()->route('laporan-bulanan.index')->with('success', 'Laporan bulanan berhasil disimpan.');
    }

    public function edit(Request $request, int $id)
    {
        $anggaran = $this->activeBudget($request);
        $laporan = $this->findReport($id, $anggaran->id);
        $monthlyTotals = $this->monthlyTotals($anggaran, $laporan->bulan);
        $laporan->forceFill($monthlyTotals['totals']);

        return view('laporan_bulanan.form', compact('anggaran', 'laporan', 'monthlyTotals'));
    }

    public function update(Request $request, int $id)
    {
        $anggaran = $this->activeBudget($request);
        $laporan = $this->findReport($id, $anggaran->id);
        $validated = $this->validateReport($request, $anggaran->tahun, $laporan->id);
        $monthlyTotals = $this->monthlyTotals($anggaran, (int) $validated['bulan']);
        $this->ensureBelanjaClassified($monthlyTotals);
        $laporan->update([...$validated, ...$monthlyTotals['totals']]);

        return redirect()->route('laporan-bulanan.index')->with('success', 'Laporan bulanan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id)
    {
        $anggaran = $this->activeBudget($request);
        $this->findReport($id, $anggaran->id)->delete();

        return redirect()->route('laporan-bulanan.index')->with('success', 'Laporan bulanan berhasil dihapus.');
    }

    public function print(Request $request, int $id)
    {
        $anggaran = $this->activeBudget($request);
        $laporan = $this->findReport($id, $anggaran->id);
        $monthlyTotals = $this->monthlyTotals($anggaran, $laporan->bulan);
        $this->ensureBelanjaClassified($monthlyTotals);
        $laporan->forceFill($monthlyTotals['totals']);
        $sekolah = auth()->user()->sekolah;

        return view('laporan_bulanan.print', compact('laporan', 'sekolah', 'monthlyTotals'));
    }

    public function realisasi(Request $request, int $id)
    {
        $anggaran = $this->activeBudget($request);
        $laporan = $this->findReport($id, $anggaran->id);
        $this->ensureBelanjaClassified($this->monthlyTotals($anggaran, $laporan->bulan));
        $sekolah = auth()->user()->sekolah;
        $realisasi = $this->realisasiData($laporan);

        return view('laporan_bulanan.realisasi', compact('laporan', 'sekolah', 'realisasi'));
    }

    private function activeBudget(Request $request)
    {
        $anggaran = $request->anggaran_data;
        abort_unless(
            $anggaran && (int) $anggaran->sekolah_id === (int) auth()->user()->sekolah_id,
            404,
            'Anggaran aktif tidak ditemukan.'
        );

        return $anggaran;
    }

    private function findReport(int $id, int $anggaranId): LaporanBulanan
    {
        return LaporanBulanan::where('sekolah_id', auth()->user()->sekolah_id)
            ->where('anggaran_id', $anggaranId)
            ->findOrFail($id);
    }

    private function validateReport(Request $request, string $tahun, ?int $ignoreId = null): array
    {
        return $request->validate([
            'bulan' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('laporan_bulanans', 'bulan')
                    ->where('anggaran_id', $request->anggaran_data->id)
                    ->where('tahun', $tahun)
                    ->ignore($ignoreId),
            ],
            'nomor_surat' => 'nullable|string|max:100',
            'sumber_dana' => 'required|string|max:255',
            'saldo_awal' => 'required|numeric|min:0',
            'kas_tunai' => 'required|numeric|min:0',
            'rekening_bank' => 'required|numeric|min:0',
            'penjelasan_selisih' => 'nullable|string|max:5000',
            'kota_surat' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
        ]);
    }

    private function monthlyTotals($anggaran, int $bulan): array
    {
        $tahun = (int) $anggaran->tahun;
        $penerimaan = [
            'penerimaan_tahap_i' => 0,
            'penerimaan_tahap_ii' => 0,
            'penerimaan_kinerja' => 0,
            'penerimaan_lain' => 0,
        ];

        Penerimaan::where('anggaran_id', $anggaran->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get(['uraian', 'nominal'])
            ->each(function (Penerimaan $item) use (&$penerimaan) {
                $uraian = mb_strtolower($item->uraian);
                if (preg_match('/tahap\s*(ii|2)\b/u', $uraian)) {
                    $key = 'penerimaan_tahap_ii';
                } elseif (preg_match('/tahap\s*(i|1)\b/u', $uraian)) {
                    $key = 'penerimaan_tahap_i';
                } elseif (str_contains($uraian, 'kinerja')) {
                    $key = 'penerimaan_kinerja';
                } else {
                    $key = 'penerimaan_lain';
                }

                $penerimaan[$key] += (float) $item->nominal;
            });

        $pengeluaran = [
            'pengeluaran_barang_jasa' => 0,
            'pengeluaran_modal_peralatan' => 0,
            'pengeluaran_modal_aset_lainnya' => 0,
        ];
        $belanjaBelumDiatur = [];

        Belanja::with('korek')
            ->where('anggaran_id', $anggaran->id)
            ->where('status', Belanja::STATUS_POSTED)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->each(function (Belanja $belanja) use (&$pengeluaran, &$belanjaBelumDiatur) {
                $jenis = mb_strtolower(trim($belanja->korek?->jenis_belanja ?? ''));
                $field = match ($jenis) {
                    'operasional' => 'pengeluaran_barang_jasa',
                    'mesin' => 'pengeluaran_modal_peralatan',
                    'aset lainnya' => 'pengeluaran_modal_aset_lainnya',
                    default => null,
                };

                if ($field === null) {
                    $belanjaBelumDiatur[] = [
                        'no_bukti' => $belanja->no_bukti,
                        'uraian' => $belanja->uraian,
                        'rekening' => $belanja->korek?->kode ?? $belanja->kodeakun ?? 'Tidak diketahui',
                    ];

                    return;
                }

                $pengeluaran[$field] += (float) $belanja->subtotal + (float) $belanja->ppn;
            });

        $pengeluaran['pengeluaran_lain'] = (float) Bku::where('anggaran_id', $anggaran->id)
            ->whereNotNull('sts_id')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->sum('kredit');

        return [
            'totals' => [...$penerimaan, ...$pengeluaran],
            'belanja_belum_diatur' => $belanjaBelumDiatur,
        ];
    }

    private function ensureBelanjaClassified(array $monthlyTotals): void
    {
        if ($monthlyTotals['belanja_belum_diatur'] === []) {
            return;
        }

        $details = collect($monthlyTotals['belanja_belum_diatur'])
            ->map(fn (array $item) => "{$item['no_bukti']} (rekening {$item['rekening']})")
            ->implode(', ');

        throw ValidationException::withMessages([
            'bulan' => "Jenis belanja Korek belum diatur untuk transaksi: {$details}. Atur jenis belanja pada data Korek sebelum menyimpan laporan.",
        ]);
    }

    private function realisasiData(LaporanBulanan $laporan): array
    {
        $anggarans = \App\Models\Anggaran::where('sekolah_id', auth()->user()->sekolah_id)
            ->where('tahun', $laporan->tahun)
            ->get();
        $anggaranIds = $anggarans->modelKeys();
        $jenisByAnggaran = $anggarans->mapWithKeys(fn ($item) => [
            $item->id => str_contains(mb_strtolower($item->nama_anggaran.' '.$item->singkatan), 'kinerja')
                ? 'kinerja'
                : 'reguler',
        ]);
        $bulan = $laporan->bulan;
        $tahun = (int) $laporan->tahun;
        $semester = $bulan >= 7 ? 'II' : 'I';

        $unclassifiedBelanja = Belanja::with('korek')
            ->whereIn('anggaran_id', $anggaranIds)
            ->where('status', Belanja::STATUS_POSTED)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', $bulan)
            ->get()
            ->filter(fn (Belanja $item) => ! in_array(
                mb_strtolower(trim($item->korek?->jenis_belanja ?? '')),
                ['operasional', 'mesin', 'aset lainnya'],
                true
            ))
            ->map(fn (Belanja $item) => [
                'no_bukti' => $item->no_bukti,
                'uraian' => $item->uraian,
                'rekening' => $item->korek?->kode ?? $item->kodeakun ?? 'Tidak diketahui',
            ])
            ->values()
            ->all();
        $this->ensureBelanjaClassified(['belanja_belum_diatur' => $unclassifiedBelanja]);

        $series = fn (?float $budget, float $previous, float $current): array => [
            'budget' => $budget,
            'previous' => $previous,
            'current' => $current,
            'cumulative' => $previous + $current,
            'remaining' => $budget === null ? null : $budget - $previous - $current,
        ];
        $sumSeries = function (array $items) use ($series): array {
            $knownBudgets = collect($items)->pluck('budget')->filter(fn ($value) => $value !== null);
            $budget = $knownBudgets->isEmpty() ? null : $knownBudgets->sum();

            return $series(
                $budget,
                collect($items)->sum('previous'),
                collect($items)->sum('current')
            );
        };
        $incomeByLabel = [];
        foreach (['reguler', 'kinerja', 'bunga', 'pajak'] as $category) {
            $incomeByLabel[$category] = ['previous' => 0.0, 'current' => 0.0];
        }
        Penerimaan::whereIn('anggaran_id', $anggaranIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', $bulan)
            ->get(['anggaran_id', 'tanggal', 'uraian', 'nominal'])
            ->each(function (Penerimaan $item) use (&$incomeByLabel, $jenisByAnggaran, $bulan) {
                $uraian = mb_strtolower($item->uraian);
                if (str_contains($uraian, 'pajak')) {
                    $category = 'pajak';
                } elseif (str_contains($uraian, 'bunga') || str_contains($uraian, 'jasa giro')) {
                    $category = 'bunga';
                } else {
                    $category = $jenisByAnggaran[$item->anggaran_id] ?? 'reguler';
                }

                $period = (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') === $bulan ? 'current' : 'previous';
                $incomeByLabel[$category][$period] += (float) $item->nominal;
            });

        \App\Models\Bku::whereIn('anggaran_id', $anggaranIds)
            ->whereNotNull('pajak_id')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', $bulan)
            ->get(['tanggal', 'debit'])
            ->each(function ($item) use (&$incomeByLabel, $bulan) {
                $period = (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') === $bulan ? 'current' : 'previous';
                $incomeByLabel['pajak'][$period] += (float) $item->debit;
            });

        $incomeRows = [];
        foreach ([
            'reguler' => 'BOSP Reguler',
            'kinerja' => 'BOSP Kinerja',
            'bunga' => 'Bunga Bank/Jasa Giro',
            'pajak' => 'Potongan dan Pungutan Pajak',
        ] as $category => $label) {
            $amounts = $incomeByLabel[$category];
            $incomeRows[] = [
                'label' => $label,
                ...$series(null, $amounts['previous'], $amounts['current']),
            ];
        }
        $incomeTotal = $sumSeries($incomeRows);

        $expenseCategories = [
            'operasional' => 'Belanja Barang dan Jasa',
            'mesin' => 'Belanja Modal Peralatan dan Mesin',
            'aset lainnya' => 'Belanja Modal Aset Tetap Lainnya',
        ];
        $expenseRows = [];
        $expenseTotals = [];
        foreach ($expenseCategories as $jenis => $label) {
            $byBudgetType = [];
            foreach (['reguler', 'kinerja'] as $budgetType) {
                $budgetIds = $anggarans
                    ->filter(fn ($item) => $jenisByAnggaran[$item->id] === $budgetType)
                    ->modelKeys();
                $budgetAmount = (float) Rkas::whereIn('anggaran_id', $budgetIds)
                    ->whereHas('korek', fn ($query) => $query->where('jenis_belanja', $jenis))
                    ->sum('totalharga');

                $transactions = Belanja::with('korek')
                    ->whereIn('anggaran_id', $budgetIds)
                    ->where('status', Belanja::STATUS_POSTED)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', '<=', $bulan)
                    ->get()
                    ->filter(fn (Belanja $item) => mb_strtolower(trim($item->korek?->jenis_belanja ?? '')) === $jenis);

                $previous = (float) $transactions
                    ->filter(fn (Belanja $item) => (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') < $bulan)
                    ->sum(fn (Belanja $item) => (float) $item->subtotal + (float) $item->ppn);
                $current = (float) $transactions
                    ->filter(fn (Belanja $item) => (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') === $bulan)
                    ->sum(fn (Belanja $item) => (float) $item->subtotal + (float) $item->ppn);

                $byBudgetType[$budgetType] = $series($budgetAmount > 0 ? $budgetAmount : null, $previous, $current);
            }

            $expenseRows[$jenis] = [
                'label' => $label,
                'reguler' => ['label' => 'BOSP Reguler', ...$byBudgetType['reguler']],
                'kinerja' => ['label' => 'BOSP Kinerja', ...$byBudgetType['kinerja']],
            ];
            $expenseTotals[] = $sumSeries(array_values($byBudgetType));
        }

        $nonProgram = [
            'Biaya Administrasi Bank' => ['budget' => null, 'previous' => 0.0, 'current' => 0.0],
            'Setoran Bunga Bank/Jasa Giro' => ['budget' => null, 'previous' => 0.0, 'current' => 0.0],
            'Setoran Pajak' => ['budget' => null, 'previous' => 0.0, 'current' => 0.0],
        ];
        Sts::whereIn('anggaran_id', $anggaranIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', $bulan)
            ->get(['bku_id', 'tanggal', 'uraian', 'nominal'])
            ->each(function (Sts $item) use (&$nonProgram, $bulan) {
                $description = mb_strtolower($item->uraian);
                $label = str_contains($description, 'bunga') || str_contains($description, 'jasa giro')
                    ? 'Setoran Bunga Bank/Jasa Giro'
                    : 'Setoran Pajak';
                $period = (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') === $bulan
                    ? 'current'
                    : 'previous';
                $nonProgram[$label][$period] += (float) $item->nominal;
            });

        \App\Models\Bku::whereIn('anggaran_id', $anggaranIds)
            ->whereNull('belanja_id')
            ->whereNull('penerimaan_id')
            ->whereNull('sts_id')
            ->whereNotIn('id', Sts::whereNotNull('bku_id')->select('bku_id'))
            ->where('kredit', '>', 0)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', $bulan)
            ->get(['tanggal', 'uraian', 'kredit'])
            ->each(function ($item) use (&$nonProgram, $bulan) {
                $description = mb_strtolower($item->uraian);
                if (str_contains($description, 'pajak')) {
                    return;
                } elseif (str_contains($description, 'bunga') || str_contains($description, 'jasa giro')) {
                    return;
                } elseif (str_contains($description, 'bank') || str_contains($description, 'administrasi')) {
                    $label = 'Biaya Administrasi Bank';
                } else {
                    return;
                }
                $period = (int) \Illuminate\Support\Carbon::parse($item->tanggal)->format('n') === $bulan
                    ? 'current'
                    : 'previous';
                $nonProgram[$label][$period] += (float) $item->kredit;
            });

        foreach ($nonProgram as $label => $values) {
            $expenseRows['non_program'][] = ['label' => $label, ...$series(null, $values['previous'], $values['current'])];
        }
        $expenseTotals[] = $sumSeries($nonProgram);
        $expenseTotal = $sumSeries($expenseTotals);

        return [
            'semester' => $semester,
            'month_label' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F'),
            'income_rows' => $incomeRows,
            'income_total' => $incomeTotal,
            'expense_rows' => $expenseRows,
            'expense_total' => $expenseTotal,
        ];
    }
}
