<?php

use App\Http\Controllers\Admin\AnggaranController;
use App\Http\Controllers\Admin\KorekController;
use App\Http\Controllers\Admin\RkasCleanupController;
use App\Http\Controllers\Admin\SekolahController as AdminSekolahController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AkbController;
use App\Http\Controllers\Api\ApiJsonController;
use App\Http\Controllers\ArkasController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\EkskulLaporanController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KegiatanManualController;
use App\Http\Controllers\NpdController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PersediaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\RekananController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StsController;
use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// ZONA 1: PUBLIK (Tanpa Login)
// =========================================================================
Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// ZONA 2: SEMUA USER YANG LOGIN
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/anggaran/switch', [DashboardController::class, 'switch'])->name('anggaran.switch');
    Route::get('/cetak-cover', [CetakController::class, 'cetakCover'])->name('cetak.cover');
    Route::post('/anggaran/switch-tw', [DashboardController::class, 'switchTw'])->name('anggaran.switch-tw');
});

// =========================================================================
// ZONA NEW: KHUSUS INPUT TRANSAKSI BELANJA & TALANGAN
// Permission: 'input-belanja'
// =========================================================================
Route::middleware(['permission:input-belanja'])->group(function () {

    // Form Tambah, Edit, Simpan, dan Hapus Belanja
    Route::get('/belanja/create', [BelanjaController::class, 'create'])->name('belanja.create');
    Route::post('/belanja', [BelanjaController::class, 'store'])->name('belanja.store');
    Route::get('/belanja/{id}/edit', [BelanjaController::class, 'edit'])->name('belanja.edit');
    Route::put('/belanja/{id}', [BelanjaController::class, 'update'])->name('belanja.update');
    Route::delete('/belanja/{id}', [BelanjaController::class, 'destroy'])->name('belanja.destroy');

    // Fitur Ekstra Manipulasi Belanja
    Route::post('/{id}/post', [BelanjaController::class, 'post'])->name('belanja.post');
    Route::post('/belanja/{id}/duplicate', [BelanjaController::class, 'duplicate'])->name('belanja.duplicate');
    Route::get('/{id}/edit-penawaran', [BelanjaController::class, 'editPenawaran'])->name('belanja.edit_penawaran');
    Route::put('/{id}/update-penawaran', [BelanjaController::class, 'updatePenawaran'])->name('belanja.update_penawaran');
    Route::post('/{id}/upload-foto', [SuratController::class, 'uploadFoto'])->name('belanja.upload_foto');

    // Input Jurnal Talangan
    Route::get('/surat/talangan', [SuratController::class, 'createTalangan'])->name('talangan.create');
    Route::post('/surat/talangan/store', [SuratController::class, 'storeTalangan'])->name('surat.talangan.store');
    Route::delete('/surat/talangan/{surat_id}', [SuratController::class, 'destroyTalangan'])->name('surat.talangan.destroy');
    Route::delete('/surat/talangan-npd/{id}', [SuratController::class, 'hapusSurat'])->name('surat.hapus_talangan_npd');
});

