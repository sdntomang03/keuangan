<?php

namespace App\Http\Controllers;

use App\Exports\PaguTriwulanExport;
use App\Models\AkbRinci;
use App\Models\Anggaran;
use App\Models\BelanjaRinci;
use App\Models\PenyesuaianPagu;
use App\Models\PenyesuaianPaguRinci;
use App\Models\Rkas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PenyesuaianPaguController extends Controller
{
    private const TARIF_PAJAK = 12;

    public function index(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $request->validate([
            'kegiatan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'idbl')->where(fn ($query) => $query->where('anggaran_id', $anggaran->id)),
            ],
            'keterangan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'keterangan')->where(fn ($query) => $query
                    ->where('anggaran_id', $anggaran->id)
                    ->when($request->input('kegiatan'), fn ($query, $idbl) => $query->where('idbl', $idbl))),
            ],
            'tw' => ['nullable', 'array'],
            'tw.*' => ['required', 'integer', 'distinct', Rule::in([1, 2, 3, 4])],
        ]);
        $kegiatanDipilih = $request->input('kegiatan');
        $keteranganDipilih = (string) $request->input('keterangan', '');
        $twDipilih = collect($request->input('tw', []))->map(fn ($tw) => (int) $tw)->unique()->sort()->values()->all();
        $bulanDipilih = $this->bulanDariTriwulan($twDipilih);
        $twAktif = (int) (auth()->user()->sekolah?->triwulan_aktif ?? 0);
        $komponenSudahDisesuaikan = $this->idblrinciSudahDisesuaikan($anggaran->id, $twAktif);
        $daftarKegiatan = DB::table('rkas')
            ->leftJoin('kegiatans', 'kegiatans.idbl', '=', 'rkas.idbl')
            ->where('rkas.anggaran_id', $anggaran->id)
            ->whereNotNull('rkas.idbl')
            ->select('rkas.idbl', 'kegiatans.kodegiat', 'kegiatans.namagiat')
            ->distinct()
            ->orderBy('kegiatans.namagiat')
            ->get();
        $daftarKeterangan = DB::table('rkas')
            ->where('anggaran_id', $anggaran->id)
            ->when($kegiatanDipilih, fn ($query) => $query->where('idbl', $kegiatanDipilih))
            ->whereNotNull('keterangan')
            ->where('keterangan', '<>', '')
            ->distinct()
            ->orderBy('keterangan')
            ->pluck('keterangan');

        $komponenPerKegiatan = $this->komponenQuery($anggaran->id, $bulanDipilih)
            ->when($kegiatanDipilih, fn ($query) => $query->where('idbl', $kegiatanDipilih))
            ->when($keteranganDipilih !== '', fn ($query) => $query->where('keterangan', $keteranganDipilih))
            ->get()
            ->filter(fn (Rkas $komponen) => $komponen->pagu_setahun > 0 || $komponen->realisasi_setahun > 0)
            ->each(fn (Rkas $komponen) => $komponen->setAttribute(
                'sisa_pagu',
                (float) $komponen->pagu_setahun - (float) $komponen->realisasi_setahun
            ))
            ->groupBy(fn (Rkas $komponen) => $komponen->idbl ?: 'tanpa-kegiatan-'.$komponen->id);

        return view('rkas.penyesuaian-pagu', compact('anggaran', 'komponenPerKegiatan', 'daftarKegiatan', 'daftarKeterangan', 'kegiatanDipilih', 'keteranganDipilih', 'twDipilih', 'twAktif', 'komponenSudahDisesuaikan'));
    }

    public function daftar(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $twAktif = auth()->user()->sekolah?->triwulan_aktif;
        $twDipilih = $request->input('tw', $twAktif ?? 1);
        $request->validate(['tw' => ['nullable', Rule::in(['semua', '0', '1', '2', '3', '4'])]]);

        $penyesuaian = PenyesuaianPagu::query()
            ->with([
                'rincis' => fn ($query) => $query->orderBy('bulan'),
                'rincis.rkas.korek',
            ])
            ->where('anggaran_id', $anggaran->id)
            ->when($twDipilih !== 'semua', fn ($query) => $query->where('tw', $twDipilih))
            ->latest()
            ->get();

        $rekeningRekap = $penyesuaian
            ->flatMap(fn (PenyesuaianPagu $catatan) => $catatan->rincis)
            ->groupBy(fn ($rinci) => $rinci->rkas?->korek?->kode ?? '-')
            ->map(function ($rincis, $kode) {
                return [
                    'kode' => $kode,
                    'singkat' => $rincis->first()->rkas?->korek?->singkat ?? '-',
                    'total' => max(0, $rincis->sum(
                        fn ($rinci) => (float) $rinci->nominal_selisih + (float) $rinci->nominal_ppn
                    )),
                ];
            })
            ->sortBy('kode')
            ->values();

        return view('rkas.penyesuaian-pagu-daftar', compact('anggaran', 'penyesuaian', 'rekeningRekap', 'twDipilih', 'twAktif'));
    }

    public function hapusRinci(Request $request, PenyesuaianPaguRinci $rinci)
    {
        abort_unless(auth()->user()->can('kelola-anggaran'), 403);
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $penyesuaian = $rinci->penyesuaianPagu;
        abort_unless($penyesuaian && (int) $penyesuaian->anggaran_id === (int) $anggaran->id, 404);

        DB::transaction(function () use ($rinci, $penyesuaian) {
            $rinci->delete();

            if (! $penyesuaian->rincis()->exists()) {
                $penyesuaian->delete();
            }
        });

        return redirect()->route('rkas.penyesuaian-pagu.daftar', ['tw' => $penyesuaian->tw])
            ->with('success', 'Rincian penyesuaian berhasil dihapus. Komponen dapat diproses kembali setelah seluruh rincian bulanannya dihapus.');
    }

    public function paguTriwulan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $request->validate(['tw' => ['nullable', 'integer', 'between:1,4']]);
        $twAktif = auth()->user()->sekolah?->triwulan_aktif;
        $twDipilih = (int) $request->input('tw', in_array($twAktif, [1, 2, 3, 4], true) ? $twAktif : 1);
        $data = $this->dataPaguTriwulan($anggaran->id, $twDipilih);

        return view('rkas.penyesuaian-pagu-tw', [
            'anggaran' => $anggaran,
            'twAktif' => $twAktif,
            'twDipilih' => $twDipilih,
            ...$data,
        ]);
    }

    public function exportPaguTriwulan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $request->validate(['tw' => ['required', 'integer', 'between:1,4']]);
        $tw = (int) $request->input('tw');
        $data = $this->dataPaguTriwulan($anggaran->id, $tw);
        $namaFile = sprintf('pagu-tw-%d-hasil-penyesuaian-%s.xlsx', $tw, $anggaran->tahun);

        return Excel::download(new PaguTriwulanExport($data['rekeningRekap']->all(), $data['komponen']->all(), $tw), $namaFile);
    }

    private function dataPaguTriwulan(int $anggaranId, int $tw): array
    {
        $bulanTw = range(($tw - 1) * 3 + 1, $tw * 3);

        $penyesuaianTerbaru = PenyesuaianPaguRinci::query()
            ->whereHas('penyesuaianPagu', fn ($query) => $query->where('anggaran_id', $anggaranId))
            ->orderBy('updated_at')
            ->orderBy('id')
            ->get()
            ->groupBy('idblrinci')
            ->map(fn ($rincis) => $rincis->keyBy('bulan'));

        $komponen = Rkas::query()
            ->with([
                'kegiatan',
                'korek',
                'akbRincis' => fn ($query) => $query->where('anggaran_id', $anggaranId),
            ])
            ->where('anggaran_id', $anggaranId)
            ->get()
            ->map(function (Rkas $item) use ($bulanTw, $penyesuaianTerbaru) {
                $volumeAwal = 0.0;
                foreach ($item->akbRincis->whereIn('bulan', $bulanTw) as $akbRinci) {
                    $volumeAwal += (float) $akbRinci->volume;
                }

                $selisihVolume = 0.0;
                foreach ($bulanTw as $bulan) {
                    $rinciTerbaru = $penyesuaianTerbaru->get($item->idblrinci)?->get($bulan);
                    if ($rinciTerbaru) {
                        $selisihVolume += (float) $rinciTerbaru->volume_selisih;
                    }
                }
                $volumeSetelah = $volumeAwal - $selisihVolume;
                $ppnPersen = (float) $item->totalpajak > 0 ? self::TARIF_PAJAK : 0;
                $hargaSatuan = (float) $item->hargasatuan;
                $paguDasar = $volumeAwal * $hargaSatuan * (1 + $ppnPersen / 100);
                $penyesuaianBersih = ($volumeSetelah - $volumeAwal) * $hargaSatuan * (1 + $ppnPersen / 100);

                return [
                    'idbl' => $item->idbl,
                    'kodegiat' => $item->kegiatan?->kodegiat ?? $item->idbl ?? '-',
                    'namagiat' => $item->kegiatan?->namagiat ?? 'Kegiatan tidak terdefinisi',
                    'idblrinci' => $item->idblrinci,
                    'komponen' => $item->namakomponen ?: 'Komponen tanpa nama',
                    'spek' => $item->spek ?? '',
                    'keterangan' => $item->keterangan ?? '',
                    'satuan' => $item->satuan ?? '',
                    'harga_satuan' => $hargaSatuan,
                    'ppn_persen' => $ppnPersen,
                    'volume_awal' => round($volumeAwal, 2),
                    'volume_setelah' => round($volumeSetelah, 2),
                    'kode_rekening' => $item->korek?->kode ?? '-',
                    'akun' => $item->korek?->singkat ?? '-',
                    'pagu_dasar' => round($paguDasar, 2),
                    'penyesuaian' => round($penyesuaianBersih, 2),
                    'pagu_hasil' => round($volumeSetelah * $hargaSatuan * (1 + $ppnPersen / 100), 2),
                ];
            })
            ->filter(fn (array $item) => $item['volume_awal'] != 0 || $item['volume_setelah'] != 0)
            ->sortBy(['kode_rekening', 'komponen'])
            ->values();

        $rekeningRekap = $komponen
            ->groupBy(fn (array $item) => $item['kode_rekening'])
            ->map(fn ($items, $kode) => [
                'kode' => $kode,
                'akun' => $items->first()['akun'],
                'pagu_dasar' => $items->sum('pagu_dasar'),
                'penyesuaian' => $items->sum('penyesuaian'),
                'pagu_hasil' => $items->sum('pagu_hasil'),
            ])
            ->values();

        return compact('bulanTw', 'komponen', 'rekeningRekap');
    }

    public function proses(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $request->validate([
            'jenis' => ['required', Rule::in(['pergeseran', 'perubahan'])],
            'kegiatan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'idbl')->where(fn ($query) => $query->where('anggaran_id', $anggaran->id)),
            ],
            'keterangan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'keterangan')->where(fn ($query) => $query
                    ->where('anggaran_id', $anggaran->id)
                    ->when($request->input('kegiatan'), fn ($query, $idbl) => $query->where('idbl', $idbl))),
            ],
            'tw' => ['nullable', 'array'],
            'tw.*' => ['required', 'integer', 'distinct', Rule::in([1, 2, 3, 4])],
            'komponen' => ['required', 'array', 'min:1'],
            'komponen.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('rkas', 'id')->where(fn ($query) => $query->where('anggaran_id', $anggaran->id)),
            ],
        ], [
            'komponen.required' => 'Pilih minimal satu komponen untuk diproses.',
            'komponen.min' => 'Pilih minimal satu komponen untuk diproses.',
        ]);

        $twDipilih = collect($request->input('tw', []))->map(fn ($tw) => (int) $tw)->unique()->sort()->values()->all();
        $bulanDipilih = $this->bulanDariTriwulan($twDipilih);
        $komponen = $this->komponenQuery($anggaran->id, $bulanDipilih)
            ->whereIn('id', $request->input('komponen'))
            ->when($request->input('kegiatan'), fn ($query, $idbl) => $query->where('idbl', $idbl))
            ->when($request->input('keterangan') !== null && $request->input('keterangan') !== '', fn ($query) => $query->where('keterangan', $request->input('keterangan')))
            ->get();

        if ($komponen->count() !== count($request->input('komponen'))) {
            throw ValidationException::withMessages(['komponen' => 'Komponen pilihan tidak sesuai dengan filter yang digunakan.']);
        }

        $twAktif = (int) (auth()->user()->sekolah?->triwulan_aktif ?? 0);
        $sudahDisesuaikan = $this->idblrinciSudahDisesuaikan($anggaran->id, $twAktif);
        if ($komponen->contains(fn (Rkas $item) => in_array($item->idblrinci, $sudahDisesuaikan, true))) {
            throw ValidationException::withMessages([
                'komponen' => 'Komponen yang sudah memiliki rincian pada TW aktif harus dihapus terlebih dahulu sebelum diproses kembali.',
            ]);
        }

        $volumeAkbBulanan = $this->getVolumeAkbBulanan($anggaran->id, $komponen);
        $realisasiBulanan = $this->getVolumeRealisasiBulanan($anggaran->id, $komponen);

        $komponen->each(function (Rkas $item) use ($volumeAkbBulanan, $realisasiBulanan) {
            $item->volume_akb = $this->normalisasiVolumeBulanan(
                $volumeAkbBulanan->get($item->idblrinci, collect())
            );
            $item->volume_tersisa = $this->hitungVolumeTersisa(
                $item->volume_akb,
                $realisasiBulanan->get($item->idblrinci, collect())
            );
        });

        return view('rkas.penyesuaian-pagu-hasil', [
            'anggaran' => $anggaran,
            'komponen' => $komponen,
            'tarifPajak' => self::TARIF_PAJAK,
            'jenis' => $request->input('jenis'),
            'kegiatanDipilih' => $request->input('kegiatan'),
            'keteranganDipilih' => (string) $request->input('keterangan', ''),
            'twDipilih' => $twDipilih,
        ]);
    }

    public function simpan(Request $request)
    {
        $anggaran = $request->anggaran_data;
        if (! $anggaran) {
            return redirect()->route('sekolah.index')->with('error', 'Silakan tentukan Anggaran Aktif terlebih dahulu.');
        }

        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['pergeseran', 'perubahan'])],
            'kegiatan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'idbl')->where(fn ($query) => $query->where('anggaran_id', $anggaran->id)),
            ],
            'keterangan' => [
                'nullable',
                'string',
                Rule::exists('rkas', 'keterangan')->where(fn ($query) => $query
                    ->where('anggaran_id', $anggaran->id)
                    ->when($request->input('kegiatan'), fn ($query, $idbl) => $query->where('idbl', $idbl))),
            ],
            'tw' => ['nullable', 'array'],
            'tw.*' => ['required', 'integer', 'distinct', Rule::in([1, 2, 3, 4])],
            'komponen' => ['required', 'array', 'min:1'],
            'komponen.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('rkas', 'id')->where(fn ($query) => $query->where('anggaran_id', $anggaran->id)),
            ],
            'volume' => ['required', 'array'],
            'volume.*' => ['required', 'array'],
            'volume.*.*' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
        ]);

        $twDipilih = collect($request->input('tw', []))->map(fn ($tw) => (int) $tw)->unique()->sort()->values()->all();
        $bulanDipilih = $this->bulanDariTriwulan($twDipilih);
        $komponen = $this->komponenQuery($anggaran->id, $bulanDipilih)
            ->whereIn('id', $validated['komponen'])
            ->when(($validated['keterangan'] ?? '') !== '', fn ($query) => $query->where('keterangan', $validated['keterangan']))
            ->get();

        if ($komponen->count() !== count($validated['komponen'])) {
            throw ValidationException::withMessages(['komponen' => 'Salah satu komponen tidak lagi tersedia pada anggaran aktif.']);
        }

        $twAktif = (int) (auth()->user()->sekolah?->triwulan_aktif ?? 0);
        $sudahDisesuaikan = $this->idblrinciSudahDisesuaikan($anggaran->id, $twAktif);
        if ($komponen->contains(fn (Rkas $item) => in_array($item->idblrinci, $sudahDisesuaikan, true))) {
            throw ValidationException::withMessages([
                'komponen' => 'Komponen yang sudah memiliki rincian pada TW aktif harus dihapus terlebih dahulu sebelum diproses kembali.',
            ]);
        }

        $volumeAkbBulanan = $this->getVolumeAkbBulanan($anggaran->id, $komponen);
        $realisasiBulanan = $this->getVolumeRealisasiBulanan($anggaran->id, $komponen);
        $volumePerubahan = $validated['volume'];

        foreach ($komponen as $item) {
            $inputBulanan = $volumePerubahan[$item->id] ?? [];
            $bulanTerkirim = array_map('intval', array_keys($inputBulanan));
            sort($bulanTerkirim);
            if ($bulanTerkirim !== range(1, 12)) {
                throw ValidationException::withMessages([
                    "volume.{$item->id}" => "Volume {$item->namakomponen} harus diisi untuk bulan 1 sampai 12.",
                ]);
            }

            $volumeAkb = $this->normalisasiVolumeBulanan($volumeAkbBulanan->get($item->idblrinci, collect()));
            $volumeTersisa = $this->hitungVolumeTersisa($volumeAkb, $realisasiBulanan->get($item->idblrinci, collect()));
            $totalVolumeAwal = 0;
            $totalVolumeSetelah = 0;
            foreach (range(1, 12) as $bulan) {
                $totalVolumeAwal += $volumeTersisa[$bulan];
                $totalVolumeSetelah += (float) $inputBulanan[$bulan];
            }

            if ($totalVolumeSetelah > $totalVolumeAwal) {
                throw ValidationException::withMessages([
                    "volume.{$item->id}" => "Total volume setelah penyesuaian untuk {$item->namakomponen} tidak boleh melebihi total sisa volume setahun ({$totalVolumeAwal}). Kurangi volume bulan sumber sebelum menambah bulan tujuan.",
                ]);
            }
        }

        $rincianBerubah = [];
        foreach ($komponen as $item) {
            $volumeAkb = $this->normalisasiVolumeBulanan($volumeAkbBulanan->get($item->idblrinci, collect()));
            $volumeTersisa = $this->hitungVolumeTersisa($volumeAkb, $realisasiBulanan->get($item->idblrinci, collect()));
            $ppnPersen = (float) $item->totalpajak > 0 ? self::TARIF_PAJAK : 0;
            foreach (range(1, 12) as $bulan) {
                $volumeAwal = round((float) $volumeTersisa[$bulan], 2);
                $volumeSetelah = round((float) $volumePerubahan[$item->id][$bulan], 2);
                $volumeSelisih = round($volumeAwal - $volumeSetelah, 2);
                if ($volumeSelisih === 0.0) {
                    continue;
                }

                $nominalSelisih = round($volumeSelisih * (float) $item->hargasatuan, 2);
                $nominalPpn = round($nominalSelisih * $ppnPersen / 100, 2);
                $rincianBerubah[] = [
                    'idblrinci' => $item->idblrinci,
                    'namakomponen' => $item->namakomponen,
                    'satuan' => $item->satuan,
                    'bulan' => $bulan,
                    'harga_satuan' => $item->hargasatuan,
                    'volume_awal' => $volumeAwal,
                    'volume_setelah' => $volumeSetelah,
                    'volume_selisih' => $volumeSelisih,
                    'ppn_persen' => $ppnPersen,
                    'nominal_selisih' => $nominalSelisih,
                    'nominal_ppn' => $nominalPpn,
                    'pagu_dapat_digeser' => max(0, $nominalSelisih + $nominalPpn),
                ];
            }
        }

        if ($rincianBerubah === []) {
            return redirect()->route('rkas.penyesuaian-pagu', array_filter(['kegiatan' => $validated['kegiatan'] ?? null, 'keterangan' => $validated['keterangan'] ?? null, 'tw' => $twDipilih]))
                ->with('info', 'Tidak ada perubahan volume. Tidak ada catatan penyesuaian yang disimpan.');
        }

        $berhasilDisimpan = DB::transaction(function () use ($anggaran, $validated, $rincianBerubah, $twAktif) {
            Anggaran::query()
                ->whereKey($anggaran->id)
                ->lockForUpdate()
                ->firstOrFail();

            $idblrinciDiproses = collect($rincianBerubah)->pluck('idblrinci')->unique()->all();
            $sudahDisesuaikan = PenyesuaianPaguRinci::query()
                ->whereIn('idblrinci', $idblrinciDiproses)
                ->whereHas('penyesuaianPagu', fn ($query) => $query
                    ->where('anggaran_id', $anggaran->id)
                    ->where('tw', $twAktif))
                ->exists();
            if ($sudahDisesuaikan) {
                throw ValidationException::withMessages([
                    'komponen' => 'Komponen yang sudah memiliki rincian pada TW aktif harus dihapus terlebih dahulu sebelum diproses kembali.',
                ]);
            }

            $penyesuaian = PenyesuaianPagu::query()
                ->where('anggaran_id', $anggaran->id)
                ->where('jenis', $validated['jenis'])
                ->where('tw', $twAktif)
                ->lockForUpdate()
                ->first();

            if (! $penyesuaian) {
                $penyesuaian = PenyesuaianPagu::create([
                    'anggaran_id' => $anggaran->id,
                    'user_id' => auth()->id(),
                    'jenis' => $validated['jenis'],
                    'tw' => $twAktif,
                ]);
            }

            foreach ($rincianBerubah as $rincian) {
                $penyesuaian->rincis()->updateOrCreate(
                    [
                        'idblrinci' => $rincian['idblrinci'],
                        'bulan' => $rincian['bulan'],
                    ],
                    $rincian
                );
            }

            return true;
        });

        if (! $berhasilDisimpan) {
            return redirect()->route('rkas.penyesuaian-pagu', array_filter(['kegiatan' => $validated['kegiatan'] ?? null, 'keterangan' => $validated['keterangan'] ?? null, 'tw' => $twDipilih]))
                ->with('info', 'Tidak ada perubahan volume. Tidak ada catatan rincian baru yang disimpan.');
        }

        return redirect()->route('rkas.penyesuaian-pagu', array_filter(['kegiatan' => $validated['kegiatan'] ?? null, 'keterangan' => $validated['keterangan'] ?? null, 'tw' => $twDipilih]))
            ->with('success', 'Perubahan volume berhasil disimpan sebagai catatan. Data RKAS tidak diubah.');
    }

    private function getVolumeRealisasiBulanan(int $anggaranId, $komponen)
    {
        return BelanjaRinci::query()
            ->select('idblrinci', 'bulan')
            ->selectRaw('SUM(belanja_rincis.volume) as volume_realisasi')
            ->whereIn('idblrinci', $komponen->pluck('idblrinci'))
            ->whereHas('belanja', fn ($query) => $query
                ->where('anggaran_id', $anggaranId)
                ->where('status', 'posted'))
            ->groupBy('idblrinci', 'bulan')
            ->get()
            ->groupBy('idblrinci')
            ->map(fn ($items) => $items->keyBy('bulan'));
    }

    private function getVolumeAkbBulanan(int $anggaranId, $komponen)
    {
        return AkbRinci::query()
            ->select('idblrinci', 'bulan')
            ->selectRaw('SUM(akb_rincis.volume) as volume_pagu')
            ->where('anggaran_id', $anggaranId)
            ->whereIn('idblrinci', $komponen->pluck('idblrinci')->unique())
            ->groupBy('idblrinci', 'bulan')
            ->get()
            ->groupBy('idblrinci')
            ->map(fn ($items) => $items->keyBy('bulan'));
    }

    private function normalisasiVolumeBulanan($volumeAkbKomponen)
    {
        return collect(range(1, 12))->mapWithKeys(fn (int $bulan) => [
            $bulan => (float) ($volumeAkbKomponen->get($bulan)->volume_pagu ?? 0),
        ]);
    }

    private function hitungVolumeTersisa($volumeAkb, $realisasiKomponen)
    {
        return $volumeAkb->mapWithKeys(function (float $volumePagu, int $bulan) use ($realisasiKomponen) {
            $volumeRealisasi = (float) ($realisasiKomponen->get($bulan)->volume_realisasi ?? 0);

            return [$bulan => max(0, $volumePagu - $volumeRealisasi)];
        });
    }

    private function bulanDariTriwulan(array $triwulan): ?array
    {
        if ($triwulan === []) {
            return null;
        }

        return collect($triwulan)
            ->flatMap(fn (int $tw) => range(($tw - 1) * 3 + 1, $tw * 3))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function idblrinciSudahDisesuaikan(int $anggaranId, int $tw): array
    {
        return PenyesuaianPaguRinci::query()
            ->whereHas('penyesuaianPagu', fn ($query) => $query
                ->where('anggaran_id', $anggaranId)
                ->where('tw', $tw))
            ->distinct()
            ->pluck('idblrinci')
            ->all();
    }

    private function komponenQuery(int $anggaranId, ?array $bulan = null): Builder
    {
        return Rkas::query()
            ->with(['kegiatan', 'korek'])
            ->withSum(['akbrincis as pagu_setahun' => fn ($query) => $query
                ->where('anggaran_id', $anggaranId)
                ->when($bulan, fn ($query) => $query->whereIn('bulan', $bulan))], 'nominal')
            ->withSum(['belanjaRincis as realisasi_setahun' => function ($query) use ($anggaranId, $bulan) {
                $query->whereHas('belanja', fn ($belanja) => $belanja
                    ->where('anggaran_id', $anggaranId)
                    ->where('status', 'posted'))
                    ->when($bulan, fn ($query) => $query->whereIn('bulan', $bulan))
                    ->select(\Illuminate\Support\Facades\DB::raw('SUM(
                        CASE
                            WHEN (SELECT totalpajak FROM rkas WHERE rkas.idblrinci = belanja_rincis.idblrinci AND rkas.anggaran_id = '.$anggaranId.') > 0
                            THEN (volume * harga_satuan * 1.12)
                            ELSE (volume * harga_satuan)
                        END
                    )'));
            }], 'total_bruto')
            ->where('anggaran_id', $anggaranId)
            ->orderBy('idbl')
            ->orderBy('namakomponen');
    }
}
