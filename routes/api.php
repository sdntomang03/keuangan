<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\ApiJsonController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/halo', function () {
    return response()->json([
        'status' => 'sukses',
        'pesan' => 'Halo dari API Laravel 12!',
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- RUTE TERLINDUNGI (Wajib login / bawa token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [UserController::class, 'index']);

    // PINDAHKAN ROUTE INI KE DALAM GRUP
    Route::get('get-rkas', [ApiJsonController::class, 'getRkas'])->name('api.getRkas');
});
