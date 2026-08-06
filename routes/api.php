<?php

use Illuminate\Support\Facades\Route;
use App\Models\Artikel;

// Tes 1: API tanpa database
Route::get('/tes', function () {
    return response()->json(['status' => 'success', 'pesan' => 'Halo dari Laravel API!']);
});

// Tes 2: API ambil data dari database
Route::get('/artikels', function () {
    return response()->json(Artikel::all());
});
