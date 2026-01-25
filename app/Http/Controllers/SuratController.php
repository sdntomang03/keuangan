<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Models\Belanja;
use App\Models\Sekolah;
use App\Models\Surat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratController extends Controller
{
    /**
     * 1. Export Data Belanja ke Excel
     */
    public function exportExcel(Request $request)
    {
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;

        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        $fileName = 'Laporan_Rincian_Belanja_'.date('YmdHis').'.xlsx';

        return Excel::download(new BelanjaExport($anggaran), $fileName);
    }

    /**
     * 2. Cetak Dokumen SPJ Lengkap (Word)
     * UPDATE: Mengambil Nomor & Tanggal dari tabel 'surats' agar sinkron
     */
    public function cetakDokumenLengkap($id, Request $request)
    {
        // Ambil Data Belanja beserta relasi surats
        $belanja = Belanja::with(['rincis', 'rekanan', 'korek', 'user', 'surats'])->findOrFail($id);
        $sekolah = Auth::user()->sekolah;

        // Validasi Sekolah
        if (! $sekolah) {
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);
            if (! $sekolah) {
                return back()->with('error', 'Profil sekolah tidak ditemukan.');
            }
        }

        // Cek Template
        $templatePath = storage_path('app/templates/template.docx');
        if (! file_exists($templatePath)) {
            return back()->with('error', 'File template.docx tidak ditemukan.');
        }

        try {
            $templateProcessor = new TemplateProcessor($templatePath);
            Carbon::setLocale('id');

            // --- A. DATA SURAT (DARI HASIL GENERATE) ---
            // Helper untuk ambil surat spesifik
            $getSurat = fn ($jenis) => $belanja->surats->where('jenis_surat', $jenis)->first();

            $suratPH = $getSurat('PH');
            $suratNH = $getSurat('NH');
            $suratSP = $getSurat('SP');
            $suratBAPB = $getSurat('BAPB');

            // Warning jika belum generate
            if (! $suratPH || ! $suratNH || ! $suratSP || ! $suratBAPB) {
                return back()->with('error', 'Mohon klik tombol "Generate Surat" terlebih dahulu sebelum mencetak.');
            }

            // Mapping Nomor Surat (Hasil Auto Sequence)
            $templateProcessor->setValue('mohon', $suratPH->nomor_surat);
            $templateProcessor->setValue('nego', $suratNH->nomor_surat);
            $templateProcessor->setValue('no_pesanan', $suratSP->nomor_surat);
            $templateProcessor->setValue('no_bapb', $suratBAPB->nomor_surat);
            // BAST/SJ biasanya ikut nomor BAPB atau nomor manual dari Toko
            $templateProcessor->setValue('no_bast', $belanja->no_bast ?? '-');

            // Mapping Tanggal Surat (Hasil Hitung Mundur Weekdays)
            $fmtDate = fn ($date) => $date ? Carbon::parse($date)->translatedFormat('d F Y') : '-';

            $templateProcessor->setValue('tanggal_permohonan', $fmtDate($suratPH->tanggal_surat));
            $templateProcessor->setValue('tanggal_nego', $fmtDate($suratNH->tanggal_surat));
            $templateProcessor->setValue('tanggal_pesanan', $fmtDate($suratSP->tanggal_surat));
            $templateProcessor->setValue('tanggal_bast', $fmtDate($belanja->tanggal_bast)); // Tanggal BAPB/Hari H

            // --- B. DATA SEKOLAH ---
            $templateProcessor->setValue('nama_sekolah', strtoupper($sekolah->nama_sekolah));
            $templateProcessor->setValue('alamat', $sekolah->alamat);
            $templateProcessor->setValue('kelurahan', $sekolah->kelurahan);
            $templateProcessor->setValue('kecamatan', $sekolah->kecamatan);
            $templateProcessor->setValue('telepon', $sekolah->telp ?? '-');
            $templateProcessor->setValue('email', $sekolah->email ?? '-');
            $templateProcessor->setValue('kode_pos', $sekolah->kodepos ?? '-');

            $anggaran = $request->anggaran_data;
            if (! $anggaran) {
                return back()->with('error', 'Silakan pilih Anggaran Aktif di Pengaturan terlebih dahulu.');
            }
            $namaAnggaran = $anggaran->nama_anggaran;
            $mapAnggaran = [
                'bos' => 'BOSP',
                'bop' => 'BOP',
            ];
            $singkatanAnggaran = $mapAnggaran[strtolower($anggaran->singkatan)] ?? 'LAINNYA';
            $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
            $tw_romawi = $romawi[$sekolah->triwulan_aktif] ?? 'I';
            $tahunAktif = $sekolah->tahun_aktif ?? date('Y');

            $templateProcessor->setValue('tahun', $tahunAktif);

            // --- C. DATA REKANAN ---
            $templateProcessor->setValue('nama_rekanan', $belanja->rekanan->nama_rekanan ?? '................');
            $templateProcessor->setValue('alamat_surat', $belanja->rekanan->alamat ?? '-');
            $templateProcessor->setValue('alamat_surat2', $belanja->rekanan->alamat2 ?? '-');
            $templateProcessor->setValue('provinsi_surat', $belanja->rekanan->provinsi ?? 'Jakarta');
            $templateProcessor->setValue('no_telp', $belanja->rekanan->no_telp ?? '-');
            $templateProcessor->setValue('nama_pimpinan', $belanja->rekanan->nama_pimpinan ?? '-');

            // --- D. DATA PEJABAT (TERMASUK PENGURUS BARANG) ---
            $templateProcessor->setValue('sekolah', $sekolah->nama_sekolah);
            $templateProcessor->setValue('nama_kepala', $sekolah->nama_kepala_sekolah);
            $templateProcessor->setValue('nip_kepala', $sekolah->nip_kepala_sekolah);
            $templateProcessor->setValue('nama_bendahara', $sekolah->nama_bendahara); // Tambahan
            $templateProcessor->setValue('nip_bendahara', $sekolah->nip_bendahara);   // Tambahan
            $templateProcessor->setValue('nama_pengurus_barang', $sekolah->nama_pengurus_barang);
            $templateProcessor->setValue('nip_pengurus_barang', $sekolah->nip_pengurus_barang);

            // --- E. ISI NARASI ---
            $kodeRekening = $belanja->korek->ket ?? '-';
            $dt = Carbon::parse($suratBAPB->tanggal_surat);

            $namaHari = $dt->translatedFormat('l'); // Senin, Selasa...
            $namaBulan = $dt->translatedFormat('F'); // Januari, Februari...

            // Konversi angka ke teks
            $tglText = trim($this->terbilang($dt->day));   // dua puluh lima
            $thnText = trim($this->terbilang($dt->year));  // dua ribu dua puluh enam

            // Susun Kalimat: "hari Minggu tanggal dua puluh lima bulan Januari tahun dua ribu dua puluh enam"
            $tglHariH = "$namaHari tanggal $tglText bulan $namaBulan tahun $thnText";

            // Narasi Permintaan
            $templateProcessor->setValue('isi_permohonan', "Berdasarkan kebutuhan sekolah yang tertuang pada Anggaran {$namaAnggaran} ({$singkatanAnggaran}) Triwulan {$tw_romawi} Tahun Anggaran {$tahunAktif} dengan Kode Rekening {$kodeRekening} di {$sekolah->nama_sekolah} pada kegiatan {$belanja->uraian}, serta Surat Penawaran Kerja Sama dari ".($belanja->rekanan->nama_rekanan ?? '-').'. Maka dengan ini kami mohon untuk saudara mengirimkan penawaran harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut:');

            // Narasi Negosiasi
            $templateProcessor->setValue('isi_nego', 'Berdasarkan Surat Penawaran yang kami terima dari '.($belanja->rekanan->nama_rekanan ?? '-').", serta berdasarkan Anggaran yang kami miliki pada Kode Rekening {$kodeRekening} kegiatan {$belanja->uraian} Tahun Anggaran {$tahunAktif}. Maka dengan ini kami mengajukan negosiasi harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :");

            // Narasi Pesanan
            $templateProcessor->setValue('isi_pesanan', 'Berdasarkan Surat Kesepakatan Negosiasi yang kami terima dari '.($belanja->rekanan->nama_rekanan ?? '-').", serta berdasarkan Anggaran {$namaAnggaran} Triwulan {$tw_romawi} Tahun Anggaran {$tahunAktif} dengan Kode Rekening {$kodeRekening} di {$sekolah->nama_sekolah} pada kegiatan {$belanja->uraian}. Maka dengan ini kami bermaksud untuk melakukan pemesanan Barang/Jasa sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :");

            // Narasi BAPB
            $templateProcessor->setValue('isi_bapb', "Pada hari ini, {$tglHariH}, sesuai dengan:");

            // Title Uraian
            $templateProcessor->setValue('title', $belanja->uraian);

            // --- F. TABEL RINCIAN ---
            $rincis = $belanja->rincis;
            $count = count($rincis);

            if ($count > 0) {
                $templateProcessor->cloneRow('no', $count);
                $templateProcessor->cloneRow('no1', $count);
                $templateProcessor->cloneRow('no2', $count);
                $templateProcessor->cloneRow('no3', $count);

                foreach ($rincis as $index => $item) {
                    $i = $index + 1;
                    $hargaFmt = number_format($item->harga_satuan, 0, ',', '.');
                    $hargaPenawaran = number_format($item->harga_penawaran, 0, ',', '.');
                    $satuan = $item->satuan ?? 'Unit';

                    // Mapping ke 4 Tabel
                    $templateProcessor->setValue("no#$i", $i);
                    $templateProcessor->setValue("nama_barang#$i", $item->namakomponen);
                    $templateProcessor->setValue("spek_barang#$i", $item->spek);
                    $templateProcessor->setValue("satuan#$i", $satuan);

                    $templateProcessor->setValue("no1#$i", $i);
                    $templateProcessor->setValue("nama_barang1#$i", $item->namakomponen);
                    $templateProcessor->setValue("satuan1#$i", $satuan);
                    $templateProcessor->setValue("harga1#$i", $hargaFmt);
                    $templateProcessor->setValue("nego1#$i", $hargaPenawaran);

                    $templateProcessor->setValue("no2#$i", $i);
                    $templateProcessor->setValue("nama_barang2#$i", $item->namakomponen);
                    $templateProcessor->setValue("qty2#$i", $item->volume);
                    $templateProcessor->setValue("satuan2#$i", $satuan);

                    $templateProcessor->setValue("no3#$i", $i);
                    $templateProcessor->setValue("nama_barang3#$i", $item->namakomponen);
                    $templateProcessor->setValue("qty3#$i", $item->volume);
                    $templateProcessor->setValue("satuan3#$i", $satuan);
                }
            } else {
                $templateProcessor->cloneRow('no', 1);
                $templateProcessor->cloneRow('no1', 1);
                $templateProcessor->cloneRow('no2', 1);
                $templateProcessor->cloneRow('no3', 1);
            }

            // --- DOWNLOAD ---
            $cleanNoBukti = str_replace(['/', '\\'], '-', $belanja->no_bukti);
            $fileName = 'Dokumen_Lengkap_'.$cleanNoBukti.'.docx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            header('Cache-Control: max-age=0');

            $templateProcessor->saveAs('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * 3. Halaman Index Manajemen Surat
     */
    public function index($belanjaId)
    {
        $belanja = Belanja::with('surats')->findOrFail($belanjaId);

        $jenisSuratList = [
            'PH' => 'Permintaan Harga',
            'NH' => 'Negosiasi Harga',
            'SP' => 'Surat Pesanan',
            'BAPB' => 'Berita Acara Penerimaan Barang',
        ];

        return view('surat.index', compact('belanja', 'jenisSuratList'));
    }

    /**
     * 4. Generate Otomatis (Weekdays & Auto Number)
     */
    public function generateDefault($belanjaId)
    {
        $belanja = Belanja::findOrFail($belanjaId);
        $baseDate = Carbon::parse($belanja->tanggal);

        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        $triwulanAktif = $user->sekolah->triwulan_aktif ?? 1;

        $timeline = [
            'BAPB' => 0, // Hari H
            'SP' => 1, // H-1
            'NH' => 3, // H-3
            'PH' => 5, // H-5
        ];

        DB::transaction(function () use ($baseDate, $timeline, $belanjaId, $sekolahId, $triwulanAktif) {
            // A. Create Data
            foreach ($timeline as $jenis => $mundur) {
                $exists = Surat::where('belanja_id', $belanjaId)
                    ->where('jenis_surat', $jenis)
                    ->exists();

                if (! $exists) {
                    $tanggalSurat = $baseDate->copy()->subWeekdays($mundur);

                    Surat::create([
                        'sekolah_id' => $sekolahId,
                        'belanja_id' => $belanjaId,
                        'triwulan' => $triwulanAktif,
                        'jenis_surat' => $jenis,
                        'nomor_surat' => 'DRAFT',
                        'tanggal_surat' => $tanggalSurat,
                    ]);
                }
            }

            // B. Re-sequence
            $tahun = $baseDate->format('Y');
            $this->urutkanUlangNomorSurat($sekolahId, $tahun);
        });

        return back()->with('success', 'Surat berhasil digenerate dan diurutkan sesuai tanggal.');
    }

    /**
     * 5. Helper Pengurutan Ulang
     */
    private function urutkanUlangNomorSurat($sekolahId, $tahun)
    {
        $surats = Surat::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal_surat', $tahun)
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $noUrut = 1;
        foreach ($surats as $surat) {
            $strNoUrut = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
            $nomorBaru = "{$strNoUrut}/UD.02.02";

            if ($surat->nomor_surat !== $nomorBaru) {
                $surat->update(['nomor_surat' => $nomorBaru]);
            }
            $noUrut++;
        }
    }

    /**
     * 6. Update Manual
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_surat' => 'required|string',
            'tanggal_surat' => 'required|date',
        ]);

        $surat = Surat::findOrFail($id);

        $surat->update([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
        ]);

        // Urutkan ulang jika tanggal berubah agar konsisten
        $this->urutkanUlangNomorSurat($surat->sekolah_id, $surat->tanggal_surat->format('Y'));

        return back()->with('success', 'Data surat diperbarui.');
    }

    public function store(Request $request, $belanjaId)
    {
        // 1. VALIDASI
        // Kita ubah validasi dari 'rinci_ids' menjadi 'items'
        $request->validate([
            'jenis_surat' => 'required',
            'nomor_surat' => 'required',
            'tanggal_surat' => 'required|date',
            'items' => 'required|array', // Array pembungkus
        ]);

        $belanja = Belanja::findOrFail($belanjaId);
        $user = Auth::user();

        // Gunakan Transaction agar aman
        DB::transaction(function () use ($request, $belanjaId, $user) {

            // 2. BUAT SURAT UTAMA
            $surat = Surat::create([
                'sekolah_id' => $user->sekolah_id,
                'belanja_id' => $belanjaId,
                'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                'jenis_surat' => $request->jenis_surat,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
            ]);

            // 3. PROSES DATA PIVOT (Relasi & Volume)
            // Kita harus memfilter item mana yang dicentang oleh user
            $pivotData = [];

            foreach ($request->items as $rinciId => $data) {
                // Cek apakah checkbox 'selected' dikirim/dicentang?
                if (isset($data['selected'])) {
                    // Format array untuk attach dengan data tambahan (pivot columns)
                    // [ ID_RINCI => ['volume' => NILAI_VOLUME], ... ]
                    $pivotData[$rinciId] = [
                        'volume' => $data['volume'],
                    ];
                }
            }

            // 4. SIMPAN KE TABEL PIVOT (surat_rinci)
            if (! empty($pivotData)) {
                $surat->rincis()->attach($pivotData);
            }
        });

        return back()->with('success', 'Surat parsial berhasil dibuat dengan volume yang disesuaikan.');
    }

    /**
     * Store Paket Parsial (SP + BAPB sekaligus)
     */
    public function storeParsial(Request $request, $belanjaId)
    {
        $request->validate([
            'keterangan' => 'required|string', // Tahap 1, dll
            'nomor_sp' => 'required',
            'tanggal_sp' => 'required|date',
            'nomor_bapb' => 'required',
            'tanggal_bapb' => 'required|date',
            'items' => 'required|array',
        ]);

        $belanja = Belanja::findOrFail($belanjaId);
        $user = Auth::user();

        DB::transaction(function () use ($request, $belanjaId, $user) {

            // 1. SIAPKAN DATA PIVOT (Rincian Barang)
            // Kita siapkan dulu array rinciannya karena akan dipakai oleh KEDUA surat
            $pivotData = [];
            foreach ($request->items as $rinciId => $data) {
                if (isset($data['selected'])) {
                    $pivotData[$rinciId] = ['volume' => $data['volume']];
                }
            }

            if (empty($pivotData)) {
                throw new \Exception('Harus memilih minimal satu barang.');
            }

            // 2. BUAT SURAT PESANAN (SP)
            $sp = Surat::create([
                'sekolah_id' => $user->sekolah_id,
                'belanja_id' => $belanjaId,
                'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                'jenis_surat' => 'SP',
                'nomor_surat' => $request->nomor_sp,
                'tanggal_surat' => $request->tanggal_sp,
                'is_parsial' => true,                // <--- TANDAI PARSIAL
                'keterangan' => $request->keterangan, // <--- TAHAP 1
            ]);
            $sp->rincis()->attach($pivotData); // Hubungkan barang

            // 3. BUAT BERITA ACARA (BAPB)
            $bapb = Surat::create([
                'sekolah_id' => $user->sekolah_id,
                'belanja_id' => $belanjaId,
                'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                'jenis_surat' => 'BAPB',
                'nomor_surat' => $request->nomor_bapb,
                'tanggal_surat' => $request->tanggal_bapb,
                'is_parsial' => true,                // <--- TANDAI PARSIAL
                'keterangan' => $request->keterangan, // <--- TAHAP 1
            ]);
            $bapb->rincis()->attach($pivotData); // Hubungkan barang yang sama
        });

        return back()->with('success', 'Paket surat parsial (SP & BAPB) berhasil dibuat.');
    }

    public function destroy($id)
    {
        $surat = Surat::findOrFail($id);

        // Hapus data pivot (barang rincian) dulu jika ada
        $surat->rincis()->detach();

        // Hapus suratnya
        $surat->delete();

        // Opsional: Urutkan ulang nomor surat jika diperlukan
        // $this->urutkanUlangNomorSurat($surat->sekolah_id, $surat->tanggal_surat->format('Y'));

        return back()->with('success', 'Dokumen surat berhasil dihapus.');
    }

    /**
     * Helper untuk mengubah angka menjadi kalimat (Terbilang)
     */
    private function terbilang($x)
    {
        $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($x < 12) {
            return ' '.$angka[$x];
        } elseif ($x < 20) {
            return $this->terbilang($x - 10).' belas';
        } elseif ($x < 100) {
            return $this->terbilang($x / 10).' puluh'.$this->terbilang($x % 10);
        } elseif ($x < 200) {
            return ' seratus'.$this->terbilang($x - 100);
        } elseif ($x < 1000) {
            return $this->terbilang($x / 100).' ratus'.$this->terbilang($x % 100);
        } elseif ($x < 2000) {
            return ' seribu'.$this->terbilang($x - 1000);
        } elseif ($x < 1000000) {
            return $this->terbilang($x / 1000).' ribu'.$this->terbilang($x % 1000);
        }
    }
}
