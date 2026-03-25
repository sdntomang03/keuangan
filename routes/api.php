<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/halo', function () {
    return response()->json([
        'status' => 'sukses',
        'pesan' => 'Halo dari API Laravel 12!',
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- RUTE TERLINDUNGI (Wajib bawa token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // Ambil data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Users dari langkah sebelumnya (sekarang terlindungi)
    Route::get('/users', [UserController::class, 'index']);
});
