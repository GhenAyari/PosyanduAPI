<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosyanduController;
use App\Http\Controllers\Api\AuthController;

/**
 * @title API Posyandu LDU
 * @version 1.0.0
 * @description Dokumentasi resmi API untuk Web Posyandu
 */

// Public Routes
Route::get('/ping', function () {
    return response()->json([
        'status' => 'sukses',
        'pesan' => 'API Posyandu LDU aktif dan siap menerima perintah!',
        'waktu_server' => now()->toDateTimeString()
    ]);
});

Route::get('/profil-posyandu', [PosyanduController::class, 'index']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get User Login
    Route::get('/me', function () {
        return response()->json(['status' => 'sukses', 'data' => auth()->user()]);
    });

    // --- RUTE KHUSUS PENGELOLA ---
    Route::middleware('isPengelola')->group(function () {
        // Nanti endpoint buat bikin artikel taruh di sini
        // Route::post('/artikels', [ArtikelController::class, 'store']);
    });
});
