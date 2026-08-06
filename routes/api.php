<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosyanduController;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'sukses',
        'pesan' => 'API Posyandu LDU aktif dan siap menerima perintah!',
        'waktu_server' => now()->toDateTimeString()
    ]);
});

// Tes 2: API ambil data dari database
Route::get('/artikels', function () {
    return response()->json();
});

// Endpoint asli untuk mengambil data dari database
Route::get('/profil-posyandu', [PosyanduController::class, 'index']);
