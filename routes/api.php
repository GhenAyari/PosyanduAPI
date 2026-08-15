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
Route::get('/makanan', [\App\Http\Controllers\Api\ReferensiMakananController::class, 'index']);


// ==========================================
// 2. PROTECTED ROUTES (Wajib punya token/login)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/formulir-identifikasi', [\App\Http\Controllers\Api\FormulirIdentifikasiController::class, 'store']);
    Route::get('/formulir-identifikasi', [\App\Http\Controllers\Api\FormulirIdentifikasiController::class, 'index']);
    Route::post('/pengaduan-masyarakat', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'store']);
    Route::get('/pengaduan-masyarakat', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'index']);

    // Rute umum untuk semua user yang berhasil login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'status' => 'sukses',
            // Gunakan ->load() untuk mengambil relasi pada data user yang sedang login
            'data' => $request->user()->load('posyandu')
        ]);
    });
    // Rute untuk warga/user mengupdate username & password mereka sendiri
    Route::put('/warga/update-akun', [\App\Http\Controllers\Api\AuthController::class, 'updateAkunWarga']);

    // ----------------------------------------------------
    // GRUP A: Khusus KADER dan KETUA POSYANDU
    // (Akses operasional posyandu harian & Artikel)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':kader,ketua')->group(function () {
        // CRUD Artikel
        Route::post('/artikels', [ArtikelController::class, 'store']);
        Route::post('/artikels/{id}', [ArtikelController::class, 'update']);
        Route::delete('/artikels/{id}', [ArtikelController::class, 'destroy']);
        Route::post('/warga', [\App\Http\Controllers\Api\WargaController::class, 'store']);
        // Mengambil daftar warga
        Route::get('/warga', [\App\Http\Controllers\Api\WargaController::class, 'index']);