// =========================================================================
// ZONA 3A: KEUANGAN (AKSES BACA / VIEW-ANGGARAN)
// Tambahkan 'input-belanja' ke dalam canany agar pengguna input juga bisa melihat halaman index
// =========================================================================
Route::middleware(['permission:view-anggaran|kelola-anggaran|input-belanja'])->group(function () {

    // RKAS & AKB (Read Only)
    Route::get('/rkas', [RkasController::class, 'index'])->name('rkas.index');
    Route::get('/rkas/anggaran', [RkasController::class, 'anggaran'])->name('rkas.anggaran');
    Route::get('/rkas/rekap', [RkasController::class, 'rekap'])->name('rkas.rekap');
    Route::get('/rkas/rincian', [RkasController::class, 'rincian'])->name('rkas.rincian');
    Route::get('/rkas/cetak-laporan', [RkasController::class, 'cetakLaporan'])->name('rkas.cetak_laporan');

    Route::get('/akb', [AkbController::class, 'index'])->name('akb.index');
    Route::get('/akb/rincian', [AkbController::class, 'rincian'])->name('akb.rincian');
    Route::get('/akb/rincianakb', [AkbController::class, 'indexRincian'])->name('akb.indexrincian');
    Route::get('/akb/export-excel', [AkbController::class, 'exportExcel'])->name('akb.export_excel');
    Route::get('/akb/satuan', [AkbController::class, 'satuan'])->name('akb.satuan');
    Route::get('/akb/ringkas', [AkbController::class, 'ringkas'])->name('akb.ringkas');
    Route::get('/akb/perbandingan', [AkbController::class, 'indexPerbandingan'])->name('akb.perbandingan.index');
    Route::get('/api/rkas/{anggaranId}/data', [AkbController::class, 'getData'])->name('api.rkas.data');

    // Belanja (Read Only untuk Hak Lihat)
    Route::get('/belanja', [BelanjaController::class, 'index'])->name('belanja.index');
    Route::get('/belanja/{id}', [BelanjaController::class, 'show'])->name('belanja.show');
    Route::get('/belanja/{id}/json', [BelanjaController::class, 'getJson'])->name('belanja.json');

    Route::prefix('api')->group(function () {
        Route::get('/get-rekening', [BelanjaController::class, 'getRekening'])->name('api.rekening');
        Route::get('/get-komponen', [BelanjaController::class, 'getKomponen'])->name('api.komponen');
        Route::get('/get-keterangan', [BelanjaController::class, 'getKeterangan'])->name('get_keterangan');
        Route::get('/rekanan', [RekananController::class, 'getRekananApi'])->name('api.rekanan');
        Route::post('/get-riwayat-komponen', [BelanjaController::class, 'getRiwayatKomponen']);
    });

    // BKU, STS, PAJAK (Read Only)
    Route::get('/bku', [BkuController::class, 'index'])->name('bku.index');
    Route::get('/sts', [StsController::class, 'index'])->name('sts.index');
    Route::get('/pajak/siap-setor', [PajakController::class, 'siapSetor'])->name('pajak.siap-setor');
    Route::get('/pajak/rekap', [PajakController::class, 'rekap'])->name('pajak.rekap');

    // Realisasi & Rekap
    Route::get('/realisasi/komponen', [RealisasiController::class, 'komponen'])->name('realisasi.komponen');
    Route::get('/realisasi/korek', [RealisasiController::class, 'korek'])->name('realisasi.korek');
    Route::get('/realisasi/jenis-belanja', [RealisasiController::class, 'jenisBelanja'])->name('realisasi.jenis-belanja');
    Route::get('/realisasi/komponen/export', [RealisasiController::class, 'exportKomponen'])->name('realisasi.komponen.export');
    Route::get('/realisasi/laporan-spj', [RealisasiController::class, 'viewLaporanSpj'])->name('realisasi.spj.view');
    Route::get('/realisasi/laporan-spj/pdf', [RealisasiController::class, 'pdfLaporanSpj'])->name('realisasi.spj.pdf');
    Route::get('/rekap/export', [RealisasiController::class, 'exportExcel'])->name('belanja.export');
    Route::get('/rekap/rekanan', [RealisasiController::class, 'rekapPerRekanan'])->name('realisasi.rekanan');
    Route::get('/rekap-rekanan/export-semua', [RealisasiController::class, 'exportSemuaRekanan'])->name('rekap-rekanan.export-semua');
    Route::get('/rekap/rekanan/export/{id}', [RealisasiController::class, 'exportDetailRekanan'])->name('rekap.rekanan.export_detail');

    // NPD (Read Only)
    Route::get('/npd', [NpdController::class, 'index'])->name('npd.index');
    Route::get('/npd/export', [NpdController::class, 'exportExcel'])->name('npd.export');
    Route::get('/api/npd/cek-saldo', [NpdController::class, 'getSaldoAnggaran'])->name('api.npd.saldo');

    // Surat & Cetak
    Route::get('/surat/manage/{belanjaId}', [SuratController::class, 'index'])->name('surat.index');
    Route::get('/surat/cetak-satuan/{id}/{jenis}', [SuratController::class, 'cetakSatuan'])->name('surat.cetak_satuan');
    Route::get('/surat/cetaksatuanpdf/{id}/{jenis}', [SuratController::class, 'cetakSatuanPdf'])->name('surat.cetakSatuanPdf');
    Route::get('/surat/cetakparsialpdf/{id}', [SuratController::class, 'cetakParsialPdf'])->name('surat.cetakParsialPdf');
    Route::get('/surat/download-semua-parsial/{belanjaId}', [SuratController::class, 'downloadSemuaParsial'])->name('surat.download_semua_parsial');
    Route::get('/surat/rekap-surat', [SuratController::class, 'rekapKeseluruhanTriwulanPdf'])->name('surat.rekap_triwulan');
    Route::get('/surat/daftar', [SuratController::class, 'daftarSurat'])->name('surat.daftar');
    Route::post('/surat/download-banyak', [SuratController::class, 'downloadBanyakPdf'])->name('surat.download_banyak');
    Route::post('/surat/hapus-banyak', [SuratController::class, 'hapusBanyakSurat'])->name('surat.hapus_banyak');
    Route::get('/surat/talangan-pdf/{talanganId}', [SuratController::class, 'cetakTalanganPdf'])->name('surat.talangan_pdf');
    Route::get('/surat/talangan-npd', [SuratController::class, 'daftarTalanganNpd'])->name('surat.daftar_talangan_npd');
    Route::get('/surat/cover-lpj', [SuratController::class, 'createCoverLpj'])->name('surat.cover_lpj.create');
    Route::post('/surat/cover-lpj/cetak', [SuratController::class, 'generateCoverPdf'])->name('surat.cover_lpj.generate');
    Route::get('/belanja/cetak-foto/{id}', [SuratController::class, 'cetakFotoSpj'])->name('belanja.cetak_foto');
    Route::get('/belanja/export-excel', [SuratController::class, 'exportExcel'])->name('belanja.export_excel');
    Route::get('/belanja/{id}/download-bundel', [SuratController::class, 'downloadBundel'])->name('belanja.downloadBundel');
    Route::get('/cetak/kop', [SuratController::class, 'cetakKopPdf'])->name('cetak.kop');
    Route::get('/surat/download-normal-zip/', [\App\Http\Controllers\SuratController::class, 'downloadSemuaNormalZip'])->name('surat.download_normal_zip');

    // Barang, Persediaan & Arkas (Read Only)
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/api/barang/search', [BarangController::class, 'search'])->name('api.barang.search');
    Route::get('/persediaan', [PersediaanController::class, 'index'])->name('persediaan.index');
    Route::get('/arkas', [ArkasController::class, 'index'])->name('arkas.index');
    Route::get('/arkas/komponen', [ArkasController::class, 'komponen'])->name('arkas.komponen');
    Route::get('/arkas/data', [ArkasController::class, 'getData'])->name('arkas.data');

    // Kegiatan Manual (Read Only)
    Route::get('/kegiatan', [KegiatanManualController::class, 'daftarKegiatan'])->name('kegiatan.index');
    Route::get('/sumber-dana', [KegiatanManualController::class, 'indexSumberDana'])->name('sumber_dana.index');
    Route::get('/laporan/laporan-rkas', [KegiatanManualController::class, 'rekapAnggaran'])->name('laporan.index');
    Route::get('/perencanaan/dashboard', [KegiatanManualController::class, 'dashboard'])->name('perencanaan.dashboard');
    Route::get('/api/komponen-by-korek', [KegiatanManualController::class, 'getKomponenByKorek'])->name('api.komponen_by_korek');
    Route::get('/ajax/sub-programs', [KegiatanManualController::class, 'getSubPrograms'])->name('ajax.sub_programs');
});

