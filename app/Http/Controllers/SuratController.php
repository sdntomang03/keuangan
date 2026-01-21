<?php

namespace App\Http\Controllers;

use App\Exports\BelanjaExport;
use App\Models\Belanja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratController extends Controller
{
    public function cetakDokumenLengkap($id)
    {
        // Ambil data belanja beserta relasi yang dibutuhkan
        $belanja = Belanja::with(['rincis', 'rekanan', 'korek', 'user'])->findOrFail($id);
        $setting = auth()->user()->setting; // Data sekolah dari user login

        $templatePath = storage_path('app/templates/template.docx');

        if (! file_exists($templatePath)) {
            return back()->with('error', 'File template tidak ditemukan di storage/app/templates/');
        }

        try {
            $templateProcessor = new TemplateProcessor($templatePath);

            // 1. DATA KOP SURAT
            $templateProcessor->setValue('nama_sekolah', strtoupper($setting->nama_sekolah));
            $templateProcessor->setValue('alamat', $setting->alamat);
            $templateProcessor->setValue('kelurahan', $setting->kelurahan);
            $templateProcessor->setValue('kecamatan', $setting->kecamatan);
            $templateProcessor->setValue('telepon', $setting->telp);
            $templateProcessor->setValue('email', $setting->email);
            $templateProcessor->setValue('kode_pos', $setting->kodepos);

            $anggaran = $setting->anggaran_aktif === 'bos' ? 'BOSP' : ($setting->anggaran_aktif === 'bop' ? 'BOP' : $setting->anggaran_aktif);
            $singkatanAnggaran = $anggaran === 'BOSP' ? 'Bantuan Operasional Satuan Pendidikan' : ($anggaran === 'BOP' ? 'Bantuan Operasional Pendidikan' : $anggaran);
            // Pemetaan angka ke Romawi
            $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

            // Ambil nilai triwulan dari setting dan ubah ke romawi
            $tw_romawi = $romawi[$setting->triwulan_aktif] ?? $setting->triwulan_aktif;
            // 2. HEADER & REKANAN [cite: 2, 6, 11, 21]
            $tgl = Carbon::parse($belanja->tanggal)->translatedFormat('d F Y');
            $templateProcessor->setValue('title', $belanja->uraian);
            $templateProcessor->setValue('nama_rekanan', $belanja->rekanan->nama_rekanan ?? '-');
            $templateProcessor->setValue('alamat_surat', $belanja->rekanan->alamat ?? '-');
            $templateProcessor->setValue('alamat_surat2', '');
            $templateProcessor->setValue('provinsi_surat', $belanja->rekanan->provinsi ?? 'Jakarta');
            $templateProcessor->setValue('hp_surat', $belanja->rekanan->no_telp ?? '-');
            $templateProcessor->setValue('nama_pimpinan', $belanja->rekanan->pimpinan ?? '-');

            // 3. NOMOR & TANGGAL SURAT [cite: 2, 6, 11, 17, 19]
            $templateProcessor->setValue('tanggal_permohonan', $tgl);
            $templateProcessor->setValue('tanggal_nego', $tgl);
            $templateProcessor->setValue('tanggal_pesanan', $tgl);
            $templateProcessor->setValue('tanggal_bast', $tgl);

            $templateProcessor->setValue('mohon', '.../PH/'.$belanja->no_bukti);
            $templateProcessor->setValue('nego', '.../NH/'.$belanja->no_bukti);
            $templateProcessor->setValue('no_pesanan', $belanja->no_bukti);
            $templateProcessor->setValue('no_bapb', '.../BAPB/'.$belanja->no_bukti);
            $templateProcessor->setValue('no_bast', '.../SJ/'.$belanja->no_bukti);

            // 4. DATA PEJABAT & INSTANSI [cite: 4, 14, 21, 25]
            $templateProcessor->setValue('sekolah', $setting->nama_sekolah);
            $templateProcessor->setValue('nama_kepala', $setting->nama_kepala_sekolah);
            $templateProcessor->setValue('nip_kepala', $setting->nip_kepala_sekolah);
            $templateProcessor->setValue('nama_pengurus_barang', $setting->nama_bendahara);
            $templateProcessor->setValue('nip_pengurus_barang', $setting->nip_bendahara);
            $templateProcessor->setValue('tahun', Carbon::parse($belanja->tanggal)->format('Y'));

            $isi_permohonan = 'Berdasarkan kebutuhan sekolah yang tertuang pada Anggaran '
            .$anggaran.' ('
            .$singkatanAnggaran.') Triwulan '
            .$tw_romawi.' Tahun Anggaran '
            .$setting->tahun_aktif
            .' dengan Kode Rekening '.$belanja->korek->ket
            .' di '.$setting->nama_sekolah
            .' pada kegiatan '.$belanja->uraian
            .', serta Surat Penawaran Kerja Sama dari '.$belanja->rekanan->nama_rekanan
            .'. Maka dengan ini kami mohon untuk saudara mengirimkan penawaran harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut:';
            // 5. ISI NARASI [cite: 2, 6, 11, 18]
            $templateProcessor->setValue('isi_permohonan', $isi_permohonan);
            $isi_nego = 'Berdasarkan Surat Penawaran yang kami terima dari '
            .$belanja->rekanan->nama_rekanan
            .', serta berdasarkan Anggaran yang kami miliki pada Kode Rekening '
            .$belanja->korek->ket
            .' kegiatan '.$belanja->uraian
            .' Tahun Anggaran '.$setting->tahun_aktif
            .'. Maka dengan ini kami mengajukan negosiasi harga sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :';
            $templateProcessor->setValue('isi_nego', $isi_nego);
            $isi_pesanan = 'Berdasarkan Surat Kesepakatan Negosiasi yang kami terima dari '
            .$belanja->rekanan->nama_rekanan
            .', serta berdasarkan Anggaran '
            .$anggaran
            .' Triwulan '.$tw_romawi
            .' Tahun Anggaran '.$setting->tahun_aktif
            .' dengan Kode Rekening '.$belanja->korek->ket
            .' di '.$setting->nama_sekolah
            .' pada kegiatan '.$belanja->uraian
            .'. Maka dengan ini kami bermaksud untuk melakukan pemesanan Barang/Jasa sesuai dengan Komponen Barang / Jasa yang kami perlukan sebagai berikut :';
            $templateProcessor->setValue('isi_pesanan', $isi_pesanan);
            $isi_bapb = 'Pada hari ini, Senin tanggal Dua Belas tahun Dua Ribu Dua Puluh Enam, sesuai dengan:';
            $templateProcessor->setValue('isi_bapb', $isi_bapb);
            // 6. TABEL RINCIAN (CLONING BARIS) [cite: 3, 7, 12, 23]
            $rincis = $belanja->rincis;
            $count = count($rincis);

            // Clone baris untuk setiap tabel di template [cite: 3, 7, 12, 23]
            $templateProcessor->cloneRow('no', $count);   // Tabel Permintaan
            $templateProcessor->cloneRow('no1', $count);  // Tabel Negosiasi
            $templateProcessor->cloneRow('no2', $count);  // Tabel Pesanan
            $templateProcessor->cloneRow('no3', $count);  // Tabel BAPB

            foreach ($rincis as $index => $item) {
                $i = $index + 1;
                // Tabel Permintaan Harga
                $templateProcessor->setValue("no#$i", $i);
                $templateProcessor->setValue("nama_barang#$i", $item->namakomponen);
                $templateProcessor->setValue("spek_barang#$i", $item->spek);
                $templateProcessor->setValue("satuan#$i", $item->satuan ?? 'Unit');

                // Tabel Negosiasi
                $templateProcessor->setValue("no1#$i", $i);
                $templateProcessor->setValue("nama_barang1#$i", $item->namakomponen);
                $templateProcessor->setValue("satuan1#$i", $item->satuan ?? 'Unit');
                $templateProcessor->setValue("harga1#$i", number_format($item->harga_satuan, 0, ',', '.'));
                $templateProcessor->setValue("nego1#$i", number_format($item->harga_satuan, 0, ',', '.'));

                // Tabel Pesanan [cite: 12]
                $templateProcessor->setValue("no2#$i", $i);
                $templateProcessor->setValue("nama_barang2#$i", $item->namakomponen);
                $templateProcessor->setValue("qty2#$i", $item->volume);
                $templateProcessor->setValue("satuan2#$i", $item->satuan ?? 'Unit');

                // Tabel BAPB [cite: 23]
                $templateProcessor->setValue("no3#$i", $i);
                $templateProcessor->setValue("nama_barang3#$i", $item->namakomponen);
                $templateProcessor->setValue("qty3#$i", $item->volume);
                $templateProcessor->setValue("satuan3#$i", $item->satuan ?? 'Unit');
            }

            // 7. DOWNLOAD
            $fileName = 'Dokumen_Lengkap_'.str_replace('/', '-', $belanja->no_bukti).'.docx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            $templateProcessor->saveAs('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        // 1. Ambil data anggaran dari request/middleware
        $anggaran = $request->anggaran_data;

        // 2. Tentukan nama file
        $fileName = 'Laporan_Rincian_Belanja_'.date('YmdHis').'.xlsx';

        // 3. Masukkan variabel $anggaran ke dalam Constructor class Export
        return Excel::download(new BelanjaExport($anggaran), $fileName);
    }
}
