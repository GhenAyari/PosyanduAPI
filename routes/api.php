<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosyanduController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Middleware\CheckRole; // Import Middleware baru kita

/**
 * @title API Posyandu LDU
 * @version 1.0.0
 * @description Dokumentasi resmi API untuk Web Posyandu
 */

// ==========================================
// 1. PUBLIC ROUTES (Bisa diakses siapa saja)
// ==========================================
Route::get('/ping', function () {
    return response()->json(['status' => 'sukses', 'pesan' => 'API Aktif']);
});
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profil-posyandu', [PosyanduController::class, 'index']);
Route::get('/artikels', [ArtikelController::class, 'index']);
Route::get('/artikels/{id}', [ArtikelController::class, 'show']);


// ==========================================
// 2. PROTECTED ROUTES (Wajib punya token/login)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Rute umum untuk semua user yang berhasil login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(['status' => 'sukses', 'data' => auth()->user()]);
    });

    // ----------------------------------------------------
    // GRUP A: Khusus KADER dan KETUA POSYANDU
    // (Akses operasional posyandu harian & Artikel)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':kader,ketua')->group(function () {
        // CRUD Artikel
        Route::post('/artikels', [ArtikelController::class, 'store']);
        Route::post('/artikels/{id}', [ArtikelController::class, 'update']);
        Route::delete('/artikels/{id}', [ArtikelController::class, 'destroy']);

        // Nanti rute untuk Form KIA, Trantib, Sosial, dll bisa ditaruh di sini
    });

    // ----------------------------------------------------
    // GRUP B: Khusus KETUA POSYANDU
    // (Akses manajerial profil posyandu)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':ketua')->group(function () {
        // Nanti rute untuk edit profil & jadwal posyandu ditaruh di sini
    });

    // ----------------------------------------------------
    // GRUP C: Khusus WARGA
    // (Akses read-only rapor keluarga)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':warga')->group(function () {
        // Nanti rute untuk melihat rapor balita/keluarga ditaruh di sini
    });

    // ----------------------------------------------------
    // GRUP D: Khusus SUPERADMIN (Desa)
    // (Akses mata elang / analitik)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':superadmin')->group(function () {
        // Nanti rute untuk dashboard rekap desa ditaruh di sini
    });

});
