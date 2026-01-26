<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Models\Belanja;
use App\Models\BelanjaFoto;
use App\Models\Sekolah;
use App\Models\Surat;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
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
            $templateProcessor->setValue('jenis_anggaran', $namaAnggaran);
            $templateProcessor->setValue('korek', $kodeRekening);
            $templateProcessor->setValue('triwulan', $tw_romawi);
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
                    // $templateProcessor->setValue("spek_barang#$i", $item->spek);
                    $templateProcessor->setValue("satuan#$i", $satuan);

                    $templateProcessor->setValue("no1#$i", $i);
                    $templateProcessor->setValue("nama_barang1#$i", $item->namakomponen);
                    $templateProcessor->setValue("satuan1#$i", $satuan);
                    $templateProcessor->setValue("harga1#$i", $hargaPenawaran);
                    $templateProcessor->setValue("nego1#$i", $hargaFmt);

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
            // --- G. LAMPIRAN FOTO DOKUMENTASI (HANYA FOTO) ---
            $fotos = $belanja->fotos;
            $countFoto = $fotos->count();

            if ($countFoto > 0) {
                // 1. Clone Baris Tabel berdasarkan variabel ${foto}
                $templateProcessor->cloneRow('foto', $countFoto);

                foreach ($fotos as $index => $foto) {
                    $i = $index + 1; // Index dimulai dari 1 (foto#1, foto#2, dst)

                    // Pastikan path fisik file benar
                    $pathFisik = storage_path('app/public/'.$foto->path);

                    // 2. Tempel Gambar
                    if (file_exists($pathFisik)) {
                        $templateProcessor->setImageValue("foto#$i", [
                            'path' => $pathFisik,
                            'width' => 500,        // Lebar diperbesar karena tanpa teks samping
                            'height' => 350,       // Tinggi proporsional
                            'ratio' => true,        // Jaga aspek rasio agar foto tidak gepeng
                        ]);
                    } else {
                        // Fallback: Jika file fisik terhapus, ganti dengan teks error
                        $templateProcessor->setValue("foto#$i", 'File foto fisik tidak ditemukan.');
                    }
                }
            } else {
                // Jika tidak ada foto, hapus placeholder ${foto} agar bersih (kosong)
                $templateProcessor->setValue('foto', '');
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
        $belanja = Belanja::with(['surats', 'rincis', 'fotos'])->findOrFail($belanjaId);

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
            $this->urutkanUlangNomorSurat($sekolahId, $baseDate->format('Y'), $triwulanAktif);
        });

        return back()->with('success', 'Surat berhasil digenerate dan diurutkan sesuai tanggal.');
    }

    /**
     * 5. Helper Pengurutan Ulang
     */
    private function urutkanUlangNomorSurat($sekolahId, $tahun, $triwulanAktif)
    {
        // 1. Ambil data sekolah untuk mendapatkan "nomor_surat" awal/terakhir
        $sekolah = Sekolah::find($sekolahId);

        // Default jika field kosong
        $baseNumber = 0;

        if ($sekolah && $sekolah->nomor_surat) {
            // Ambil angka depan dari field nomor_surat di tabel sekolahs
            // Contoh isi field: "045" atau "045/UD.02.02"
            $parts = explode('/', $sekolah->nomor_surat);
            $baseNumber = (int) $parts[0];
        }

        // 2. Ambil total surat yang sudah ada di triwulan SEBELUMNYA
        // Ini penting agar nomor di TW 2 melanjutkan nomor yang sudah terpakai di TW 1
        $suratTerpakaiLalu = Surat::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal_surat', $tahun)
            ->where('triwulan', '<', $triwulanAktif)
            ->count();

        // 3. Ambil surat di triwulan aktif untuk diurutkan
        $surats = Surat::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal_surat', $tahun)
            ->where('triwulan', $triwulanAktif)
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 4. Hitung Nomor Mulai
        // Rumus: (Nomor di Profil Sekolah) + (Surat yang sudah dibuat di triwulan lalu) + 1
        $noUrut = $baseNumber + $suratTerpakaiLalu + 1;

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
        $this->urutkanUlangNomorSurat($surat->sekolah_id, $surat->tanggal_surat->format('Y'), $surat->triwulan);

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
        // 1. VALIDASI DINAMIS (Sesuai Pilihan Combo Box)
        // Kita buat aturan dasar dulu
        $rules = [
            'jenis_surat' => 'required|in:SP,BAPB',
            'keterangan' => 'required|string', // Tahap 1, dll
            'items' => 'required|array',
        ];

        // Tambahkan aturan khusus jika pilih SP
        if ($request->jenis_surat == 'SP') {
            $rules['nomor_sp'] = 'required';
            $rules['tanggal_sp'] = 'required|date';
        }
        // Tambahkan aturan khusus jika pilih BAPB
        elseif ($request->jenis_surat == 'BAPB') {
            $rules['nomor_bapb'] = 'required';
            $rules['tanggal_bapb'] = 'required|date';
            $rules['no_bast'] = 'required';      // Wajib karena BAPB butuh BAST
            $rules['tanggal_bast'] = 'required|date';
        }

        // Jalankan Validasi
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $belanja = Belanja::findOrFail($belanjaId);
        $user = Auth::user();

        DB::transaction(function () use ($request, $belanjaId, $user) {

            // 2. SIAPKAN DATA PIVOT (Rincian Barang)
            $pivotData = [];
            foreach ($request->items as $rinciId => $data) {
                // Hanya ambil item yang dicentang
                if (isset($data['selected']) && $data['selected'] == 1) {
                    // Pastikan volume ada isinya, kalau kosong anggap 0 atau skip
                    if ($data['volume'] > 0) {
                        $pivotData[$rinciId] = ['volume' => $data['volume']];
                    }
                }
            }

            if (empty($pivotData)) {
                throw new \Exception('Harus memilih minimal satu barang dengan volume > 0.');
            }

            // 3. LOGIKA PENYIMPANAN BERDASARKAN JENIS

            // --- SKENARIO A: BUAT SURAT PESANAN (SP) ---
            if ($request->jenis_surat == 'SP') {
                $sp = Surat::create([
                    'sekolah_id' => $user->sekolah_id,
                    'belanja_id' => $belanjaId,
                    'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                    'jenis_surat' => 'SP',
                    'nomor_surat' => $request->nomor_sp,
                    'tanggal_surat' => $request->tanggal_sp,
                    'is_parsial' => true,
                    'keterangan' => $request->keterangan,
                    'sp_referensi_id' => null, // SP adalah induk, tidak punya referensi
                ]);

                // Simpan Rincian Barang
                $sp->rincis()->attach($pivotData);
            }

            // --- SKENARIO B: BUAT BAPB (& DATA BAST) ---
            elseif ($request->jenis_surat == 'BAPB') {
                $bapb = Surat::create([
                    'sekolah_id' => $user->sekolah_id,
                    'belanja_id' => $belanjaId,
                    'triwulan' => $user->sekolah->triwulan_aktif ?? 1,
                    'jenis_surat' => 'BAPB',
                    'nomor_surat' => $request->nomor_bapb,
                    'tanggal_surat' => $request->tanggal_bapb,

                    // Data BAST (Disimpan di kolom milik BAPB atau Surat)
                    'no_bast' => $request->no_bast,
                    'tanggal_bast' => $request->tanggal_bast,

                    'is_parsial' => true,
                    'keterangan' => $request->keterangan,

                    // Sambungkan ke SP Induk (Jika user memilihnya di dropdown)
                    'sp_referensi_id' => $request->sp_referensi_id ?: null,
                ]);

                // Simpan Rincian Barang
                $bapb->rincis()->attach($pivotData);
            }

        });

        return back()->with('success', 'Data parsial ('.$request->jenis_surat.') berhasil disimpan.');
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

    public function uploadFoto(Request $request, $id)
    {
        $request->validate(['foto' => 'required|image|max:10240']);

        // 1. Ambil Data Relasi Berantai
        $belanja = Belanja::with('anggaran.sekolah', 'surats')->findOrFail($id);
        $objSekolah = $belanja->anggaran->sekolah;

        // 2. Olah Data Teks
        $namaSekolah = strtoupper($objSekolah->nama_sekolah ?? 'NAMA SEKOLAH');
        $alamatSekolah = strtoupper($objSekolah->alamat_sekolah ?? $objSekolah->alamat ?? 'ALAMAT SEKOLAH');
        $alamatSekolah2 = strtoupper('Kel. '.($objSekolah->kelurahan ?? '-').', Kec. '.($objSekolah->kecamatan ?? '-'));
        $lat = $objSekolah->latitude ?? '-6.176717';
        $lng = $objSekolah->longitude ?? '106.796351';

        // 3. Generate Waktu Acak
        $detikAcak = sprintf('%02d', rand(0, 59));

        // 2. Cek apakah ada input waktu dari form
        if ($request->has('waktu_foto') && $request->waktu_foto != null) {
            // Ambil input form (format H:i, misal 10:30) dan gabungkan dengan detik acak
            $waktuAcak = $request->waktu_foto.':'.$detikAcak;
        } else {
            // Cadangan: Jika form kosong, random jam 9-15 seperti sebelumnya
            $waktuAcak = sprintf('%02d:%02d:%02d', rand(9, 15), rand(0, 59), $detikAcak);
        }

        // 4. Ambil Tanggal BAST
        $suratBast = $belanja->surats->where('jenis_surat', 'BAPB')->first();
        $tanggalBast = $suratBast && $suratBast->tanggal_surat
            ? $suratBast->tanggal_surat->translatedFormat('l, d F Y')
            : now()->translatedFormat('l, d F Y');

        // 5. Inisialisasi Image Manager
        $file = $request->file('foto');
        $manager = new ImageManager(new Driver);
        $img = $manager->read($file);

        // 6. Standardisasi Canvas (1600px agar huruf & peta proporsional)
        $img->scale(width: 1200);
        $width = $img->width();
        $height = $img->height();

        $bgHeight = 300;
        $backgroundLayer = $manager->create($width, $bgHeight)->fill('rgba(0, 0, 0, 0.5)');

        // Tempelkan di bagian paling bawah gambar
        $img->place($backgroundLayer, 'bottom-center');

        // 7. Tambahkan Peta Statis (OpenStreetMap via Yandex Static)
        try {
            // Kita ambil peta ukuran 350x350 agar terlihat jelas
            $mapUrl = "https://static-maps.yandex.ru/1.x/?lang=en_US&ll=$lng,$lat&z=16&l=map&size=250,250&pt=$lng,$lat,pm2rdm";
            $mapContent = file_get_contents($mapUrl);
            if ($mapContent) {
                $mapImage = $manager->read($mapContent);
                // Tempel di pojok kanan bawah dengan margin 20px
                $img->place($mapImage, 'bottom-right', 20, 20);
            }
        } catch (\Exception $e) {
            // Jika internet mati, proses lanjut tanpa peta
        }

        // 8. Setting Ukuran Watermark
        $fontSizeLarge = 36;
        $fontSizeSmall = 26;
        $padding = 60;
        $fontReg = public_path('fonts/Roboto-Regular.ttf');

        // 10. Render Teks Baris 1: Judul
        $img->text(strtoupper($belanja->uraian), $padding, $height - 210, function ($font) use ($fontSizeLarge, $fontReg) {
            if (file_exists($fontReg)) {
                $font->filename($fontReg);
            }
            $font->size($fontSizeLarge);
            $font->color('ffffff');
            $font->valign('top');
        });

        // 11. Render Teks Baris 2-5: Detail
        $watermarkDetail = "$tanggalBast $waktuAcak\n$alamatSekolah ($lat, $lng)\n$alamatSekolah2\n$namaSekolah";

        $img->text($watermarkDetail, $padding, $height - 140, function ($font) use ($fontSizeSmall, $fontReg) {
            if (file_exists($fontReg)) {
                $font->filename($fontReg);
            }
            $font->size($fontSizeSmall);
            $font->lineHeight(1.8); // Disesuaikan agar 4 baris detail rapi
            $font->color('ffffff');
            $font->valign('top');
        });

        // 12. Simpan File & Database
        $filename = time().'.jpg';
        $path = 'dokumentasi/'.$filename;
        Storage::disk('public')->put($path, $img->toJpeg(80));

        $belanja->fotos()->create([
            'path' => $path,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        return back()->with('success', 'Foto SPJ Berhasil diunggah dengan Peta.');
    }

    public function destroyFoto($id)
    {
        $foto = BelanjaFoto::findOrFail($id);
        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        return back()->with('success', 'Foto berhasil dihapus');
    }

    public function cetakSpParsial($id)
    {
        // 1. AMBIL DATA SP YANG DIPILIH
        $suratDipilih = Surat::with(['belanja.rekanan', 'belanja.korek', 'belanja.user.sekolah'])
            ->findOrFail($id);

        $belanja = $suratDipilih->belanja;
        $sekolah = $belanja->user->sekolah;
        $rekanan = $belanja->rekanan;

        // 2. AMBIL BARANG DARI SEMUA BAPB TERKAIT
        $daftarBapb = Surat::where('belanja_id', $belanja->id)
            ->where('jenis_surat', 'BAPB')
            ->with(['rincis.rkas'])
            ->orderBy('tanggal_surat', 'asc')
            ->get();

        // 3. LOAD TEMPLATE
        $pathTemplate = storage_path('app/templates/template_surat_pesanan.docx');
        if (! file_exists($pathTemplate)) {
            return back()->with('error', 'Template surat pesanan tidak ditemukan.');
        }

        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($pathTemplate);
            \Carbon\Carbon::setLocale('id');

            // --- A. HEADER (KOP SURAT) ---
            $templateProcessor->setValue('nama_sekolah', strtoupper($sekolah->nama_sekolah));
            $templateProcessor->setValue('alamat', $sekolah->alamat);
            $templateProcessor->setValue('kelurahan', $sekolah->kelurahan ?? '-');
            $templateProcessor->setValue('kecamatan', $sekolah->kecamatan ?? '-');
            $templateProcessor->setValue('telepon', $sekolah->no_telp ?? '-');
            $templateProcessor->setValue('email', $sekolah->email ?? '-');
            $templateProcessor->setValue('kode_pos', $sekolah->kodepos ?? '-');

            // --- B. INFO SURAT ---
            $templateProcessor->setValue('no_pesanan', $suratDipilih->nomor_surat);
            $templateProcessor->setValue('tanggal_pesanan', $suratDipilih->tanggal_surat->translatedFormat('d F Y'));
            $templateProcessor->setValue('title', $belanja->uraian);

            // Data Rekanan
            $templateProcessor->setValue('nama_rekanan', $rekanan->nama_rekanan);
            $templateProcessor->setValue('alamat_surat', $rekanan->alamat);
            $templateProcessor->setValue('alamat_surat2', $rekanan->kota ?? 'Jakarta');
            $templateProcessor->setValue('provinsi_surat', 'DKI Jakarta');

            // --- C. ISI PESANAN (UPDATE PERMINTAAN ANDA) ---

            // 1. Siapkan Variabel Pendukung
            $mapRomawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
            $tw_romawi = $mapRomawi[$belanja->triwulan ?? 1] ?? 'I';

            $tahunAktif = $sekolah->tahun_aktif ?? date('Y');
            $kodeRekening = $belanja->korek->ket ?? '-';
            $namaAnggaran = $belanja->sumber_dana ?? 'BOSP'; // Default BOSP jika null
            $namaRekanan = $rekanan->nama_rekanan ?? '-';

            // 2. Susun Kalimat (Concatenation)
            $isiPesanan = "Berdasarkan Surat Kesepakatan Negosiasi yang kami terima dari {$namaRekanan}, serta berdasarkan Anggaran {$namaAnggaran} Triwulan {$tw_romawi} Tahun Anggaran {$tahunAktif} dengan Kode Rekening {$kodeRekening} di {$sekolah->nama_sekolah} pada kegiatan {$belanja->uraian}. Maka dengan ini kami bermaksud untuk melakukan pemesanan Barang/Jasa sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :";

            // 3. Set Value ke Template
            $templateProcessor->setValue('isi_pesanan', $isiPesanan);

            // --- D. TABEL BARANG ---
            $dataRows = [];
            $nomorUrut = 1;

            foreach ($daftarBapb as $bapb) {
                $tanggalKirim = $bapb->tanggal_surat->translatedFormat('d F Y');

                foreach ($bapb->rincis as $item) {
                    $volumeParsial = $item->pivot->volume;

                    if ($volumeParsial <= 0) {
                        continue;
                    }

                    $satuan = $item->rkas ? $item->rkas->satuan : $item->satuan;

                    $dataRows[] = [
                        'krm' => $nomorUrut++,
                        'tanggal_kirim' => $tanggalKirim,
                        'barang' => $item->namakomponen,
                        'jml' => number_format($volumeParsial, 0, ',', '.'),
                        'sat' => $satuan,
                    ];
                }
            }

            if (count($dataRows) > 0) {
                $templateProcessor->cloneRowAndSetValues('krm', $dataRows);
            } else {
                $templateProcessor->cloneRowAndSetValues('krm', [['krm' => '-', 'tanggal_kirim' => '-', 'barang' => 'Belum ada realisasi', 'jml' => '0', 'sat' => '-']]);
            }

            // --- E. FOOTER ---
            $templateProcessor->setValue('sekolah', $sekolah->nama_sekolah);
            $templateProcessor->setValue('nama_kepala', $sekolah->nama_kepala_sekolah);
            $templateProcessor->setValue('nip_kepala', $sekolah->nip_kepala_sekolah);

            // --- DOWNLOAD ---
            $filename = 'SP_'.str_replace(['/', '\\'], '-', $suratDipilih->nomor_surat).'.docx';

            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            $templateProcessor->saveAs('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencetak: '.$e->getMessage());
        }
    }

    public function cetakBapbParsial($id)
    {
        // 1. AMBIL DATA BAPB YANG DIPILIH
        // Load relasi user->sekolah karena BAPB butuh data pengurus barang
        $suratDipilih = Surat::with(['rincis.rkas', 'belanja.rekanan', 'belanja.korek', 'belanja.user.sekolah'])
            ->findOrFail($id);

        $belanja = $suratDipilih->belanja;
        $sekolah = $belanja->user->sekolah;
        $rekanan = $belanja->rekanan;

        // Pastikan ini BAPB
        if ($suratDipilih->jenis_surat != 'BAPB') {
            return back()->with('error', 'Surat ini bukan BAPB.');
        }

        // 2. LOAD TEMPLATE
        $pathTemplate = storage_path('app/templates/template_surat_bapb.docx');
        if (! file_exists($pathTemplate)) {
            return back()->with('error', 'Template BAPB tidak ditemukan.');
        }

        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($pathTemplate);
            \Carbon\Carbon::setLocale('id');

            // --- A. HEADER (KOP SURAT) ---
            $templateProcessor->setValue('nama_sekolah', strtoupper($sekolah->nama_sekolah));
            $templateProcessor->setValue('alamat', $sekolah->alamat);
            $templateProcessor->setValue('kelurahan', $sekolah->kelurahan ?? '-');
            $templateProcessor->setValue('kecamatan', $sekolah->kecamatan ?? '-');
            $templateProcessor->setValue('telepon', $sekolah->no_telp ?? '-');
            $templateProcessor->setValue('email', $sekolah->email ?? '-');
            $templateProcessor->setValue('kode_pos', $sekolah->kodepos ?? '-');

            // --- B. INFO BAPB ---
            $templateProcessor->setValue('no_bapb1', $suratDipilih->nomor_surat);

            // Logic Kalimat Pembuka "Pada hari ini..."
            $dt = $suratDipilih->tanggal_surat;
            $hari = $dt->translatedFormat('l');
            $dt = Carbon::parse($suratDipilih->tanggal_surat);

            $namaHari = $dt->translatedFormat('l'); // Senin, Selasa...
            $namaBulan = $dt->translatedFormat('F'); // Januari, Februari...

            // Konversi angka ke teks
            $tglText = trim($this->terbilang($dt->day));   // dua puluh lima
            $thnText = trim($this->terbilang($dt->year));  // dua ribu dua puluh enam

            // Susun Kalimat: "hari Minggu tanggal dua puluh lima bulan Januari tahun dua ribu dua puluh enam"
            $tglHariH = "$namaHari tanggal $tglText bulan $namaBulan tahun $thnText";

            $templateProcessor->setValue('isi_bapb1', "Pada hari ini, {$tglHariH}, sesuai dengan:");

            // Tabel Detail
            $templateProcessor->setValue('no_bast1', $suratDipilih->no_bast ?? '-');
            // Gunakan tanggal BAST jika ada, jika tidak gunakan tanggal BAPB
            $tglBast = $suratDipilih->tanggal_bast ? \Carbon\Carbon::parse($suratDipilih->tanggal_bast) : $dt;
            $templateProcessor->setValue('tanggal_kirim1', $tglBast->translatedFormat('d F Y'));
            $templateProcessor->setValue('title', $belanja->uraian);
            $templateProcessor->setValue('tahun', $dt->format('Y'));

            // --- C. PIHAK PIHAK ---
            // Pihak 1 (Sekolah - Pengurus Barang)
            $templateProcessor->setValue('nama_pengurus_barang', $sekolah->nama_pengurus_barang ?? '-');
            $templateProcessor->setValue('nip_pengurus_barang', $sekolah->nip_pengurus_barang ?? '-');
            $templateProcessor->setValue('sekolah', $sekolah->nama_sekolah);
            // $templateProcessor->setValue('alamat', $sekolah->alamat); // Sudah di set di header

            // Pihak 2 (Rekanan)
            $templateProcessor->setValue('nama_pimpinan', $rekanan->nama_pimpinan);
            $templateProcessor->setValue('nama_rekanan', $rekanan->nama_rekanan);
            $templateProcessor->setValue('alamat_surat', $rekanan->alamat);
            $templateProcessor->setValue('hp_surat', $rekanan->no_telp ?? '-'); // Sesuai template ${hp_surat}

            // --- D. TABEL RINCIAN BARANG ---
            // Khusus BAPB, kita HANYA mencetak barang yang ada di surat ini saja (rincis)
            // Tidak perlu mengambil dari BAPB lain.

            $dataRows = [];
            $nomorUrut = 1;

            foreach ($suratDipilih->rincis as $item) {

                // Ambil Volume Parsial dari Pivot
                $volumeParsial = $item->pivot->volume;

                if ($volumeParsial <= 0) {
                    continue;
                }

                // Logic Satuan
                $satuan = $item->rkas ? $item->rkas->satuan : $item->satuan;
                $qtyFormat = number_format($volumeParsial, 0, ',', '.');

                $dataRows[] = [
                    'krm1' => $nomorUrut++,
                    'barang1' => $item->namakomponen,
                    'jml1' => $qtyFormat, // Jumlah Dipesan = Jumlah Diterima = Jumlah Sesuai
                    'sat1' => $satuan,
                ];
            }

            // Clone Baris Tabel (Target variabel: ${krm1})
            if (count($dataRows) > 0) {
                $templateProcessor->cloneRowAndSetValues('krm1', $dataRows);
            } else {
                $templateProcessor->cloneRowAndSetValues('krm1', [[
                    'krm1' => '-',
                    'barang1' => 'Tidak ada barang',
                    'jml1' => '0',
                    'sat1' => '-',
                ]]);
            }

            // --- E. DOWNLOAD FILE ---
            $filename = 'BAPB_'.str_replace(['/', '\\'], '-', $suratDipilih->nomor_surat).'.docx';

            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            $templateProcessor->saveAs('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencetak BAPB: '.$e->getMessage());
        }
    }

    public function cetakFotoSpj($id)
    {
        // 1. Ambil Data
        $belanja = Belanja::with(['fotos', 'user.sekolah', 'korek', 'anggaran'])->findOrFail($id);

        if ($belanja->fotos->isEmpty()) {
            return back()->with('error', 'Belum ada foto dokumentasi yang diunggah.');
        }

        $sekolah = $belanja->user->sekolah;

        // 2. Data Tambahan
        $mapRomawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        $triwulan = $mapRomawi[$belanja->triwulan ?? 1] ?? 'I';
        $tahun = $sekolah->tahun_aktif ?? date('Y');

        // 3. Load View PDF (GUNAKAN ALIAS 'DomPdf' DISINI)
        // Perhatikan: Menggunakan DomPdf::loadView, bukan Pdf::loadView
        $pdf = DomPdf::loadView('surat.pdf_foto_spj', compact('belanja', 'sekolah', 'triwulan', 'tahun'));

        // Set ukuran kertas
        $pdf->setPaper('a4', 'portrait');

        // 4. Download / Stream
        return $pdf->stream('Dokumentasi_SPJ_'.$belanja->id.'.pdf');
    }

    public function regenerateAllNumbers()
    {
        // Gunakan Transaction biar aman datanya
        DB::transaction(function () {
            // 1. Ambil semua kombinasi Sekolah, Tahun, dan Triwulan yang unik dari tabel surats
            // Kita butuh grup ini agar pengurutan per sekolah/triwulan tidak tercampur
            $groups = Surat::select(
                'sekolah_id',
                'triwulan',
                DB::raw('YEAR(tanggal_surat) as tahun')
            )
                ->whereNotNull('tanggal_surat') // Pastikan tanggal valid
                ->distinct() // Ambil yang unik saja
                ->get();

            // 2. Loop setiap grup dan jalankan pengurutan ulang
            foreach ($groups as $group) {
                // Panggil fungsi private yang sudah ada
                $this->urutkanUlangNomorSurat(
                    $group->sekolah_id,
                    $group->tahun,
                    $group->triwulan
                );
            }
        });

        return back()->with('success', 'Semua nomor surat di database berhasil diurutkan ulang.');
    }
}