// Reset password warga
        Route::put('/warga/{id}/reset-password', [\App\Http\Controllers\Api\WargaController::class, 'resetPassword']);
        // Pencatatan Kesehatan - Balita
        Route::post('/pemeriksaan-balita', [\App\Http\Controllers\Api\PemeriksaanBalitaController::class, 'store']);
        // Mengambil daftar nama anak untuk Dropdown form pemeriksaan
        Route::get('/warga/anak', [\App\Http\Controllers\Api\WargaController::class, 'getListAnak']);
        // Dropdown nama remaja
        Route::get('/warga/remaja', [\App\Http\Controllers\Api\WargaController::class, 'getListRemaja']);

        // Pencatatan Kesehatan - Remaja
        Route::post('/pemeriksaan-remaja', [\App\Http\Controllers\Api\PemeriksaanRemajaController::class, 'store']);

        // Dropdown nama Ibu
        Route::get('/warga/ibu', [\App\Http\Controllers\Api\WargaController::class, 'getListIbu']);

        // Pencatatan Kesehatan - Ibu Hamil
        Route::post('/pemeriksaan-hamil', [\App\Http\Controllers\Api\PemeriksaanHamilController::class, 'store']);

        // Dropdown nama Lansia
        Route::get('/warga/lansia', [\App\Http\Controllers\Api\WargaController::class, 'getListLansia']);

        // Pencatatan Kesehatan - Lansia
        Route::post('/pemeriksaan-lansia', [\App\Http\Controllers\Api\PemeriksaanLansiaController::class, 'store']);
        // Mengambil daftar draf berdasarkan kelompok sasaran
        Route::get('/draf-pemeriksaan/{kelompok}', [\App\Http\Controllers\Api\DraftController::class, 'getDrafts']);
        Route::post('/makanan', [\App\Http\Controllers\Api\ReferensiMakananController::class, 'store']);
        Route::put('/makanan/{id}', [\App\Http\Controllers\Api\ReferensiMakananController::class, 'update']);
        Route::delete('/makanan/{id}', [\App\Http\Controllers\Api\ReferensiMakananController::class, 'destroy']);
        Route::get('/rekap-kegiatan', [\App\Http\Controllers\Api\RekapKegiatanController::class, 'index']);
        Route::post('/rekap-kegiatan', [\App\Http\Controllers\Api\RekapKegiatanController::class, 'store']);
        Route::delete('/rekap-kegiatan/{id}', [\App\Http\Controllers\Api\RekapKegiatanController::class, 'destroy']);

        Route::get('/pencatatan-kegiatan', [\App\Http\Controllers\Api\PencatatanKegiatanController::class, 'index']);
        Route::post('/pencatatan-kegiatan', [\App\Http\Controllers\Api\PencatatanKegiatanController::class, 'store']);
        Route::delete('/pencatatan-kegiatan/{id}', [\App\Http\Controllers\Api\PencatatanKegiatanController::class, 'destroy']);

        Route::get('/data-umum', [\App\Http\Controllers\Api\DataUmumController::class, 'index']);
        Route::post('/data-umum', [\App\Http\Controllers\Api\DataUmumController::class, 'store']);
        Route::delete('/data-umum/{id}', [\App\Http\Controllers\Api\DataUmumController::class, 'destroy']);
    });

    // ----------------------------------------------------
    // GRUP B: Khusus KETUA POSYANDU
    // (Akses manajerial profil posyandu)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':ketua')->group(function () {
        // Nanti rute untuk edit profil & jadwal posyandu ditaruh di sini
        Route::get('/posyandu/me', [\App\Http\Controllers\Api\PosyanduController::class, 'getMe']);
        Route::post('/posyandu/me/update', [\App\Http\Controllers\Api\PosyanduController::class, 'updateMe']);
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
        Route::get('/admin/pengaduan', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'getAllForAdmin']);
        Route::patch('/admin/pengaduan/{id}/status', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'updateStatus']);
        Route::get('/admin/posyandu-updates', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'getLatestUpdateTiapPosyandu']);
        Route::get('/admin/formulir', [\App\Http\Controllers\Api\FormulirIdentifikasiController::class, 'getAllForAdmin']);
        Route::get('/admin/statistik', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'getStatistik']);
        // ... rute admin lainnya ...
        Route::delete('/admin/formulir/{id}', [\App\Http\Controllers\Api\FormulirIdentifikasiController::class, 'destroyForAdmin']);
        Route::delete('/admin/pengaduan/{id}', [\App\Http\Controllers\Api\PengaduanMasyarakatController::class, 'destroyForAdmin']);
        // Rute Menampilkan Data Kesehatan berdasarkan Posyandu
        Route::get('/admin/pemeriksaan/balita', [\App\Http\Controllers\Api\PemeriksaanBalitaController::class, 'getForAdmin']);
        Route::get('/admin/pemeriksaan/remaja', [\App\Http\Controllers\Api\PemeriksaanRemajaController::class, 'getForAdmin']);
        Route::get('/admin/pemeriksaan/ibu-hamil', [\App\Http\Controllers\Api\PemeriksaanHamilController::class, 'getForAdmin']);
        Route::get('/admin/pemeriksaan/lansia', [\App\Http\Controllers\Api\PemeriksaanLansiaController::class, 'getForAdmin']);

        // Rute Menghapus Data Kesehatan
        Route::delete('/admin/pemeriksaan/balita/{id}', [\App\Http\Controllers\Api\PemeriksaanBalitaController::class, 'destroyForAdmin']);
        Route::delete('/admin/pemeriksaan/remaja/{id}', [\App\Http\Controllers\Api\PemeriksaanRemajaController::class, 'destroyForAdmin']);
        Route::delete('/admin/pemeriksaan/ibu-hamil/{id}', [\App\Http\Controllers\Api\PemeriksaanHamilController::class, 'destroyForAdmin']);
        Route::delete('/admin/pemeriksaan/lansia/{id}', [\App\Http\Controllers\Api\PemeriksaanLansiaController::class, 'destroyForAdmin']);
    });

    // ----------------------------------------------------
    // GRUP E: Khusus PETUGAS PUSKESMAS
    // (Akses read-only laporan kesehatan 9 posyandu)
    // ----------------------------------------------------
    Route::middleware(CheckRole::class.':puskesmas')->group(function () {
        Route::get('/puskesmas/pemeriksaan/balita', [\App\Http\Controllers\Api\PemeriksaanBalitaController::class, 'getForAdmin']);
        Route::get('/puskesmas/pemeriksaan/remaja', [\App\Http\Controllers\Api\PemeriksaanRemajaController::class, 'getForAdmin']);
        Route::get('/puskesmas/pemeriksaan/ibu-hamil', [\App\Http\Controllers\Api\PemeriksaanHamilController::class, 'getForAdmin']);
        Route::get('/puskesmas/pemeriksaan/lansia', [\App\Http\Controllers\Api\PemeriksaanLansiaController::class, 'getForAdmin']);
    });

});