// =========================================================================
// ZONA 3B: KEUANGAN (AKSES MANAJEMEN LAINNYA)
// Permission: 'kelola-anggaran'
// =========================================================================
Route::middleware(['permission:kelola-anggaran'])->group(function () {

    // Profil Sekolah
    Route::get('/settings', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::post('/settings', [SekolahController::class, 'store'])->name('sekolah.store');

    // RKAS & AKB Master (Import & Update)
    Route::post('/rkas/import', [RkasController::class, 'import'])->name('rkas.import');
    Route::patch('/rkas/{id}/update-idkomponen', [RkasController::class, 'updateIdKomponen'])->name('rkas.update.idkomponen');

    Route::post('/akb/import', [AkbController::class, 'import'])->name('akb.import');
    Route::get('/akb/generate', [AkbController::class, 'generate'])->name('akb.generate');
    Route::post('/akb/perbandingan/proses', [AkbController::class, 'perbandingan'])->name('akb.perbandingan.proses');

    // BKU, Penerimaan, STS, Pajak (Eksekusi)
    Route::put('/{belanja_id}/unpost', [BkuController::class, 'unpost'])->name('bku.unpost');
    Route::delete('/{id}', [BkuController::class, 'destroy'])->name('bku.destroy');

    Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
        Route::post('/store', [PenerimaanController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PenerimaanController::class, 'edit'])->name('edit');
    });
    Route::put('/{id}', [PenerimaanController::class, 'update'])->name('update');

    Route::post('/sts', [StsController::class, 'store'])->name('sts.store');
    Route::get('/sts/{id}/edit', [StsController::class, 'edit'])->name('sts.edit');
    Route::put('/sts/{id}', [StsController::class, 'update'])->name('sts.update');
    Route::delete('/sts/{id}', [StsController::class, 'destroy'])->name('sts.destroy');

    Route::delete('/pajak/{id}/hapus-setor', [PajakController::class, 'hapusSetor'])->name('pajak.hapus_setor');
    Route::post('/pajak/setor/{id}', [PajakController::class, 'prosesSetor'])->name('pajak.proses-setor');

    // NPD Manajemen
    Route::get('/npd/create', [NpdController::class, 'create'])->name('npd.create');
    Route::post('/npd/store', [NpdController::class, 'storeMassal'])->name('npd.store_massal');
    Route::post('/npd/storesurat', [NpdController::class, 'storeSurat'])->name('npd.store_surat');
    Route::delete('/npd/hapus-triwulan-aktif', [NpdController::class, 'destroyTriwulan'])->name('npd.destroy_triwulan');

    // Surat Sistem (Eksekusi Nomor & Modifikasi Jurnal)
    Route::post('/surat/generate/{belanjaId}', [SuratController::class, 'generateDefault'])->name('surat.generate');
    Route::put('/surat/update/{id}', [SuratController::class, 'update'])->name('surat.update');
    Route::post('/surat/store/{belanjaId}', [SuratController::class, 'store'])->name('surat.store');
    Route::post('/surat/store-parsial/{belanjaId}', [SuratController::class, 'storeParsial'])->name('surat.store_parsial');
    Route::delete('/surat/destroy/{id}', [SuratController::class, 'destroy'])->name('surat.destroy');
    Route::delete('/surat/foto/{id}', [SuratController::class, 'destroyFoto'])->name('surat.delete_foto');
    Route::put('/{id}/update-tw', [SuratController::class, 'updateTw'])->name('surat.update_tw');
    Route::get('/surat/regenerate-all', [SuratController::class, 'regenerateAllNumbers'])->name('surat.regenerate_all');
    Route::get('/surat/semua', [SuratController::class, 'indexSeluruhSurat'])->name('surat.index_semua');

    // Barang & Arkas Master
    Route::post('/barang/import', [BarangController::class, 'import'])->name('barang.import');
    Route::delete('/barang/truncate', [BarangController::class, 'truncate'])->name('barang.truncate');

    Route::get('/arkas/import', [ArkasController::class, 'importPage'])->name('arkas.import.page');
    Route::post('/arkas/import', [ArkasController::class, 'storeImport'])->name('arkas.import.store');
    Route::post('/arkas/toggle-status/{id}', [ArkasController::class, 'toggleStatusArkas'])->name('arkas.toggle_status');
    Route::post('/arkas/update-idkomponen/{id}', [ArkasController::class, 'updateIdKomponen'])->name('arkas.update_idkomponen');

    // Setting (Rekanan & Kegiatan)
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::post('/rekanan/{id}/toggle-status', [SuratController::class, 'toggleStatus'])->name('rekanan.toggle_status');
        Route::get('/rekanan/import', [SettingController::class, 'importRekananView'])->name('rekanan.import');
        Route::get('/rekanan/template', [SettingController::class, 'downloadTemplateRekanan'])->name('rekanan.template');
        Route::post('/rekanan/import', [SettingController::class, 'importRekananStore'])->name('rekanan.import.store');
        Route::delete('/rekanan/destroy-all', [RekananController::class, 'destroyAll'])->name('rekanan.destroy_all');
        Route::get('/rekanan/export', [RekananController::class, 'export'])->name('rekanan.export');

        Route::get('/kegiatan/import', [SettingController::class, 'importKegiatanView'])->name('kegiatan.import');
        Route::get('/kegiatan/template', [SettingController::class, 'downloadTemplateKegiatan'])->name('kegiatan.template');
        Route::post('/kegiatan/import', [SettingController::class, 'importKegiatanStore'])->name('kegiatan.import.store');

        Route::resource('rekanan', RekananController::class);
        Route::resource('kegiatan', KegiatanController::class);
    });

    // Perencanaan Manual
    Route::get('/kegiatan/import', [KegiatanManualController::class, 'createImport'])->name('kegiatan.import');
    Route::post('/kegiatan/import', [KegiatanManualController::class, 'storeImport'])->name('manual.import.kegiatan');
    Route::post('/sumber-dana', [KegiatanManualController::class, 'storeSumberDana'])->name('sumber_dana.store');

    Route::get('/kegiatan/create', [KegiatanManualController::class, 'create'])->name('kegiatan.create');
    Route::post('/kegiatan/store', [KegiatanManualController::class, 'store'])->name('kegiatan.store');
    Route::get('/kegiatan/{id}/tambah-komponen', [KegiatanManualController::class, 'tambahKomponen'])->name('kegiatan.tambah_komponen');
    Route::post('/kegiatan/{id}/tambah-komponen', [KegiatanManualController::class, 'storeKomponen'])->name('kegiatan.store_komponen');
    Route::delete('/kegiatan/{kegiatan_id}/komponen/{rkas_id}', [KegiatanManualController::class, 'destroyKomponen'])->name('kegiatan.destroy_komponen');
    Route::post('/kegiatan/{id}/cek-komponen-duplikat', [KegiatanManualController::class, 'checkKomponenDuplicate'])->name('kegiatan.cek_komponen');
    Route::put('/kegiatan/{id}/update-multi-komponen', [KegiatanManualController::class, 'updateMultiKomponen'])->name('kegiatan.update_multi_komponen');
    Route::delete('/kegiatan/{id}/komponen-multi', [KegiatanManualController::class, 'destroyMultiKomponen'])->name('kegiatan.destroy_multi_komponen');
    Route::delete('/kegiatan/{id}', [KegiatanManualController::class, 'destroy'])->name('kegiatan.destroy');
    Route::match(['get', 'post'], '/kegiatan/{id}/rekonsiliasi', [KegiatanManualController::class, 'rekonsiliasi'])->name('kegiatan.rekonsiliasi');
    Route::match(['get', 'post'], '/kegiatan/cek-json', [KegiatanManualController::class, 'cekJson'])->name('kegiatan.cek_json');

    Route::get('/komponen/', [KegiatanManualController::class, 'createImportKomponen'])->name('komponen.import');
    Route::post('/komponen/import', [KegiatanManualController::class, 'storeImportKomponen'])->name('komponen.import.store');
});

