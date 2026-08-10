<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PosyanduController extends Controller
{
    // ==========================================================
    // 1. UNTUK HALAMAN PUBLIK (BERANDA)
    // ==========================================================
    public function index()
    {
        // PERBAIKAN BUG: Ubah first() menjadi get() agar 9 lokasi terbaca!
        $posyandus = DB::table('posyandus')->get();

        if ($posyandus->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Posyandu tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Data Profil 9 Posyandu berhasil diambil',
            'data' => $posyandus
        ]);
    }

    // ==========================================================
    // 2. UNTUK DASBOR KETUA/KADER (Ambil Data Posyandunya Saja)
    // ==========================================================
    public function getMe(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;
        $posyandu = DB::table('posyandus')->where('id', $posyanduId)->first();

        return response()->json([
            'status' => 'sukses',
            'data' => $posyandu
        ]);
    }

    // ==========================================================
    // 3. UNTUK DASBOR KETUA/KADER (Simpan Pembaruan Profil)
    // ==========================================================
    public function updateMe(Request $request)
    {
        $posyanduId = $request->user()->posyandu_id;

        // Ambil semua data teks yang dikirim dari React
        $updateData = $request->except(['foto']);

        // Tangani unggah foto jika kader memasukkan gambar baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $path = $file->store('profil_posyandu', 'public');
            $updateData['foto'] = $path;
        }

        // Update ke database
        DB::table('posyandus')->where('id', $posyanduId)->update($updateData);

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Profil Posyandu berhasil diperbarui!'
        ]);
    }
}
