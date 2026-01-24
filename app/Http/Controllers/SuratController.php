<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Models\Belanja;
use App\Models\Sekolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use NumberFormatter;
use PhpOffice\PhpWord\TemplateProcessor; // Pastikan ekstensi PHP intl aktif

class SuratController extends Controller
{
    /**
     * Export Data Belanja ke Excel
     */
    public function exportExcel(Request $request)
    {
        // 1. Ambil data anggaran (Asumsi dari Middleware atau Filter request)
        // Jika null, ambil dari user login sebagai fallback
        $anggaran = $request->anggaran_data ?? Auth::user()->sekolah->anggaranAktif;

        // Validasi
        if (! $anggaran) {
            return back()->with('error', 'Data anggaran tidak ditemukan.');
        }

        // 2. Tentukan nama file
        $fileName = 'Laporan_Rincian_Belanja_'.date('YmdHis').'.xlsx';

        // 3. Download Excel
        return Excel::download(new BelanjaExport($anggaran), $fileName);
    }

    /**
     * Cetak Dokumen SPJ Lengkap (Word)
     */
    public function cetakDokumenLengkap($id)
    {
        // 1. Ambil Data Belanja beserta relasi
        $belanja = Belanja::with(['rincis', 'rekanan', 'korek', 'user'])->findOrFail($id);

        // 2. [PERBAIKAN UTAMA] Ambil Data Sekolah sebagai OBJECT (bukan ID)
        $sekolah = Auth::user()->sekolah;

        // Validasi Relasi Sekolah
        if (! $sekolah) {
            // Fallback: Cari manual jika relasi di model User bermasalah
            $sekolah = Sekolah::find(Auth::user()->sekolah_id);

            if (! $sekolah) {
                return back()->with('error', 'Profil sekolah tidak ditemukan. Mohon lengkapi data sekolah.');
            }
        }

        // 3. Cek Template
        $templatePath = storage_path('app/templates/template.docx');
        if (! file_exists($templatePath)) {
            return back()->with('error', 'File template.docx tidak ditemukan di folder storage/app/templates/');
        }

        try {
            // 4. Proses Template
            $templateProcessor = new TemplateProcessor($templatePath);
            Carbon::setLocale('id'); // Set bahasa tanggal ke Indonesia

            // --- A. DATA SEKOLAH (KOP SURAT) ---
            $templateProcessor->setValue('nama_sekolah', strtoupper($sekolah->nama_sekolah));
            $templateProcessor->setValue('alamat', $sekolah->alamat);
            $templateProcessor->setValue('kelurahan', $sekolah->kelurahan);
            $templateProcessor->setValue('kecamatan', $sekolah->kecamatan);
            $templateProcessor->setValue('telepon', $sekolah->telp ?? '-');
            $templateProcessor->setValue('email', $sekolah->email ?? '-');
            $templateProcessor->setValue('kode_pos', $sekolah->kodepos ?? '-');

            // Logic Nama Anggaran & Triwulan
            $namaAnggaran = match ($sekolah->anggaran_aktif) {
                'bos' => 'BOSP',
                'bop' => 'BOP',
                default => $sekolah->anggaran_aktif,
            };

            $singkatanAnggaran = match ($namaAnggaran) {
                'BOSP' => 'Bantuan Operasional Satuan Pendidikan',
                'BOP' => 'Bantuan Operasional Pendidikan',
                default => $namaAnggaran,
            };

            $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
            $tw_romawi = $romawi[$sekolah->triwulan_aktif] ?? $sekolah->triwulan_aktif;
            $tahunAktif = $sekolah->tahun_aktif ?? date('Y');

            // --- B. DATA BELANJA & TANGGAL ---
            $tgl = Carbon::parse($belanja->tanggal)->translatedFormat('d F Y');
            $hariIni = Carbon::parse($belanja->tanggal)->translatedFormat('l'); // Hari (Senin, dll)

            $templateProcessor->setValue('title', $belanja->uraian);
            $templateProcessor->setValue('tahun', $tahunAktif);
            $templateProcessor->setValue('tanggal_permohonan', $tgl);
            $templateProcessor->setValue('tanggal_nego', $tgl);
            $templateProcessor->setValue('tanggal_pesanan', $tgl);
            $templateProcessor->setValue('tanggal_bast', $tgl);

            // --- C. NOMOR SURAT ---
            $templateProcessor->setValue('mohon', '.../PH/'.$belanja->no_bukti);
            $templateProcessor->setValue('nego', '.../NH/'.$belanja->no_bukti);
            $templateProcessor->setValue('no_pesanan', $belanja->no_bukti);
            $templateProcessor->setValue('no_bapb', '.../BAPB/'.$belanja->no_bukti);
            $templateProcessor->setValue('no_bast', '.../SJ/'.$belanja->no_bukti);

            // --- D. DATA REKANAN ---
            $templateProcessor->setValue('nama_rekanan', $belanja->rekanan->nama_rekanan ?? '................');
            $templateProcessor->setValue('alamat_surat', $belanja->rekanan->alamat ?? '-');
            $templateProcessor->setValue('alamat_surat2', $belanja->rekanan->alamat2 ?? '-');
            $templateProcessor->setValue('provinsi_surat', $belanja->rekanan->provinsi ?? 'Jakarta');
            $templateProcessor->setValue('no_telp', $belanja->rekanan->no_telp ?? '-');
            $templateProcessor->setValue('nama_pimpinan', $belanja->rekanan->nama_pimpinan ?? '-');

            // --- E. DATA PEJABAT ---
            $templateProcessor->setValue('sekolah', $sekolah->nama_sekolah);
            $templateProcessor->setValue('nama_kepala', $sekolah->nama_kepala_sekolah);
            $templateProcessor->setValue('nip_kepala', $sekolah->nip_kepala_sekolah);
            $templateProcessor->setValue('nama_pengurus_barang', $sekolah->nama_pengurus_barang);
            $templateProcessor->setValue('nip_pengurus_barang', $sekolah->nip_pengurus_barang);

            // --- F. ISI NARASI ---
            $kodeRekening = $belanja->korek->ket ?? '-';

            // 1. Narasi Permintaan
            $isi_permohonan = "Berdasarkan kebutuhan sekolah yang tertuang pada Anggaran {$namaAnggaran} ({$singkatanAnggaran}) Triwulan {$tw_romawi} Tahun Anggaran {$tahunAktif} dengan Kode Rekening {$kodeRekening} di {$sekolah->nama_sekolah} pada kegiatan {$belanja->uraian}, serta Surat Penawaran Kerja Sama dari ".($belanja->rekanan->nama_rekanan ?? '-').'. Maka dengan ini kami mohon untuk saudara mengirimkan penawaran harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut:';
            $templateProcessor->setValue('isi_permohonan', $isi_permohonan);

            // 2. Narasi Negosiasi
            $isi_nego = 'Berdasarkan Surat Penawaran yang kami terima dari '.($belanja->rekanan->nama_rekanan ?? '-').", serta berdasarkan Anggaran yang kami miliki pada Kode Rekening {$kodeRekening} kegiatan {$belanja->uraian} Tahun Anggaran {$tahunAktif}. Maka dengan ini kami mengajukan negosiasi harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :";
            $templateProcessor->setValue('isi_nego', $isi_nego);

            // 3. Narasi Pesanan
            $isi_pesanan = 'Berdasarkan Surat Kesepakatan Negosiasi yang kami terima dari '.($belanja->rekanan->nama_rekanan ?? '-').", serta berdasarkan Anggaran {$namaAnggaran} Triwulan {$tw_romawi} Tahun Anggaran {$tahunAktif} dengan Kode Rekening {$kodeRekening} di {$sekolah->nama_sekolah} pada kegiatan {$belanja->uraian}. Maka dengan ini kami bermaksud untuk melakukan pemesanan Barang/Jasa sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :";
            $templateProcessor->setValue('isi_pesanan', $isi_pesanan);

            // 4. Narasi BAPB
            $isi_bapb = "Pada hari ini, {$hariIni} tanggal {$tgl}, sesuai dengan:";
            $templateProcessor->setValue('isi_bapb', $isi_bapb);

            // --- G. TABEL RINCIAN (CLONING ROW) ---
            $rincis = $belanja->rincis;
            $count = count($rincis);

            if ($count > 0) {
                // Clone baris tabel
                $templateProcessor->cloneRow('no', $count);
                $templateProcessor->cloneRow('no1', $count);
                $templateProcessor->cloneRow('no2', $count);
                $templateProcessor->cloneRow('no3', $count);

                foreach ($rincis as $index => $item) {
                    $i = $index + 1;
                    $hargaFmt = number_format($item->harga_satuan, 0, ',', '.');
                    $satuan = $item->satuan ?? 'Unit';

                    // Isi data ke 4 tabel sekaligus
                    // Tabel 1: Permintaan
                    $templateProcessor->setValue("no#$i", $i);
                    $templateProcessor->setValue("nama_barang#$i", $item->namakomponen);
                    $templateProcessor->setValue("spek_barang#$i", $item->spek);
                    $templateProcessor->setValue("satuan#$i", $satuan);

                    // Tabel 2: Negosiasi
                    $templateProcessor->setValue("no1#$i", $i);
                    $templateProcessor->setValue("nama_barang1#$i", $item->namakomponen);
                    $templateProcessor->setValue("satuan1#$i", $satuan);
                    $templateProcessor->setValue("harga1#$i", $hargaFmt);
                    $templateProcessor->setValue("nego1#$i", $hargaFmt);

                    // Tabel 3: Pesanan
                    $templateProcessor->setValue("no2#$i", $i);
                    $templateProcessor->setValue("nama_barang2#$i", $item->namakomponen);
                    $templateProcessor->setValue("qty2#$i", $item->volume);
                    $templateProcessor->setValue("satuan2#$i", $satuan);

                    // Tabel 4: BAPB
                    $templateProcessor->setValue("no3#$i", $i);
                    $templateProcessor->setValue("nama_barang3#$i", $item->namakomponen);
                    $templateProcessor->setValue("qty3#$i", $item->volume);
                    $templateProcessor->setValue("satuan3#$i", $satuan);
                }
            } else {
                // Jika rincian kosong, clone 1 baris kosong agar template tidak error
                $templateProcessor->cloneRow('no', 1);
                $templateProcessor->cloneRow('no1', 1);
                $templateProcessor->cloneRow('no2', 1);
                $templateProcessor->cloneRow('no3', 1);
            }

            // --- H. DOWNLOAD ---
            $cleanNoBukti = str_replace(['/', '\\'], '-', $belanja->no_bukti);
            $fileName = 'Dokumen_Lengkap_'.$cleanNoBukti.'.docx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            header('Cache-Control: max-age=0');

            $templateProcessor->saveAs('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses dokumen: '.$e->getMessage());
        }
    }

    /**
     * Helper Function: Terbilang Tahun (Opsional, jika ingin dipakai)
     */
    private function terbilangTahun($tahun)
    {
        $f = new NumberFormatter('id', NumberFormatter::SPELLOUT);

        return ucwords($f->format($tahun));
    }
}
