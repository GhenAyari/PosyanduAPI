<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosyanduController extends Controller
{

    /**
     * Mengambil data profil Posyandu
     *
     * Endpoint ini digunakan untuk mendapatkan data profil posyandu.
     *
     * @unauthenticated  <-- Ini buat ngasih tau Scramble kalau endpoint ini NDAK butuh login/token
     *
     * @response array{status: string, pesan: string, data: array{id: int, nama: string, alamat: string}}
     */
    public function index()
    {
        // Mengambil data posyandu pertama (karena kita baru buat 1 dummy)
        $posyandu = DB::table('posyandus')->first();

        if (!$posyandu) {
            return response()->json([
                'status' => 'error',
                'pesan' => 'Data Posyandu tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Data Profil Posyandu berhasil diambil',
            'data' => $posyandu
        ]);
    }
}
