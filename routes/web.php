<?php

use App\Http\Controllers\Admin\AnggaranController;
use App\Http\Controllers\Admin\KorekController;
use App\Http\Controllers\Admin\RkasCleanupController;
use App\Http\Controllers\Admin\SekolahController as AdminSekolahController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AkbController;
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
// 1. ZONA PUBLIK (Tanpa Login)
// =========================================================================
Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// 2. ZONA PENGGUNA TERAUTENTIKASI (Semua User yang Login)
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Akses global untuk semua yang login
    Route::post('/anggaran/switch', [DashboardController::class, 'switch'])->name('anggaran.switch');
    Route::get('/cetak-cover', [CetakController::class, 'cetakCover'])->name('cetak.cover');

    // =========================================================================
    // 3A. ZONA KEUANGAN (AKSES BACA / VIEW)
    // Permission: 'view-anggaran' ATAU 'kelola-anggaran'
    // Hanya berisi Route::get (Melihat, Rekap, Cetak, Export, API Pencarian)
    // =========================================================================
    // Menggunakan middleware 'permission' bawaan Spatie dengan tanda pipa (|) yang berarti ATAU
    Route::middleware(['permission:view-anggaran|kelola-anggaran'])->group(function () {

        // --- RKAS ---
        Route::prefix('rkas')->name('rkas.')->group(function () {
            Route::get('/', [RkasController::class, 'index'])->name('index');
            Route::get('/anggaran', [RkasController::class, 'anggaran'])->name('anggaran');
            Route::get('/rekap', [RkasController::class, 'rekap'])->name('rekap');
            Route::get('/rincian', [RkasController::class, 'rincian'])->name('rincian');
            Route::get('/cetak-laporan', [RkasController::class, 'cetakLaporan'])->name('cetak_laporan');
        });

        // --- AKB ---
        Route::prefix('akb')->name('akb.')->group(function () {
            Route::get('/', [AkbController::class, 'index'])->name('index');
            Route::get('/rincian', [AkbController::class, 'rincian'])->name('rincian');
            Route::get('/rincianakb', [AkbController::class, 'indexRincian'])->name('indexrincian');
            Route::get('/export-excel', [AkbController::class, 'exportExcel'])->name('export_excel');
            Route::get('/satuan', [AkbController::class, 'satuan'])->name('satuan');
            Route::get('/ringkas', [AkbController::class, 'ringkas'])->name('ringkas');
            Route::get('/perbandingan', [AkbController::class, 'indexPerbandingan'])->name('perbandingan.index');
        });
        Route::get('/api/rkas/{anggaranId}/data', [AkbController::class, 'getData'])->name('api.rkas.data');

        // --- BELANJA (Hanya Index & Show) ---
        Route::resource('belanja', BelanjaController::class)->only(['index', 'show']);
        Route::get('/belanja/{id}/json', [BelanjaController::class, 'getJson'])->name('belanja.json');
        Route::prefix('api')->group(function () {
            Route::get('/get-rekening', [BelanjaController::class, 'getRekening'])->name('api.rekening');
            Route::get('/get-komponen', [BelanjaController::class, 'getKomponen'])->name('api.komponen');
            Route::get('/get-keterangan', [BelanjaController::class, 'getKeterangan'])->name('get_keterangan');
        });

        // --- BKU & STS (Hanya Lihat) ---
        Route::get('/bku', [BkuController::class, 'index'])->name('bku.index');
        Route::resource('sts', StsController::class)->only(['index', 'show']);

        // --- PAJAK (Hanya Lihat & Rekap) ---
        Route::get('/pajak/siap-setor', [PajakController::class, 'siapSetor'])->name('pajak.siap-setor');
        Route::get('/pajak/rekap', [PajakController::class, 'rekap'])->name('pajak.rekap');

        // --- REALISASI & REKAP ---
        Route::prefix('realisasi')->name('realisasi.')->group(function () {
            Route::get('/komponen', [RealisasiController::class, 'komponen'])->name('komponen');
            Route::get('/korek', [RealisasiController::class, 'korek'])->name('korek');
            Route::get('/jenis-belanja', [RealisasiController::class, 'jenisBelanja'])->name('jenis-belanja');
            Route::get('/komponen/export', [RealisasiController::class, 'exportKomponen'])->name('komponen.export');
            Route::get('/rekanan', [RealisasiController::class, 'rekapPerRekanan'])->name('rekanan');
        });
        Route::get('/rekap/export', [RealisasiController::class, 'exportExcel'])->name('belanja.export');
        Route::get('/rekap-rekanan/export-semua', [RealisasiController::class, 'exportSemuaRekanan'])->name('rekap-rekanan.export-semua');
        Route::get('/rekap/rekanan/export/{id}', [RealisasiController::class, 'exportDetailRekanan'])->name('rekap.rekanan.export_detail');

        // --- NPD (Hanya Lihat & Export) ---
        Route::get('/npd', [NpdController::class, 'index'])->name('npd.index');
        Route::get('/npd/export', [NpdController::class, 'exportExcel'])->name('npd.export');
        Route::get('/npd/{id}', [NpdController::class, 'show'])->name('npd.show');
        Route::get('/api/npd/cek-saldo', [NpdController::class, 'getSaldoAnggaran'])->name('api.npd.saldo');

        // --- SURAT & DOKUMEN CETAK ---
        Route::prefix('surat')->name('surat.')->group(function () {
            Route::get('/manage/{belanjaId}', [SuratController::class, 'index'])->name('index');
            Route::get('/cetak-sp/{id}', [SuratController::class, 'cetakSpParsial'])->name('cetak_sp');
            Route::get('/cetak-bapb/{id}', [SuratController::class, 'cetakBapbParsial'])->name('cetak_bapb');
            Route::get('/cetak-satuan/{id}/{jenis}', [SuratController::class, 'cetakSatuan'])->name('cetak_satuan');
            Route::get('/cetakpdf/{id}', [SuratController::class, 'cetakPdf'])->name('cetakpdf');
            Route::get('/cetaksatuanpdf/{id}/{jenis}', [SuratController::class, 'cetakSatuanPdf'])->name('cetakSatuanPdf');
            Route::get('/cetakparsialpdf/{id}', [SuratController::class, 'cetakParsialPdf'])->name('cetakParsialPdf');
            Route::get('/download-semua-parsial/{belanjaId}', [SuratController::class, 'downloadSemuaParsial'])->name('download_semua_parsial');
            Route::get('/rekap-surat', [SuratController::class, 'rekapKeseluruhanTriwulanPdf'])->name('rekap_triwulan');
            Route::get('/daftar', [SuratController::class, 'daftarSurat'])->name('daftar');
            Route::get('/talangan-pdf/{talanganId}', [SuratController::class, 'cetakTalanganPdf'])->name('talangan_pdf');
            Route::get('/talangan-npd', [SuratController::class, 'daftarTalanganNpd'])->name('daftar_talangan_npd');
        });
        Route::get('/belanja/cetak-foto/{id}', [SuratController::class, 'cetakFotoSpj'])->name('belanja.cetak_foto');
        Route::get('/belanja/export-excel', [SuratController::class, 'exportExcel'])->name('belanja.export_excel');
        Route::get('/belanja/{id}/download-bundel', [SuratController::class, 'downloadBundel'])->name('belanja.downloadBundel');
        Route::get('/cetak/kop', [SuratController::class, 'cetakKopPdf'])->name('cetak.kop');

        // --- BARANG, PERSEDIAAN & ARKAS (Hanya Lihat) ---
        Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/api/barang/search', [BarangController::class, 'search'])->name('api.barang.search');
        Route::get('/persediaan', [PersediaanController::class, 'index'])->name('persediaan.index');

        Route::get('/arkas', [ArkasController::class, 'index'])->name('arkas.index');
        Route::get('/arkas/komponen', [ArkasController::class, 'komponen'])->name('arkas.komponen');
        Route::get('/arkas/data', [ArkasController::class, 'getData'])->name('arkas.data');

        // --- KEGIATAN MANUAL (Hanya Lihat) ---
        Route::get('/kegiatan', [KegiatanManualController::class, 'daftarKegiatan'])->name('kegiatan.index');
        Route::get('/sumber-dana', [KegiatanManualController::class, 'indexSumberDana'])->name('sumber_dana.index');
        Route::get('/laporan/laporan-rkas', [KegiatanManualController::class, 'rekapAnggaran'])->name('laporan.index');
        Route::get('/perencanaan/dashboard', [KegiatanManualController::class, 'dashboard'])->name('perencanaan.dashboard');
        Route::get('/api/komponen-by-korek', [KegiatanManualController::class, 'getKomponenByKorek'])->name('api.komponen_by_korek');
        Route::get('/ajax/sub-programs', [KegiatanManualController::class, 'getSubPrograms'])->name('ajax.sub_programs');
    });

    // =========================================================================
    // 3B. ZONA KEUANGAN (AKSES KELOLA / MANAJEMEN)
    // Permission: 'kelola-anggaran'
    // Berisi Route untuk Tambah (POST), Ubah (PUT/PATCH), Hapus (DELETE), & Import
    // =========================================================================
    Route::middleware(['can:kelola-anggaran'])->group(function () {

        // --- PROFIL SEKOLAH / SETTINGS ---
        Route::get('/settings', [SekolahController::class, 'index'])->name('sekolah.index');
        Route::post('/settings', [SekolahController::class, 'store'])->name('sekolah.store');

        // --- RKAS ---
        Route::post('/rkas/import', [RkasController::class, 'import'])->name('rkas.import');
        Route::patch('/rkas/{id}/update-idkomponen', [RkasController::class, 'updateIdKomponen'])->name('rkas.update.idkomponen');

        // --- AKB ---
        Route::post('/akb/import', [AkbController::class, 'import'])->name('akb.import');
        Route::get('/akb/generate', [AkbController::class, 'generate'])->name('akb.generate');
        Route::post('/akb/perbandingan/proses', [AkbController::class, 'perbandingan'])->name('akb.perbandingan.proses');

        // --- BELANJA ---
        Route::resource('belanja', BelanjaController::class)->except(['index', 'show']);
        Route::post('/{id}/post', [BelanjaController::class, 'post'])->name('belanja.post');
        Route::post('/belanja/{id}/duplicate', [BelanjaController::class, 'duplicate'])->name('belanja.duplicate');
        Route::get('/{id}/edit-penawaran', [BelanjaController::class, 'editPenawaran'])->name('belanja.edit_penawaran');
        Route::put('/{id}/update-penawaran', [BelanjaController::class, 'updatePenawaran'])->name('belanja.update_penawaran');

        // --- BKU ---
        Route::put('/{belanja_id}/unpost', [BkuController::class, 'unpost'])->name('bku.unpost');
        Route::delete('/bku/{id}', [BkuController::class, 'destroy'])->name('bku.destroy');

        // --- PENERIMAAN & STS ---
        Route::post('/penerimaan/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
        Route::get('/penerimaan/{id}/edit', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
        Route::put('/penerimaan/{id}', [PenerimaanController::class, 'update'])->name('penerimaan.update');
        Route::resource('sts', StsController::class)->except(['index', 'show']);

        // --- PAJAK ---
        Route::delete('/pajak/{id}/hapus-setor', [PajakController::class, 'hapusSetor'])->name('pajak.hapus_setor');
        Route::post('/pajak/setor/{id}', [PajakController::class, 'prosesSetor'])->name('pajak.proses-setor');

        // --- NPD ---
        Route::get('/npd/create', [NpdController::class, 'create'])->name('npd.create');
        Route::post('/npd/store', [NpdController::class, 'storeMassal'])->name('npd.store_massal');
        Route::post('/npd/storesurat', [NpdController::class, 'storeSurat'])->name('npd.store_surat');
        Route::delete('/npd/hapus-triwulan-aktif', [NpdController::class, 'destroyTriwulan'])->name('npd.destroy_triwulan');

        // --- SURAT & TALANGAN ---
        Route::post('/surat/generate/{belanjaId}', [SuratController::class, 'generateDefault'])->name('surat.generate');
        Route::put('/surat/update/{id}', [SuratController::class, 'update'])->name('surat.update');
        Route::post('/surat/store/{belanjaId}', [SuratController::class, 'store'])->name('surat.store');
        Route::post('/surat/store-parsial/{belanjaId}', [SuratController::class, 'storeParsial'])->name('surat.store_parsial');
        Route::delete('/surat/destroy/{id}', [SuratController::class, 'destroy'])->name('surat.destroy');
        Route::delete('/surat/foto/{id}', [SuratController::class, 'destroyFoto'])->name('surat.delete_foto');
        Route::put('/surat/{id}/update-tw', [SuratController::class, 'updateTw'])->name('surat.update_tw');
        Route::get('/surat/regenerate-all', [SuratController::class, 'regenerateAllNumbers'])->name('surat.regenerate_all');

        Route::get('/surat/talangan', [SuratController::class, 'createTalangan'])->name('talangan.create');
        Route::post('/surat/talangan/store', [SuratController::class, 'storeTalangan'])->name('talangan.store');
        Route::delete('/surat/talangan/{surat_id}', [SuratController::class, 'destroyTalangan'])->name('talangan.destroy');
        Route::delete('/surat/talangan-npd/{id}', [SuratController::class, 'hapusSurat'])->name('hapus_talangan_npd');

        Route::get('/surat/cover-lpj', [SuratController::class, 'createCoverLpj'])->name('cover_lpj.create');
        Route::post('/surat/cover-lpj/cetak', [SuratController::class, 'generateCoverPdf'])->name('cover_lpj.generate');
        Route::post('/surat/{id}/upload-foto', [SuratController::class, 'uploadFoto'])->name('belanja.upload_foto');

        // --- BARANG ---
        Route::post('/barang/import', [BarangController::class, 'import'])->name('barang.import');
        Route::delete('/barang/truncate', [BarangController::class, 'truncate'])->name('barang.truncate');

        // --- ARKAS ---
        Route::get('/arkas/import', [ArkasController::class, 'importPage'])->name('arkas.import.page'); // Form Halaman Import
        Route::post('/arkas/import', [ArkasController::class, 'storeImport'])->name('arkas.import.store');
        Route::post('/arkas/toggle-status/{id}', [ArkasController::class, 'toggleStatusArkas'])->name('arkas.toggle_status');
        Route::post('/arkas/update-idkomponen/{id}', [ArkasController::class, 'updateIdKomponen'])->name('arkas.update_idkomponen');

        // --- SETTING (REKANAN, KEGIATAN, SUMBER DANA) ---
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::resource('rekanan', RekananController::class);
            Route::get('/rekanan/import', [SettingController::class, 'importRekananView'])->name('rekanan.import');
            Route::get('/rekanan/template', [SettingController::class, 'downloadTemplateRekanan'])->name('rekanan.template');
            Route::post('/rekanan/import', [SettingController::class, 'importRekananStore'])->name('rekanan.import.store');
            Route::delete('/rekanan/destroy-all', [RekananController::class, 'destroyAll'])->name('rekanan.destroy_all');
            Route::get('/rekanan/export', [RekananController::class, 'export'])->name('rekanan.export');
            Route::post('/rekanan/{id}/toggle-status', [SuratController::class, 'toggleStatus'])->name('rekanan.toggle_status');

            Route::resource('kegiatan', KegiatanController::class);
            Route::get('/kegiatan/import', [SettingController::class, 'importKegiatanView'])->name('kegiatan.import');
            Route::get('/kegiatan/template', [SettingController::class, 'downloadTemplateKegiatan'])->name('kegiatan.template');
            Route::post('/kegiatan/import', [SettingController::class, 'importKegiatanStore'])->name('kegiatan.import.store');
        });

        // --- PERENCANAAN / KEGIATAN MANUAL ---
        Route::get('/kegiatan-manual/import', [KegiatanManualController::class, 'createImport'])->name('kegiatan.import_manual');
        Route::post('/kegiatan-manual/import', [KegiatanManualController::class, 'storeImport'])->name('manual.import.kegiatan');
        Route::post('/sumber-dana', [KegiatanManualController::class, 'storeSumberDana'])->name('sumber_dana.store');

        Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
            Route::get('/create', [KegiatanManualController::class, 'create'])->name('create');
            Route::post('/store', [KegiatanManualController::class, 'store'])->name('store');
            Route::get('/{id}/tambah-komponen', [KegiatanManualController::class, 'tambahKomponen'])->name('tambah_komponen');
            Route::post('/{id}/tambah-komponen', [KegiatanManualController::class, 'storeKomponen'])->name('store_komponen');
            Route::delete('/{kegiatan_id}/komponen/{rkas_id}', [KegiatanManualController::class, 'destroyKomponen'])->name('destroy_komponen');
            Route::post('/{id}/cek-komponen-duplikat', [KegiatanManualController::class, 'checkKomponenDuplicate'])->name('cek_komponen');
            Route::put('/{id}/update-multi-komponen', [KegiatanManualController::class, 'updateMultiKomponen'])->name('update_multi_komponen');
            Route::delete('/{id}/komponen-multi', [KegiatanManualController::class, 'destroyMultiKomponen'])->name('destroy_multi_komponen');
            Route::delete('/{id}', [KegiatanManualController::class, 'destroy'])->name('destroy');
            Route::match(['get', 'post'], '/{id}/rekonsiliasi', [KegiatanManualController::class, 'rekonsiliasi'])->name('rekonsiliasi');
            Route::match(['get', 'post'], '/cek-json', [KegiatanManualController::class, 'cekJson'])->name('cek_json');
        });

        Route::get('/komponen/import', [KegiatanManualController::class, 'createImportKomponen'])->name('komponen.import');
        Route::post('/komponen/import', [KegiatanManualController::class, 'storeImportKomponen'])->name('komponen.import.store');
    });

    // =========================================================================
    // 4. ZONA EKSTRAKURIKULER (Hanya untuk Permission 'input-ekskul')
    // =========================================================================
    Route::middleware(['can:input-ekskul'])->group(function () {

        Route::prefix('ekskul')->name('ekskul.')->group(function () {
            Route::get('/index/{belanjaId?}', [EkskulController::class, 'index'])->name('index');
            Route::get('/create/{belanjaId?}', [EkskulController::class, 'create'])->name('create');
            Route::post('/store', [EkskulController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [EkskulController::class, 'edit'])->name('edit');
            Route::put('/update/{id}', [EkskulController::class, 'update'])->name('update');
            Route::delete('/{id}', [EkskulController::class, 'destroy'])->name('destroy');

            Route::get('/get-rekening', [EkskulController::class, 'getRekening'])->name('get_rekening');
            Route::get('/get-komponen', [EkskulController::class, 'getKomponen'])->name('get_komponen');
            Route::get('/get-by-pelatih', [EkskulController::class, 'getByPelatih'])->name('get_by_pelatih');

            Route::get('/pelatih', [EkskulController::class, 'refEkskulIndex'])->name('ref.index');
            Route::post('/pelatih', [EkskulController::class, 'refEkskulStore'])->name('ref.store');
            Route::put('/pelatih/{id}', [EkskulController::class, 'refEkskulUpdate'])->name('ref.update');
            Route::delete('/pelatih/{id}', [EkskulController::class, 'refEkskulDestroy'])->name('ref.destroy');

            Route::get('/cetak/{id}', [EkskulController::class, 'cetak'])->name('cetak');
            Route::get('/cetak-absensi/{id}', [EkskulController::class, 'cetakAbsensi'])->name('cetak_absensi');

            Route::get('/manage-details/{belanjaId}', [EkskulController::class, 'manageDetails'])->name('manage_details');
            Route::post('/store-detail', [EkskulController::class, 'storeDetail'])->name('store_detail');
            Route::delete('/delete-detail/{id}', [EkskulController::class, 'deleteDetail'])->name('delete_detail');
            Route::put('/detail/{id}', [EkskulController::class, 'updateDetail'])->name('update_detail');
            Route::get('/bulk-create/{id}', [EkskulController::class, 'create_bulk'])->name('create_bulk');
            Route::post('/bulk-store', [EkskulController::class, 'store_detail_bulk'])->name('store_detail_bulk');
        });

        Route::prefix('ekskul-laporan')->name('ekskul.laporan.')->group(function () {
            Route::get('/', [EkskulLaporanController::class, 'index'])->name('index');
            Route::post('/', [EkskulLaporanController::class, 'store'])->name('store');
            Route::delete('/{id}', [EkskulLaporanController::class, 'destroy'])->name('destroy');
        });

    });

    // =========================================================================
    // 5. ZONA SUPER ADMIN (Hanya untuk Role 'admin')
    // =========================================================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

        // Manajemen Instansi Sekolah & User
        Route::resource('sekolah', AdminSekolahController::class);
        Route::resource('users', AdminUserController::class);
        Route::patch('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Manajemen Akses (Roles & Permissions)
        Route::post('/roles', [AdminUserController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}/permissions', [AdminUserController::class, 'updateRolePermissions'])->name('roles.update_permissions');
        Route::delete('/roles/{role}', [AdminUserController::class, 'destroyRole'])->name('roles.destroy');
        Route::post('/permissions', [AdminUserController::class, 'storePermission'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [AdminUserController::class, 'destroyPermission'])->name('permissions.destroy');

        // Manajemen Master Anggaran
        Route::get('/anggaran', [AnggaranController::class, 'index'])->name('anggaran.index');
        Route::post('/anggaran/generate', [AnggaranController::class, 'generate'])->name('anggaran.generate');

        // Manajemen Master Kode Rekening
        Route::resource('korek', KorekController::class)->except(['show']);
        Route::post('korek/import-update', [KorekController::class, 'importKorekUpdate'])->name('korek.import_update');
        Route::patch('korek/{korek}/update-jenis-belanja', [KorekController::class, 'updateJenisBelanjaAjax'])->name('korek.update_jenis_belanja');
        Route::post('korek/bulk-update-jenis', [KorekController::class, 'bulkUpdateJenisBelanja'])->name('korek.bulk_update_jenis');

        // Pembersihan RKAS Global
        Route::get('/rkas/cleanup', [RkasCleanupController::class, 'index'])->name('rkas.cleanup');
        Route::get('/api/anggaran-by-sekolah/{sekolahId}', [RkasCleanupController::class, 'getAnggaranBySekolah']);
        Route::delete('/rkas/cleanup/destroy', [RkasCleanupController::class, 'destroy'])->name('rkas.cleanup.destroy');

        // Import JSON Kegiatan Khusus Admin
        Route::get('/setting/import-kegiatan', [SettingController::class, 'importKegiatanJson'])->name('setting.kegiatan.importjson');
        Route::post('/setting/import-kegiatan', [SettingController::class, 'storeImportKegiatanJson'])->name('setting.kegiatan.store_import');

    }); // <-- Akhir Group Admin

}); // <-- Akhir ZONA PENGGUNA TERAUTENTIKASI

require __DIR__.'/auth.php';
