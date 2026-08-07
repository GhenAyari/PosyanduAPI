<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosyanduController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArtikelController; // Import Controller Baru

/**
 * @title API Posyandu LDU
 * @version 1.0.0
 * @description Dokumentasi resmi API untuk Web Posyandu
 */

// --- PUBLIC ROUTES (Tanpa Login) ---
Route::get('/ping', function () {
    return response()->json(['status' => 'sukses', 'pesan' => 'API Aktif']);
});

Route::get('/profil-posyandu', [PosyanduController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint baca artikel untuk halaman publik
Route::get('/artikels', [ArtikelController::class, 'index']);
Route::get('/artikels/{id}', [ArtikelController::class, 'show']);

// --- PROTECTED ROUTES (Butuh Token) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function () {
        return response()->json(['status' => 'sukses', 'data' => auth()->user()]);
    });

    // Rute Khusus Pengelola (Kader/Superadmin)
    Route::middleware('isPengelola')->group(function () {

        // Endpoint CRUD Artikel
        Route::post('/artikels', [ArtikelController::class, 'store']);
        Route::post('/artikels/{id}', [ArtikelController::class, 'update']); // Menggunakan POST untuk form-data upload foto (disimulasikan sebagai PUT)
        Route::delete('/artikels/{id}', [ArtikelController::class, 'destroy']);

    });
});