// =========================================================================
// ZONA 4: EKSTRAKURIKULER
// =========================================================================
Route::middleware(['permission:input-ekskul|kelola-anggaran'])->group(function () {

    Route::group(['prefix' => 'ekskul', 'as' => 'ekskul.'], function () {
        Route::get('/index/{belanjaId?}', [EkskulController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [EkskulController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [EkskulController::class, 'update'])->name('update');
        Route::get('/create/{belanjaId?}', [EkskulController::class, 'create'])->name('create');
        Route::get('/get-rekening', [EkskulController::class, 'getRekening'])->name('get_rekening');
        Route::get('/get-komponen', [EkskulController::class, 'getKomponen'])->name('get_komponen');
        Route::get('/get-by-pelatih', [EkskulController::class, 'getByPelatih'])->name('get_by_pelatih');
        Route::post('/store', [EkskulController::class, 'store'])->name('store');
        Route::get('/pelatih', [EkskulController::class, 'refEkskulIndex'])->name('ref.index');
        Route::post('/pelatih', [EkskulController::class, 'refEkskulStore'])->name('ref.store');
        Route::put('/pelatih/{id}', [EkskulController::class, 'refEkskulUpdate'])->name('ref.update');
        Route::delete('/pelatih/{id}', [EkskulController::class, 'refEkskulDestroy'])->name('ref.destroy');
        Route::get('/cetak/{id}', [EkskulController::class, 'cetak'])->name('cetak');
        Route::get('/cetak-absensi/{id}', [EkskulController::class, 'cetakAbsensi'])->name('cetak_absensi');
        Route::delete('/{id}', [EkskulController::class, 'destroy'])->name('destroy');
        Route::get('/manage-details/{belanjaId}', [EkskulController::class, 'manageDetails'])->name('manage_details');
        Route::post('/store-detail', [EkskulController::class, 'storeDetail'])->name('store_detail');
        Route::delete('/delete-detail/{id}', [EkskulController::class, 'deleteDetail'])->name('delete_detail');
        Route::put('/detail/{id}', [EkskulController::class, 'updateDetail'])->name('update_detail');
        Route::get('/bulk-create/{id}', [EkskulController::class, 'create_bulk'])->name('create_bulk');
        Route::post('/bulk-store', [EkskulController::class, 'store_detail_bulk'])->name('store_detail_bulk');
    });

    Route::get('/ekskul-laporan', [EkskulLaporanController::class, 'index'])->name('ekskul.laporan.index');
    Route::post('/ekskul-laporan', [EkskulLaporanController::class, 'store'])->name('ekskul.laporan.store');
    Route::delete('/ekskul-laporan/{id}', [EkskulLaporanController::class, 'destroy'])->name('ekskul.laporan.destroy');
});

// =========================================================================
// ZONA 5: SUPER ADMIN
// =========================================================================
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Sekolah & Users
    Route::resource('sekolah', AdminSekolahController::class);
    Route::resource('users', AdminUserController::class);
    Route::patch('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/roles', [AdminUserController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{role}/permissions', [AdminUserController::class, 'updateRolePermissions'])->name('roles.update_permissions');
    Route::delete('/roles/{role}', [AdminUserController::class, 'destroyRole'])->name('roles.destroy');
    Route::post('/permissions', [AdminUserController::class, 'storePermission'])->name('permissions.store');
    Route::delete('/permissions/{permission}', [AdminUserController::class, 'destroyPermission'])->name('permissions.destroy');

    // Anggaran & Korek
    Route::get('/anggaran', [AnggaranController::class, 'index'])->name('anggaran.index');
    Route::post('/anggaran/generate', [AnggaranController::class, 'generate'])->name('anggaran.generate');

    Route::resource('korek', KorekController::class)->except(['show']);
    Route::post('korek/import-update', [KorekController::class, 'importKorekUpdate'])->name('korek.import_update');
    Route::patch('korek/{korek}/update-jenis-belanja', [KorekController::class, 'updateJenisBelanjaAjax'])->name('korek.update_jenis_belanja');
    Route::post('korek/bulk-update-jenis', [KorekController::class, 'bulkUpdateJenisBelanja'])->name('korek.bulk_update_jenis');

    // Cleanup
    Route::get('/rkas/cleanup', [RkasCleanupController::class, 'index'])->name('rkas.cleanup');
    Route::get('/api/anggaran-by-sekolah/{sekolahId}', [RkasCleanupController::class, 'getAnggaranBySekolah']);
    Route::delete('/rkas/cleanup/destroy', [RkasCleanupController::class, 'destroy'])->name('rkas.cleanup.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('setting')->group(function () {
    Route::get('/import-kegiatan', [SettingController::class, 'importKegiatanJson'])->name('setting.kegiatan.importjson');
    Route::post('/import-kegiatan', [SettingController::class, 'storeImportKegiatanJson'])->name('setting.kegiatan.store_import');
});

Route::get('/rkas-realisasi', [ApiJsonController::class, 'getRkasRealisasi'])->name('api.rkas.realisasi');
require __DIR__.'/auth.php';
