<?php

use App\Http\Controllers\AkbController;
use App\Http\Controllers\BelanjaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RkasController;
use App\Http\Controllers\SettingController;
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
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/rkas', [RkasController::class, 'index'])->name('rkas.index');
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

});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Otomatis mencakup: index, create, store, show, edit, update, destroy
    Route::resource('belanja', BelanjaController::class);

    // Rute API tambahan untuk pencarian data
    Route::prefix('api')->group(function () {
        Route::get('/get-rekening', [BelanjaController::class, 'getRekening'])->name('api.rekening');
        Route::get('/get-komponen', [BelanjaController::class, 'getKomponen'])->name('api.komponen');
    });

});
require __DIR__.'/auth.php';
