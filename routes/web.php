<?php

use App\Http\Controllers\Admin\RkasCleanupController;
use App\Http\Controllers\Admin\SekolahController as AdminSekolahController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AkbController;
use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\Coba;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\RekananController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/anggaran/switch', [DashboardController::class, 'switch'])->name('anggaran.switch');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rkas', [RkasController::class, 'index'])->name('rkas.index');
    Route::get('/rkas/anggaran', [RkasController::class, 'anggaran'])->name('rkas.anggaran');
    Route::post('/rkas/import', [RkasController::class, 'import'])->name('rkas.import');
    Route::get('/rkas/rincian', [RkasController::class, 'rincian'])->name('rkas.rincian');

});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/akb', [AkbController::class, 'index'])->name('akb.index');
    Route::post('/akb/import', [AkbController::class, 'import'])->name('akb.import');
    Route::get('/akb/rincian', [AkbController::class, 'rincian'])->name('akb.rincian');
    Route::get('/akb/generate', [AkbController::class, 'generate'])->name('akb.generate');
    Route::get('/akb/rincianakb', [AkbController::class, 'indexRincian'])->name('akb.indexrincian');
    Route::get('/akb/export-excel', [AkbController::class, 'exportExcel'])->name('akb.export_excel');
    Route::get('/akb/satuan', [AkbController::class, 'satuan'])->name('akb.satuan');

});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [SekolahController::class, 'index'])->name('sekolah.index');
    Route::post('/settings', [SekolahController::class, 'store'])->name('sekolah.store');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Otomatis mencakup: index, create, store, show, edit, update, destroy
    Route::resource('belanja', BelanjaController::class);

    Route::post('/{id}/post', [BelanjaController::class, 'post'])->name('belanja.post');
    // Rute API tambahan untuk pencarian data
    Route::prefix('api')->group(function () {
        Route::get('/get-rekening', [BelanjaController::class, 'getRekening'])->name('api.rekening');
        Route::get('/get-komponen', [BelanjaController::class, 'getKomponen'])->name('api.komponen');
    });

});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/bku', [BkuController::class, 'index'])->name('bku.index');
    Route::put('/{belanja_id}/unpost', [BkuController::class, 'unpost'])->name('bku.unpost');
    Route::delete('/{id}', [BkuController::class, 'destroy'])->name('bku.destroy');
});
Route::middleware(['auth', 'verified'])->group(function () {

    // Kelompokkan agar URL lebih spesifik dan tidak bentrok
    Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
        Route::post('/store', [PenerimaanController::class, 'store'])->name('store');

        // Perbaikan URL: sekarang menjadi /penerimaan/{id}/edit
        Route::get('/{id}/edit', [PenerimaanController::class, 'edit'])->name('edit');

        // Perbaikan URL: sekarang menjadi /penerimaan/{id}
        Route::put('/{id}', [PenerimaanController::class, 'update'])->name('update');
    });
});
Route::middleware(['auth', 'verified'])->group(function () {
    // Route Pajak
    Route::get('/pajak/siap-setor', [PajakController::class, 'siapSetor'])->name('pajak.siap-setor');
    Route::post('/pajak/setor/{id}', [PajakController::class, 'prosesSetor'])->name('pajak.proses-setor');
    Route::get('/realisasi/komponen', [RealisasiController::class, 'komponen'])->name('realisasi.komponen');
    Route::get('/realisasi/korek', [RealisasiController::class, 'korek'])->name('realisasi.korek');
    Route::get('/belanja/cetak/{id}', [SuratController::class, 'cetakDokumenLengkap'])->name('belanja.print');
    Route::get('/rekap/export', [SuratController::class, 'exportExcel'])->name('belanja.export');
});
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // 1. Route Resource untuk Sekolah
    // Ini otomatis membuat route: index, create, store, edit, update, destroy
    // URL: /admin/sekolah
    // Name: admin.sekolah.index, admin.sekolah.store, dst.
    Route::resource('sekolah', AdminSekolahController::class);

    // 2. Route Resource untuk Users (Manajemen Pengguna)
    // URL: /admin/users
    // Name: admin.users.index, admin.users.create, dst.
    Route::resource('users', AdminUserController::class);

    // (Opsional) Custom Route untuk User (misal: Reset Password manual)
    Route::patch('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
        ->name('users.reset-password');

});
// Tambahkan 'admin/' pada prefix dan 'admin.' pada name
Route::middleware(['auth'])->prefix('setting')->name('setting.')->group(function () {
    Route::post('/rekanan/{id}/toggle-status', [SuratController::class, 'toggleStatus'])
        ->name('rekanan.toggle_status');
    // --- REKANAN ---
    Route::get('/rekanan/import', [SettingController::class, 'importRekananView'])->name('rekanan.import');
    Route::get('/rekanan/template', [SettingController::class, 'downloadTemplateRekanan'])->name('rekanan.template');
    Route::post('/rekanan/import', [SettingController::class, 'importRekananStore'])->name('rekanan.import.store');
    Route::delete('/rekanan/destroy-all', [RekananController::class, 'destroyAll'])
        ->name('rekanan.destroy_all');
    // --- KEGIATAN ---
    // 1. Menampilkan Halaman Form Upload
    Route::get('/kegiatan/import', [SettingController::class, 'importKegiatanView'])
        ->name('kegiatan.import');

    // 2. Download Template Excel (.xlsx)
    // Sekarang route ini akan bernama: 'admin.settings.kegiatan.template' (SESUAI)
    Route::get('/kegiatan/template', [SettingController::class, 'downloadTemplateKegiatan'])
        ->name('kegiatan.template');

    // 3. Proses Eksekusi Import (POST)
    Route::post('/kegiatan/import', [SettingController::class, 'importKegiatanStore'])
        ->name('kegiatan.import.store');

    Route::resource('rekanan', RekananController::class);
    Route::resource('kegiatan', KegiatanController::class);
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:admin']], function () {

    // Halaman Menu Penghapusan
    Route::get('/rkas/cleanup', [RkasCleanupController::class, 'index'])->name('rkas.cleanup');

    // API untuk mengambil anggaran berdasarkan sekolah (AJAX)
    Route::get('/api/anggaran-by-sekolah/{sekolahId}', [RkasCleanupController::class, 'getAnggaranBySekolah']);

    // Action Hapus
    Route::delete('/rkas/cleanup/destroy', [RkasCleanupController::class, 'destroy'])->name('rkas.cleanup.destroy');
});

