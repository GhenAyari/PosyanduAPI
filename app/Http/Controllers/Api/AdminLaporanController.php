<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminLaporanController extends Controller
{
    public function getLaporanPosyandu($posyandu_id)
    {
        // PERUBAHAN: Gunakan ->get() untuk mengambil SEMUA RIWAYAT, bukan cuma ->first()
        $rekap46 = DB::table('rekap_kegiatans')->where('posyandu_id', $posyandu_id)->latest()->get();
        $rekap13 = DB::table('pencatatan_kegiatans')->where('posyandu_id', $posyandu_id)->latest()->get();
        $dataUmum = DB::table('data_umums')->where('posyandu_id', $posyandu_id)->latest()->get();

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'rekap46'  => $rekap46,
                'rekap13'  => $rekap13,
                'dataUmum' => $dataUmum
            ]
        ], 200);
    }
}
