<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosyanduController extends Controller
{
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