Route::middleware(['auth'])->prefix('surat')->group(function () {
    Route::get('/manage/{belanjaId}', [SuratController::class, 'index'])->name('surat.index');
    Route::post('/generate/{belanjaId}', [SuratController::class, 'generateDefault'])->name('surat.generate');
    Route::put('/update/{id}', [SuratController::class, 'update'])->name('surat.update');
    Route::post('/store/{belanjaId}', [SuratController::class, 'store'])->name('surat.store');
    Route::post('/store-parsial/{belanjaId}', [SuratController::class, 'storeParsial'])->name('surat.store_parsial');
    Route::get('/cetak-sp/{id}', [SuratController::class, 'cetakSpParsial'])->name('surat.cetak_sp');
    Route::get('/cetak-bapb/{id}', [SuratController::class, 'cetakBapbParsial'])->name('surat.cetak_bapb');
    Route::get('/{id}/edit-penawaran', [BelanjaController::class, 'editPenawaran'])->name('belanja.edit_penawaran');
    Route::delete('/destroy/{id}', [SuratController::class, 'destroy'])->name('surat.destroy');
    Route::post('/{id}/upload-foto', [SuratController::class, 'uploadFoto'])
        ->name('belanja.upload_foto');
    Route::get('/belanja/cetak-foto/{id}', [SuratController::class, 'cetakFotoSpj'])->name('belanja.cetak_foto');
    Route::get('/belanja/export-excel', [SuratController::class, 'exportExcel'])->name('belanja.export_excel');
    Route::get('/regenerate-all', [SuratController::class, 'regenerateAllNumbers'])
        ->name('surat.regenerate_all');

    // Route untuk Hapus Foto (Karena tadi kita tambahkan tombol hapus)
    Route::delete('/foto/{id}', [SuratController::class, 'destroyFoto'])
        ->name('surat.delete_foto');
    // Proses Simpan
    Route::put('/{id}/update-penawaran', [BelanjaController::class, 'updatePenawaran'])->name('belanja.update_penawaran');
    Route::get('/cetak-bundel/{id}', [SuratController::class, 'cetakBundel'])->name('surat.cetak_bundel');
    Route::get('/belanja/{id}/download-bundel', [SuratController::class, 'downloadBundel'])
        ->name('belanja.downloadBundel');
    // Cetak Satuan (ID Belanja + Jenis Surat)
    Route::get('/cetak-satuan/{id}/{jenis}', [SuratController::class, 'cetakSatuan'])->name('surat.cetak_satuan');
    Route::get('/cetakpdf/{id}', [SuratController::class, 'cetakPdf'])->name('surat.cetakpdf');
    Route::get('/cetaksatuanpdf/{id}/{jenis}', [SuratController::class, 'cetakSatuanPdf'])->name('surat.cetakSatuanPdf');
    Route::get('/cetakparsialpdf/{id}', [SuratController::class, 'cetakParsialPdf'])->name('surat.cetakParsialPdf');
});

Route::group(['middleware' => ['auth']], function () {

    Route::group(['prefix' => 'ekskul', 'as' => 'ekskul.'], function () {

        // 1. Halaman Index (Daftar SPJ)
        // {belanjaId?} tanda tanya artinya parameter ini OPSIONAL.
        // - Akses /ekskul/index          -> Menampilkan semua data
        // - Akses /ekskul/index/5        -> Menampilkan data milik belanja ID 5 saja
        Route::get('/index/', [EkskulController::class, 'index'])->name('index');

        Route::get('/edit/{id}', [EkskulController::class, 'edit'])->name('edit');

        // Proses Update Data ke Database
        Route::put('/update/{id}', [EkskulController::class, 'update'])->name('update');

        // 2. Halaman Create (Form Input)
        // Parameter {belanjaId} WAJIB ada, karena kita butuh ID belanja untuk dikirim ke form
        Route::get('/create/', [EkskulController::class, 'create'])->name('create');
        // API AJAX (Penyebab Error Anda)
        Route::get('/get-rekening', [EkskulController::class, 'getRekening'])->name('get_rekening');
        Route::get('/get-komponen', [EkskulController::class, 'getKomponen'])->name('get_komponen');
        Route::get('/get-by-pelatih', [EkskulController::class, 'getByPelatih'])->name('get_by_pelatih');
        // 3. Proses Simpan (Store)
        Route::post('/store', [EkskulController::class, 'store'])->name('store');
        Route::get('/referensi/ekskul', [EkskulController::class, 'refEkskulIndex'])->name('ref.index');
        Route::post('/referensi/ekskul', [EkskulController::class, 'refEkskulStore'])->name('ref.store');
        Route::put('/referensi/ekskul/{id}', [EkskulController::class, 'refEkskulUpdate'])->name('ref.update');
        Route::delete('/referensi/ekskul/{id}', [EkskulController::class, 'refEkskulDestroy'])->name('ref.destroy');

        // 4. Cetak Kwitansi & Lampiran
        Route::get('/cetak/{id}', [EkskulController::class, 'cetak'])->name('cetak');
        Route::get('/cetak-absensi/{id}', [EkskulController::class, 'cetakAbsensi'])->name('cetak_absensi');
        // 5. Hapus Data
        Route::delete('/{id}', [EkskulController::class, 'destroy'])->name('destroy');
        Route::get('/manage-details/{belanjaId}', [EkskulController::class, 'manageDetails'])->name('manage_details');
        Route::post('/store-detail', [EkskulController::class, 'storeDetail'])->name('store_detail');
        Route::delete('/delete-detail/{id}', [EkskulController::class, 'deleteDetail'])->name('delete_detail');

    });

});

Route::get('/coba', [Coba::class, 'index'])->name('index');
Route::get('/banding', [Coba::class, 'banding'])->name('banding');
Route::get('/coba/rkas', [Coba::class, 'rkas'])->name('coba.rkas');
Route::get('/coba/anggaran', [Coba::class, 'anggaran'])->name('coba.anggaran');
require __DIR__.'/auth.php';
